<?php

class PharmacistMessagesModel
{
    private static ?array $chatColumnsCache = null;
    private static ?int $pharmacyIdCache = null;
    private static ?int $lastDebugLogAt = null;
    private static array $patientPharmacyCache = [];

    private static function writeLog(string $file, string $level, string $message, array $context = []): void
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 3);
        $logDir = $rootDir . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($context, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        @file_put_contents($logDir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }

    private static function currentPharmacyId(?int $fallbackPharmacistId = null): int
    {
        if (self::$pharmacyIdCache !== null && self::$pharmacyIdCache > 0) {
            return self::$pharmacyIdCache;
        }

        if (isset($GLOBALS['currentPharmacyId']) && (int) $GLOBALS['currentPharmacyId'] > 0) {
            self::$pharmacyIdCache = (int) $GLOBALS['currentPharmacyId'];
            return self::$pharmacyIdCache;
        }

        $auth = Auth::getUser();
        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            self::$pharmacyIdCache = $fromToken;
            return $fromToken;
        }

        $authId = (int) ($auth['id'] ?? 0);
        if ($authId > 0) {
            self::$pharmacyIdCache = PharmacyContext::resolvePharmacistPharmacyId($authId);
            if (self::$pharmacyIdCache > 0) {
                return self::$pharmacyIdCache;
            }
        }

        if (($fallbackPharmacistId ?? 0) > 0) {
            self::$pharmacyIdCache = PharmacyContext::resolvePharmacistPharmacyId((int) $fallbackPharmacistId);
            if (self::$pharmacyIdCache > 0) {
                return self::$pharmacyIdCache;
            }
        }

        $selected = PharmacyContext::selectedPharmacyId();
        if ($selected > 0) {
            self::$pharmacyIdCache = $selected;
            return self::$pharmacyIdCache;
        }

        self::$pharmacyIdCache = 0;
        return self::$pharmacyIdCache;
    }

    private static function hasChatPharmacy(): bool
    {
        $cols = self::getChatColumns();
        return isset($cols['pharmacy_id']);
    }

    private static function patientPharmacyId(string $patientNic): int
    {
        $patientNic = trim($patientNic);
        if ($patientNic === '') {
            return 0;
        }
        if (isset(self::$patientPharmacyCache[$patientNic])) {
            return self::$patientPharmacyCache[$patientNic];
        }
        if (!self::tableExists('patient_pharmacy_selection')) {
            self::$patientPharmacyCache[$patientNic] = 0;
            return 0;
        }
        $row = Database::fetchOne("SELECT pharmacy_id FROM patient_pharmacy_selection WHERE patient_nic = ? AND is_active = 1 ORDER BY id DESC LIMIT 1", 's', [$patientNic]);
        if ($row) {
            $pid = (int) ($row['pharmacy_id'] ?? 0);
            self::$patientPharmacyCache[$patientNic] = $pid;
            return $pid;
        }
        self::$patientPharmacyCache[$patientNic] = 0;
        return 0;
    }

    private static function chatPharmacyFilter(int $pharmacyId): string
    {
        if (!self::hasChatPharmacy() || $pharmacyId <= 0) {
            return '';
        }
        return "AND (pharmacy_id IS NULL OR pharmacy_id = 0 OR pharmacy_id = " . $pharmacyId . ")";
    }

    private static function tableExists(string $name): bool
    {
        return in_array($name, ['chat_messages', 'patient_pharmacy_selection', 'patient', 'pharmacist', 'pharmacies', 'pharmacy_users'], true);
    }

    private static function getChatColumns(): array
    {
        if (self::$chatColumnsCache !== null) {
            return self::$chatColumnsCache;
        }

        $cols = array_fill_keys([
            'id',
            'sender_type',
            'sender_id',
            'receiver_id',
            'message_text',
            'sent_at',
            'is_read',
            'typing',
            'type',
            'created_at',
            'pharmacy_id',
        ], true);

        self::$chatColumnsCache = $cols;
        return $cols;
    }

    private static function firstExistingColumn(array $candidates): ?string
    {
        $cols = self::getChatColumns();
        foreach ($candidates as $candidate) {
            if (isset($cols[strtolower($candidate)])) {
                return $candidate;
            }
        }
        return null;
    }

    private static function participantExpr(array $candidates): ?string
    {
        $present = [];
        foreach ($candidates as $candidate) {
            if (self::firstExistingColumn([$candidate]) !== null) {
                $present[] = $candidate;
            }
        }
        if (empty($present)) {
            return null;
        }
        if (count($present) === 1) {
            return $present[0];
        }

        $parts = [];
        foreach ($present as $col) {
            $parts[] = "NULLIF($col, '')";
        }
        return "COALESCE(" . implode(', ', $parts) . ")";
    }

    public static function canUseMessages(): bool
    {
        return self::tableExists('chat_messages');
    }

    public static function getContacts(string $type): array
    {
        Database::setUpConnection();

        if ($type === 'suppliers') {
            if (!self::tableExists('supplier')) {
                return [];
            }

            $sql = "
                SELECT
                    CAST(s.id AS CHAR) AS contact_id,
                    s.name,
                    m.message_text,
                    m.sent_at,
                    (
                        SELECT COUNT(*)
                        FROM chat_messages cm
                        WHERE cm.sender_type = 'supplier'
                          AND cm.sender_id = CAST(s.id AS CHAR)
                          AND cm.receiver_id = 'PHARMACIST'
                          AND cm.is_read = 0
                          " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND cm.pharmacy_id = " . self::currentPharmacyId() : '') . "
                    ) AS unread_count
                FROM supplier s
                LEFT JOIN chat_messages m ON m.id = (
                    SELECT MAX(m2.id)
                    FROM chat_messages m2
                    WHERE (m2.sender_type = 'supplier' AND m2.sender_id = CAST(s.id AS CHAR) AND m2.receiver_id = 'PHARMACIST')
                       OR (m2.sender_type = 'pharmacist' AND m2.receiver_id = CAST(s.id AS CHAR))
                    " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND m2.pharmacy_id = " . self::currentPharmacyId() : '') . "
                )
                ORDER BY CASE WHEN m.sent_at IS NULL THEN 1 ELSE 0 END, m.sent_at DESC, s.name ASC
            ";
        } else {
            if (!self::tableExists('patient')) {
                return [];
            }

            $sql = "
                SELECT
                    p.nic AS contact_id,
                    p.name,
                    m.message_text,
                    m.sent_at,
                    (
                        SELECT COUNT(*)
                        FROM chat_messages cm
                        WHERE cm.sender_id = p.nic
                          AND cm.receiver_id = 'PHARMACIST'
                          AND cm.is_read = 0
                    ) AS unread_count
                FROM patient p
                LEFT JOIN chat_messages m ON m.id = (
                    SELECT MAX(m2.id)
                    FROM chat_messages m2
                    WHERE (m2.sender_id = p.nic AND m2.receiver_id = 'PHARMACIST')
                       OR (m2.sender_type = 'pharmacist' AND m2.receiver_id = p.nic)
                )
                ORDER BY CASE WHEN m.sent_at IS NULL THEN 1 ELSE 0 END, m.sent_at DESC, p.name ASC
            ";
        }

        $rows = [];
        $rs = Database::search($sql);
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = [
                    'id' => (string) ($row['contact_id'] ?? ''),
                    'name' => (string) ($row['name'] ?? 'Unknown'),
                    'lastMessage' => (string) ($row['message_text'] ?? ''),
                    'lastMessageAt' => (string) ($row['sent_at'] ?? ''),
                    'unread' => (int) ($row['unread_count'] ?? 0),
                ];
            }
        }

        return $rows;
    }

    public static function getMessages(string $contactId, string $type): array
    {
        if ($contactId === '' || !self::canUseMessages()) {
            return [];
        }

        $safeId = Database::escape($contactId);
        $contactPharmacyId = 0;
        $senderExpr = self::participantExpr(['sender_id', 'sender']);
        $receiverExpr = self::participantExpr(['receiver_id', 'receiver']);
        if ($senderExpr === null || $receiverExpr === null) {
            return [];
        }

        $pharmacyFilter = '';
        if ($type === 'patients') {
            $contactPharmacyId = self::patientPharmacyId($contactId);
            if ($contactPharmacyId <= 0) {
                $contactPharmacyId = self::currentPharmacyId();
            }
            $pharmacyFilter = self::chatPharmacyFilter($contactPharmacyId);
        } else {
            $pharmacyFilter = self::chatPharmacyFilter(self::currentPharmacyId());
        }

        if ($type === 'suppliers') {
            $sql = "
                SELECT id, sender_type, sender_id, receiver_id, message_text, sent_at, is_read
                FROM chat_messages
                WHERE (
                    (sender_type = 'supplier' AND $senderExpr = '$safeId' AND UPPER($receiverExpr) = 'PHARMACIST')
                    OR (sender_type = 'pharmacist' AND $receiverExpr = '$safeId')
                )
                $pharmacyFilter
                ORDER BY sent_at ASC, id ASC
                LIMIT 200
            ";
        } else {
            $sql = "
                SELECT id, sender_type, sender_id, receiver_id, message_text, sent_at, is_read
                FROM chat_messages
                WHERE (
                    ($senderExpr = '$safeId' AND UPPER($receiverExpr) = 'PHARMACIST')
                    OR (sender_type = 'pharmacist' AND $receiverExpr = '$safeId')
                )
                $pharmacyFilter
                ORDER BY sent_at ASC, id ASC
                LIMIT 200
            ";
        }

        $rows = [];
        $startedAt = microtime(true);
        $rs = Database::search($sql);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        if (!($rs instanceof mysqli_result)) {
            self::writeLog('messages-error.log', 'ERROR', 'Pharmacist getMessages query failed.', [
                'contact_id' => $contactId,
                'type' => $type,
                'duration_ms' => $durationMs,
                'db_error' => Database::$connection->error ?? null,
            ]);
            return [];
        }
        if ($durationMs >= 2000) {
            self::writeLog('messages-debug.log', 'DEBUG', 'Slow pharmacist getMessages query.', [
                'contact_id' => $contactId,
                'type' => $type,
                'duration_ms' => $durationMs,
                'pharmacy_id' => $type === 'patients' ? ($contactPharmacyId ?? 0) : self::currentPharmacyId(),
            ]);
        }
        $now = time();
        if (self::$lastDebugLogAt === null || ($now - self::$lastDebugLogAt) >= 20) {
            self::$lastDebugLogAt = $now;
            self::writeLog('messages-debug.log', 'DEBUG', 'Pharmacist getMessages completed.', [
                'contact_id' => $contactId,
                'type' => $type,
                'duration_ms' => $durationMs,
                'pharmacy_id' => $type === 'patients' ? ($contactPharmacyId ?? 0) : self::currentPharmacyId(),
            ]);
        }
        while ($row = $rs->fetch_assoc()) {
            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'senderType' => (string) ($row['sender_type'] ?? ''),
                'senderId' => (string) ($row['sender_id'] ?? ''),
                'receiverId' => (string) ($row['receiver_id'] ?? ''),
                'text' => (string) ($row['message_text'] ?? ''),
                'sentAt' => (string) ($row['sent_at'] ?? ''),
                'isRead' => (int) ($row['is_read'] ?? 0) === 1,
            ];
        }

        return $rows;
    }

    public static function markAsRead(string $contactId, string $type): void
    {
        if ($contactId === '' || !self::canUseMessages()) {
            return;
        }

        $safeId = Database::escape($contactId);
        $contactPharmacyId = 0;

        if ($type === 'suppliers') {
            $senderExpr = self::participantExpr(['sender_id', 'sender']);
            $receiverExpr = self::participantExpr(['receiver_id', 'receiver']);
            if ($senderExpr === null || $receiverExpr === null) {
                return;
            }
            Database::iud("
                UPDATE chat_messages
                SET is_read = 1
                WHERE UPPER($receiverExpr) = 'PHARMACIST'
                  AND sender_type = 'supplier'
                  AND $senderExpr = '$safeId'
                  AND is_read = 0
                  " . self::chatPharmacyFilter(self::currentPharmacyId()) . "
            ");
            return;
        }

        $senderExpr = self::participantExpr(['sender_id', 'sender']);
        $receiverExpr = self::participantExpr(['receiver_id', 'receiver']);
        if ($senderExpr === null || $receiverExpr === null) {
            return;
        }
        $contactPharmacyId = self::patientPharmacyId($contactId);
        if ($contactPharmacyId <= 0) {
            $contactPharmacyId = self::currentPharmacyId();
        }
        Database::iud("
            UPDATE chat_messages
            SET is_read = 1
        WHERE UPPER($receiverExpr) = 'PHARMACIST'
              AND $senderExpr = '$safeId'
              AND is_read = 0
              " . self::chatPharmacyFilter($contactPharmacyId) . "
        ");
    }

    public static function sendMessage(int $pharmacistId, string $contactId, string $message, string $type = 'patients'): bool
    {
        if ($contactId === '' || trim($message) === '' || !self::canUseMessages()) {
            return false;
        }

        $safeContact = Database::escape($contactId);
        $safeMessage = Database::escape(trim($message));
        $safeSenderId = Database::escape((string) $pharmacistId);
        $cols = self::getChatColumns();

        $insertCols = [];
        $insertVals = [];

        // Role/type column can be named differently in legacy schemas.
        if (isset($cols['sender_type'])) {
            $insertCols[] = 'sender_type';
            $insertVals[] = "'pharmacist'";
        }
        if (isset($cols['typing'])) {
            $insertCols[] = 'typing';
            $insertVals[] = "'pharmacist'";
        }
        if (isset($cols['type'])) {
            $insertCols[] = 'type';
            $insertVals[] = "'pharmacist'";
        }

        if (isset($cols['sender_id'])) {
            $insertCols[] = 'sender_id';
            $insertVals[] = "'$safeSenderId'";
        }
        if (isset($cols['sender'])) {
            $insertCols[] = 'sender';
            $insertVals[] = "'$safeSenderId'";
        }

        if (isset($cols['receiver_id'])) {
            $insertCols[] = 'receiver_id';
            $insertVals[] = "'$safeContact'";
        }
        if (isset($cols['receiver'])) {
            $insertCols[] = 'receiver';
            $insertVals[] = "'$safeContact'";
        }

        if (isset($cols['message_text'])) {
            $insertCols[] = 'message_text';
            $insertVals[] = "'$safeMessage'";
        } elseif (isset($cols['message'])) {
            $insertCols[] = 'message';
            $insertVals[] = "'$safeMessage'";
        }

        if (isset($cols['sent_at'])) {
            $insertCols[] = 'sent_at';
            $insertVals[] = 'NOW()';
        } elseif (isset($cols['created_at'])) {
            $insertCols[] = 'created_at';
            $insertVals[] = 'NOW()';
        }

        if (isset($cols['is_read'])) {
            $insertCols[] = 'is_read';
            $insertVals[] = '0';
        }
        $resolvedPharmacyId = self::currentPharmacyId($pharmacistId);
        if ($type === 'patients') {
            $contactPharmacyId = self::patientPharmacyId($contactId);
            if ($contactPharmacyId > 0) {
                $resolvedPharmacyId = $contactPharmacyId;
            }
        }
        if (isset($cols['pharmacy_id']) && $resolvedPharmacyId > 0) {
            $insertCols[] = 'pharmacy_id';
            $insertVals[] = (string) $resolvedPharmacyId;
        }

        if (empty($insertCols)) {
            self::writeLog('messages-error.log', 'ERROR', 'Pharmacist sendMessage aborted due missing insert columns.', [
                'pharmacist_id' => $pharmacistId,
                'contact_id' => $contactId,
            ]);
            return false;
        }

        $sql = "INSERT INTO chat_messages (" . implode(', ', $insertCols) . ")
                VALUES (" . implode(', ', $insertVals) . ")";
        $ok = Database::iud($sql);
        if (!$ok) {
            self::writeLog('messages-error.log', 'ERROR', 'Pharmacist sendMessage insert failed.', [
                'pharmacist_id' => $pharmacistId,
                'contact_id' => $contactId,
                'message_len' => strlen(trim($message)),
                'db_error' => Database::$connection->error ?? null,
            ]);
        } else {
            self::writeLog('messages-debug.log', 'DEBUG', 'Pharmacist message sent.', [
                'pharmacist_id' => $pharmacistId,
                'contact_id' => $contactId,
                'message_len' => strlen(trim($message)),
                'pharmacy_id' => $resolvedPharmacyId,
            ]);
            if (isset($cols['pharmacy_id']) && $resolvedPharmacyId <= 0) {
                self::writeLog('messages-error.log', 'ERROR', 'Pharmacist message saved without pharmacy_id.', [
                    'pharmacist_id' => $pharmacistId,
                    'contact_id' => $contactId,
                    'message_len' => strlen(trim($message)),
                ]);
            }
        }
        return $ok;
    }
}
