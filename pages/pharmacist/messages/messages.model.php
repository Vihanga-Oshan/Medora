<?php

require_once ROOT . '/core/AppLogger.php';
require_once ROOT . '/core/ChatMessageSupport.php';

class PharmacistMessagesModel
{
    private static ?int $pharmacyIdCache = null;
    private static array $patientPharmacyCache = [];

    private static function writeLog(string $file, string $level, string $message, array $context = []): void
    {
        AppLogger::write($file, $level, $message, $context);
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
        return ChatMessageSupport::hasColumn('pharmacy_id');
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

    public static function getContacts(string $type): array
    {
        $messageCol = ChatMessageSupport::firstExistingColumn(['message_text', 'message'], 'm') ?? "''";
        $sentAtCol = ChatMessageSupport::firstExistingColumn(['sent_at', 'created_at'], 'm') ?? "''";
        $cmSender = ChatMessageSupport::participantExpr(['sender_id', 'sender'], 'cm');
        $cmReceiver = ChatMessageSupport::participantExpr(['receiver_id', 'receiver'], 'cm');
        $m2Sender = ChatMessageSupport::participantExpr(['sender_id', 'sender'], 'm2');
        $m2Receiver = ChatMessageSupport::participantExpr(['receiver_id', 'receiver'], 'm2');
        $cmRole = ChatMessageSupport::firstExistingColumn(['sender_type', 'type', 'typing'], 'cm');
        $m2Role = ChatMessageSupport::firstExistingColumn(['sender_type', 'type', 'typing'], 'm2');

        if ($type === 'suppliers') {
            $sql = "
                SELECT
                    CAST(s.id AS CHAR) AS contact_id,
                    s.name,
                    $messageCol AS message_text,
                    $sentAtCol AS sent_at,
                    (
                        SELECT COUNT(*)
                        FROM chat_messages cm
                        WHERE " . ($cmRole ? "$cmRole = 'supplier' AND " : '') . "
                              " . ($cmSender ?: "''") . " = CAST(s.id AS CHAR)
                          AND UPPER(" . ($cmReceiver ?: "''") . ") = 'PHARMACIST'
                          AND cm.is_read = 0
                          " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND cm.pharmacy_id = " . self::currentPharmacyId() : '') . "
                    ) AS unread_count
                FROM supplier s
                LEFT JOIN chat_messages m ON m.id = (
                    SELECT MAX(m2.id)
                    FROM chat_messages m2
                    WHERE ("
                        . ($m2Role ? "$m2Role = 'supplier' AND " : '')
                        . ($m2Sender ?: "''") . " = CAST(s.id AS CHAR) AND UPPER(" . ($m2Receiver ?: "''") . ") = 'PHARMACIST')
                       OR ("
                        . ($m2Role ? "$m2Role = 'pharmacist' AND " : '')
                        . ($m2Receiver ?: "''") . " = CAST(s.id AS CHAR))
                    " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND m2.pharmacy_id = " . self::currentPharmacyId() : '') . "
                )
                ORDER BY CASE WHEN $sentAtCol IS NULL THEN 1 ELSE 0 END, $sentAtCol DESC, s.name ASC
            ";
        } else {
            $sql = "
                SELECT
                    convo.contact_id,
                    COALESCE(NULLIF(TRIM(p.name), ''), convo.contact_id) AS name,
                    $messageCol AS message_text,
                    $sentAtCol AS sent_at,
                    (
                        SELECT COUNT(*)
                        FROM chat_messages cm
                        WHERE " . ($cmSender ?: "''") . " = convo.contact_id
                          AND UPPER(" . ($cmReceiver ?: "''") . ") = 'PHARMACIST'
                          AND cm.is_read = 0
                          " . self::chatPharmacyFilter(self::currentPharmacyId()) . "
                    ) AS unread_count
                FROM (
                    SELECT DISTINCT
                        CASE
                            WHEN sender_type = 'pharmacist' THEN receiver_id
                            ELSE sender_id
                        END AS contact_id
                    FROM chat_messages
                    WHERE (
                        (sender_type = 'patient' AND UPPER(receiver_id) = 'PHARMACIST')
                        OR (sender_type = 'pharmacist' AND UPPER(receiver_id) <> 'PHARMACIST')
                    )
                    " . self::chatPharmacyFilter(self::currentPharmacyId()) . "
                ) convo
                LEFT JOIN patient p ON p.nic = convo.contact_id
                LEFT JOIN chat_messages m ON m.id = (
                    SELECT MAX(m2.id)
                    FROM chat_messages m2
                    WHERE ("
                        . ($m2Sender ?: "''") . " = convo.contact_id AND UPPER(" . ($m2Receiver ?: "''") . ") = 'PHARMACIST')
                       OR ("
                        . ($m2Role ? "$m2Role = 'pharmacist' AND " : '')
                        . ($m2Receiver ?: "''") . " = convo.contact_id)
                    " . self::chatPharmacyFilter(self::currentPharmacyId()) . "
                )
                ORDER BY CASE WHEN $sentAtCol IS NULL THEN 1 ELSE 0 END, $sentAtCol DESC, name ASC
            ";
        }

        return array_map(
            static fn(array $row): array => ChatMessageSupport::mapContactRow($row),
            Database::fetchAll($sql)
        );
    }

    public static function getMessages(string $contactId, string $type): array
    {
        if ($contactId === '') {
            return [];
        }

        $contactPharmacyId = 0;
        $senderExpr = ChatMessageSupport::participantExpr(['sender_id', 'sender']);
        $receiverExpr = ChatMessageSupport::participantExpr(['receiver_id', 'receiver']);
        $roleExpr = ChatMessageSupport::firstExistingColumn(['sender_type', 'type', 'typing']);
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
            $supplierRole = $roleExpr ? "$roleExpr = 'supplier' AND " : '';
            $pharmacistRole = $roleExpr ? "$roleExpr = 'pharmacist' AND " : '';
            $sql = "
                SELECT " . ChatMessageSupport::threadSelectSql() . "
                FROM chat_messages
                WHERE (
                    ($supplierRole$senderExpr = ? AND UPPER($receiverExpr) = 'PHARMACIST')
                    OR ($pharmacistRole$receiverExpr = ?)
                )
                $pharmacyFilter
                ORDER BY sent_at ASC, id ASC
                LIMIT 200
            ";
            $types = 'ss';
            $params = [$contactId, $contactId];
        } else {
            $pharmacistRole = $roleExpr ? "$roleExpr = 'pharmacist' AND " : '';
            $sql = "
                SELECT " . ChatMessageSupport::threadSelectSql() . "
                FROM chat_messages
                WHERE (
                    ($senderExpr = ? AND UPPER($receiverExpr) = 'PHARMACIST')
                    OR ($pharmacistRole$receiverExpr = ?)
                )
                $pharmacyFilter
                ORDER BY sent_at ASC, id ASC
                LIMIT 200
            ";
            $types = 'ss';
            $params = [$contactId, $contactId];
        }

        $queryResult = ChatMessageSupport::fetchAllTimed($sql, $types, $params);
        if ($queryResult['failed']) {
            self::writeLog('messages-error.log', 'ERROR', 'Pharmacist getMessages query failed.', [
                'contact_id' => $contactId,
                'type' => $type,
                'duration_ms' => $queryResult['duration_ms'],
                'db_error' => $queryResult['db_error'],
            ]);
            return [];
        }
        $durationMs = $queryResult['duration_ms'];
        if ($durationMs >= 2000) {
            self::writeLog('messages-debug.log', 'DEBUG', 'Slow pharmacist getMessages query.', [
                'contact_id' => $contactId,
                'type' => $type,
                'duration_ms' => $durationMs,
                'pharmacy_id' => $type === 'patients' ? ($contactPharmacyId ?? 0) : self::currentPharmacyId(),
            ]);
        }
        if (ChatMessageSupport::shouldWriteDebug('pharmacist_get_messages')) {
            self::writeLog('messages-debug.log', 'DEBUG', 'Pharmacist getMessages completed.', [
                'contact_id' => $contactId,
                'type' => $type,
                'duration_ms' => $durationMs,
                'pharmacy_id' => $type === 'patients' ? ($contactPharmacyId ?? 0) : self::currentPharmacyId(),
            ]);
        }
        return ChatMessageSupport::mapPharmacistThreadRows($queryResult['rows']);
    }

    public static function markAsRead(string $contactId, string $type): void
    {
        if ($contactId === '') {
            return;
        }

        $contactPharmacyId = 0;

        if ($type === 'suppliers') {
            $senderExpr = ChatMessageSupport::participantExpr(['sender_id', 'sender']);
            $receiverExpr = ChatMessageSupport::participantExpr(['receiver_id', 'receiver']);
            $roleExpr = ChatMessageSupport::firstExistingColumn(['sender_type', 'type', 'typing']);
            if ($senderExpr === null || $receiverExpr === null) {
                return;
            }
            Database::execute("
                UPDATE chat_messages
                SET is_read = 1
                WHERE UPPER($receiverExpr) = 'PHARMACIST'
                  " . ($roleExpr ? "AND $roleExpr = 'supplier'" : '') . "
                  AND $senderExpr = ?
                  AND is_read = 0
                  " . self::chatPharmacyFilter(self::currentPharmacyId()) . "
            ", 's', [$contactId]);
            return;
        }

        $senderExpr = ChatMessageSupport::participantExpr(['sender_id', 'sender']);
        $receiverExpr = ChatMessageSupport::participantExpr(['receiver_id', 'receiver']);
        if ($senderExpr === null || $receiverExpr === null) {
            return;
        }
        $contactPharmacyId = self::patientPharmacyId($contactId);
        if ($contactPharmacyId <= 0) {
            $contactPharmacyId = self::currentPharmacyId();
        }
        Database::execute("
            UPDATE chat_messages
            SET is_read = 1
        WHERE UPPER($receiverExpr) = 'PHARMACIST'
              AND $senderExpr = ?
              AND is_read = 0
              " . self::chatPharmacyFilter($contactPharmacyId) . "
        ", 's', [$contactId]);
    }

    public static function sendMessage(int $pharmacistId, string $contactId, string $message, string $type = 'patients'): bool
    {
        if ($contactId === '' || trim($message) === '') {
            return false;
        }

        $trimmedMessage = trim($message);
        $resolvedPharmacyId = self::currentPharmacyId($pharmacistId);
        if ($type === 'patients') {
            $contactPharmacyId = self::patientPharmacyId($contactId);
            if ($contactPharmacyId > 0) {
                $resolvedPharmacyId = $contactPharmacyId;
            }
        }
        $insert = ChatMessageSupport::buildInsertParts([
            'sender_type' => 'pharmacist',
            'typing' => 'pharmacist',
            'type' => 'pharmacist',
            'sender_id' => (string) $pharmacistId,
            'sender' => (string) $pharmacistId,
            'receiver_id' => $contactId,
            'receiver' => $contactId,
            'message_text' => $trimmedMessage,
            'message' => $trimmedMessage,
            'pharmacy_id' => $resolvedPharmacyId,
        ]);

        if (empty($insert['columns'])) {
            self::writeLog('messages-error.log', 'ERROR', 'Pharmacist sendMessage aborted due missing insert columns.', [
                'pharmacist_id' => $pharmacistId,
                'contact_id' => $contactId,
            ]);
            return false;
        }

        $sql = "INSERT INTO chat_messages (" . implode(', ', $insert['columns']) . ")
                VALUES (" . implode(', ', $insert['values']) . ")";
        $ok = Database::execute($sql, $insert['types'], $insert['params']);
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
            if (ChatMessageSupport::hasColumn('pharmacy_id') && $resolvedPharmacyId <= 0) {
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
