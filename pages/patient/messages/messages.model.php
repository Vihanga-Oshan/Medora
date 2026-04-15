<?php

class PatientMessagesModel
{
    private static ?array $chatColumnsCache = null;
    private static ?int $lastDebugLogAt = null;

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

    private static function currentPharmacyId(): int
    {
        return PharmacyContext::selectedPharmacyId();
    }

    private static function hasChatPharmacy(): bool
    {
        $cols = self::getChatColumns();
        return isset($cols['pharmacy_id']);
    }

    private static function chatPharmacyFilter(): string
    {
        if (!self::hasChatPharmacy() || self::currentPharmacyId() <= 0) {
            return '';
        }
        // Treat 0 as legacy/global scope so messages are not hidden by pharmacy context.
        return "AND (pharmacy_id IS NULL OR pharmacy_id = 0 OR pharmacy_id = " . self::currentPharmacyId() . ")";
    }

    private static function tableExists(string $name): bool
    {
        return in_array($name, ['chat_messages', 'patient_pharmacy_selection', 'pharmacies'], true);
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

    private static function messageSelectSql(): string
    {
        $idCol = self::firstExistingColumn(['id']) ?? 'id';
        $senderTypeCol = self::firstExistingColumn(['sender_type', 'type', 'typing']);
        $messageCol = self::firstExistingColumn(['message_text', 'message']);
        $sentAtCol = self::firstExistingColumn(['sent_at', 'created_at']);
        $isReadCol = self::firstExistingColumn(['is_read']);

        return implode(', ', [
            $idCol . ' AS id',
            ($senderTypeCol ? $senderTypeCol : "''") . ' AS sender_type',
            ($messageCol ? $messageCol : "''") . ' AS message_text',
            ($sentAtCol ? $sentAtCol : "''") . ' AS sent_at',
            ($isReadCol ? $isReadCol : '0') . ' AS is_read',
        ]);
    }

    private static function threadWhereSql(string $safeNic): string
    {
        $cols = self::getChatColumns();
        $senderCol = self::participantExpr(['sender_id', 'sender']);
        $receiverCol = self::participantExpr(['receiver_id', 'receiver']);

        if ($senderCol === null || $receiverCol === null) {
            return '1=0';
        }

        $clauses = [];

        // Patient -> pharmacist
        $clauses[] = "($senderCol = '$safeNic' AND ($receiverCol = 'PHARMACIST' OR $receiverCol = 'pharmacist'))";

        // Pharmacist -> patient (legacy/new schemas)
        if (isset($cols['sender_type'])) {
            $clauses[] = "(sender_type = 'pharmacist' AND $receiverCol = '$safeNic')";
        }
        if (isset($cols['type'])) {
            $clauses[] = "(type = 'pharmacist' AND $receiverCol = '$safeNic')";
        }
        if (isset($cols['typing'])) {
            $clauses[] = "(typing = 'pharmacist' AND $receiverCol = '$safeNic')";
        }

        // Legacy fallback for rows without explicit role columns.
        $clauses[] = "($receiverCol = '$safeNic' AND $senderCol <> '$safeNic')";

        return implode(' OR ', $clauses);
    }

    private static function shouldWriteFetchDebug(): bool
    {
        $now = time();
        if (self::$lastDebugLogAt === null || ($now - self::$lastDebugLogAt) >= 20) {
            self::$lastDebugLogAt = $now;
            return true;
        }
        return false;
    }

    public static function canUseMessages(): bool
    {
        return self::tableExists('chat_messages');
    }

    public static function getMessages(string $patientNic): array
    {
        if (!self::canUseMessages()) {
            return [];
        }
        if (trim($patientNic) === '') {
            self::writeLog('messages-error.log', 'ERROR', 'Patient getMessages called with empty NIC.', []);
            return [];
        }

        $safeNic = Database::escape($patientNic);
        $threadWhere = self::threadWhereSql($safeNic);
        $query = "
            SELECT " . self::messageSelectSql() . "
            FROM chat_messages
            WHERE (" . $threadWhere . ")
              " . self::chatPharmacyFilter() . "
            ORDER BY sent_at ASC, id ASC
            LIMIT 300
        ";

        $startedAt = microtime(true);
        $rs = Database::search($query);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        if (!($rs instanceof mysqli_result)) {
            self::writeLog('messages-error.log', 'ERROR', 'Patient getMessages query failed.', [
                'patient_nic' => $patientNic,
                'duration_ms' => $durationMs,
                'db_error' => Database::$connection->error ?? null,
            ]);
            return [];
        }

        $rows = [];
        $pharmacistCount = 0;
        while ($row = $rs->fetch_assoc()) {
            $senderType = strtolower(trim((string) ($row['sender_type'] ?? '')));
            if ($senderType === 'pharmacist') {
                $pharmacistCount++;
            }
            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'senderType' => (string) ($row['sender_type'] ?? ''),
                'text' => (string) ($row['message_text'] ?? ''),
                'sentAt' => (string) ($row['sent_at'] ?? ''),
            ];
        }

        // Fallback: if pharmacy-scoped query returns no pharmacist replies, try without pharmacy filter.
        if (empty($rows) && self::hasChatPharmacy() && self::currentPharmacyId() > 0) {
            $fallbackQuery = "
                SELECT " . self::messageSelectSql() . "
                FROM chat_messages
                WHERE (" . $threadWhere . ")
                ORDER BY sent_at ASC, id ASC
                LIMIT 300
            ";
            $fallbackStartedAt = microtime(true);
            $fallbackRs = Database::search($fallbackQuery);
            $fallbackDurationMs = (int) round((microtime(true) - $fallbackStartedAt) * 1000);
            if ($fallbackRs instanceof mysqli_result) {
                $fallbackRows = [];
                $fallbackPharmacistCount = 0;
                while ($fallbackRow = $fallbackRs->fetch_assoc()) {
                    $senderType = strtolower(trim((string) ($fallbackRow['sender_type'] ?? '')));
                    if ($senderType === 'pharmacist') {
                        $fallbackPharmacistCount++;
                    }
                    $fallbackRows[] = [
                        'id' => (int) ($fallbackRow['id'] ?? 0),
                        'senderType' => (string) ($fallbackRow['sender_type'] ?? ''),
                        'text' => (string) ($fallbackRow['message_text'] ?? ''),
                        'sentAt' => (string) ($fallbackRow['sent_at'] ?? ''),
                    ];
                }
                if (!empty($fallbackRows)) {
                    $rows = $fallbackRows;
                    $pharmacistCount = $fallbackPharmacistCount;
                    self::writeLog('messages-debug.log', 'DEBUG', 'Patient getMessages used fallback without pharmacy filter.', [
                        'patient_nic' => $patientNic,
                        'pharmacy_id' => self::currentPharmacyId(),
                        'duration_ms' => $durationMs,
                        'fallback_duration_ms' => $fallbackDurationMs,
                        'result_count' => count($rows),
                        'pharmacist_count' => $pharmacistCount,
                    ]);
                }
            } else {
                self::writeLog('messages-error.log', 'ERROR', 'Patient getMessages fallback query failed.', [
                    'patient_nic' => $patientNic,
                    'duration_ms' => $fallbackDurationMs,
                    'db_error' => Database::$connection->error ?? null,
                ]);
            }
        }

        if ($durationMs >= 1500 || self::shouldWriteFetchDebug()) {
            self::writeLog('messages-debug.log', 'DEBUG', 'Patient getMessages completed.', [
                'patient_nic' => $patientNic,
                'pharmacy_id' => self::currentPharmacyId(),
                'duration_ms' => $durationMs,
                'result_count' => count($rows),
                'pharmacist_count' => $pharmacistCount,
            ]);
        }
        return $rows;
    }

    public static function sendMessage(string $patientNic, string $message): bool
    {
        if (!self::canUseMessages()) {
            return false;
        }

        $trimmed = trim($message);
        if ($trimmed === '') {
            return false;
        }

        $safeNic = Database::escape($patientNic);
        $safeMessage = Database::escape($trimmed);
        $cols = self::getChatColumns();

        $insertCols = [];
        $insertVals = [];

        if (isset($cols['sender_type'])) {
            $insertCols[] = 'sender_type';
            $insertVals[] = "'patient'";
        }
        if (isset($cols['typing'])) {
            $insertCols[] = 'typing';
            $insertVals[] = "'patient'";
        }
        if (isset($cols['type'])) {
            $insertCols[] = 'type';
            $insertVals[] = "'patient'";
        }

        if (isset($cols['sender_id'])) {
            $insertCols[] = 'sender_id';
            $insertVals[] = "'$safeNic'";
        }
        if (isset($cols['sender'])) {
            $insertCols[] = 'sender';
            $insertVals[] = "'$safeNic'";
        }

        if (isset($cols['receiver_id'])) {
            $insertCols[] = 'receiver_id';
            $insertVals[] = "'PHARMACIST'";
        }
        if (isset($cols['receiver'])) {
            $insertCols[] = 'receiver';
            $insertVals[] = "'PHARMACIST'";
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
        if (isset($cols['pharmacy_id']) && self::currentPharmacyId() > 0) {
            $insertCols[] = 'pharmacy_id';
            $insertVals[] = (string) self::currentPharmacyId();
        }

        if (empty($insertCols)) {
            self::writeLog('messages-error.log', 'ERROR', 'Patient sendMessage aborted due missing insert columns.', [
                'patient_nic' => $patientNic,
            ]);
            return false;
        }

        $sql = "INSERT INTO chat_messages (" . implode(', ', $insertCols) . ")
                VALUES (" . implode(', ', $insertVals) . ")";
        $ok = Database::iud($sql);
        if (!$ok) {
            self::writeLog('messages-error.log', 'ERROR', 'Patient sendMessage insert failed.', [
                'patient_nic' => $patientNic,
                'receiver' => 'PHARMACIST',
                'message_len' => strlen($trimmed),
                'db_error' => Database::$connection->error ?? null,
            ]);
        } else {
            self::writeLog('messages-debug.log', 'DEBUG', 'Patient message sent.', [
                'patient_nic' => $patientNic,
                'message_len' => strlen($trimmed),
                'pharmacy_id' => self::currentPharmacyId(),
            ]);
        }
        return $ok;
    }

    public static function markPharmacistMessagesRead(string $patientNic): void
    {
        if (!self::canUseMessages()) {
            return;
        }

        $safeNic = Database::escape($patientNic);
        $senderCol = self::participantExpr(['sender_id', 'sender']);
        $receiverCol = self::participantExpr(['receiver_id', 'receiver']);
        $cols = self::getChatColumns();
        if ($senderCol === null || $receiverCol === null || !isset($cols['is_read'])) {
            return;
        }

        $roleClauses = [];
        if (isset($cols['sender_type'])) {
            $roleClauses[] = "sender_type = 'pharmacist'";
        }
        if (isset($cols['type'])) {
            $roleClauses[] = "type = 'pharmacist'";
        }
        if (isset($cols['typing'])) {
            $roleClauses[] = "typing = 'pharmacist'";
        }
        // Legacy fallback for rows where sender role is not set.
        $roleClauses[] = "$senderCol <> '$safeNic'";

        Database::iud("
            UPDATE chat_messages
            SET is_read = 1
            WHERE $receiverCol = '$safeNic'
              AND (" . implode(' OR ', $roleClauses) . ")
              AND is_read = 0
              " . self::chatPharmacyFilter() . "
        ");
    }

    public static function getActiveMedsForToday(string $patientNic): array
    {
        $safeNic = Database::escape($patientNic);
        $today = date('Y-m-d');
        $safeDate = Database::escape($today);
        $rows = [];

        if (self::tableExists('medication_schedules')) {
            $rs = Database::search("
                SELECT m.name AS medicine_name, ms.dosage, ms.frequency, ms.meal_timing
                FROM medication_schedules ms
                JOIN medicines m ON ms.medicine_id = m.id
                WHERE ms.patient_nic = '$safeNic'
                  AND ms.schedule_date = '$safeDate'
                  AND " . (PharmacyContext::tableHasPharmacyId('medication_schedules') && self::currentPharmacyId() > 0 ? "ms.pharmacy_id = " . self::currentPharmacyId() : "1=1") . "
                ORDER BY FIELD(ms.frequency,'MORNING','AFTERNOON','EVENING','NIGHT')
            ");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
            if (!empty($rows)) {
                return $rows;
            }
        }

        if (self::tableExists('medication_schedule') && self::tableExists('schedule_master')) {
            $rs = Database::search("
                SELECT
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    COALESCE(dc.label, '-') AS dosage,
                    COALESCE(f.label, '-') AS frequency,
                    COALESCE(mt.label, '-') AS meal_timing
                FROM medication_schedule ms
                JOIN schedule_master sm ON sm.id = ms.schedule_master_id
                LEFT JOIN medicines m ON ms.medicine_id = m.id
                LEFT JOIN dosage_categories dc ON ms.dosage_id = dc.id
                LEFT JOIN frequencies f ON ms.frequency_id = f.id
                LEFT JOIN meal_timing mt ON ms.meal_timing_id = mt.id
                WHERE sm.patient_nic = '$safeNic'
                  AND '$safeDate' BETWEEN ms.start_date
                                      AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
                  AND " . (PharmacyContext::tableHasPharmacyId('schedule_master') && self::currentPharmacyId() > 0 ? "sm.pharmacy_id = " . self::currentPharmacyId() : "1=1") . "
                ORDER BY ms.id ASC
            ");
        } else {
            return [];
        }

        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}
