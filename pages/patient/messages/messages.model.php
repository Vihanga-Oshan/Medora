<?php

require_once ROOT . '/core/AppLogger.php';
require_once ROOT . '/core/ChatMessageSupport.php';

class PatientMessagesModel
{
    private static function writeLog(string $file, string $level, string $message, array $context = []): void
    {
        AppLogger::write($file, $level, $message, $context);
    }

    private static function currentPharmacyId(): int
    {
        return PharmacyContext::selectedPharmacyId();
    }

    private static function hasChatPharmacy(): bool
    {
        return ChatMessageSupport::hasColumn('pharmacy_id');
    }

    private static function chatPharmacyFilter(): string
    {
        if (!self::hasChatPharmacy() || self::currentPharmacyId() <= 0) {
            return '';
        }
        return "AND (pharmacy_id IS NULL OR pharmacy_id = 0 OR pharmacy_id = " . self::currentPharmacyId() . ")";
    }

    private static function threadWhere(string $patientNic): array
    {
        $senderCol = ChatMessageSupport::participantExpr(['sender_id', 'sender']);
        $receiverCol = ChatMessageSupport::participantExpr(['receiver_id', 'receiver']);

        if ($senderCol === null || $receiverCol === null) {
            return ['sql' => '1=0', 'types' => '', 'params' => []];
        }

        $clauses = [];
        $types = '';
        $params = [];

        // Patient -> pharmacist
        $clauses[] = "($senderCol = ? AND ($receiverCol = 'PHARMACIST' OR $receiverCol = 'pharmacist'))";
        $types .= 's';
        $params[] = $patientNic;

        // Pharmacist -> patient (legacy/new schemas)
        if (ChatMessageSupport::hasColumn('sender_type')) {
            $clauses[] = "(sender_type = 'pharmacist' AND $receiverCol = ?)";
            $types .= 's';
            $params[] = $patientNic;
        }
        if (ChatMessageSupport::hasColumn('type')) {
            $clauses[] = "(type = 'pharmacist' AND $receiverCol = ?)";
            $types .= 's';
            $params[] = $patientNic;
        }
        if (ChatMessageSupport::hasColumn('typing')) {
            $clauses[] = "(typing = 'pharmacist' AND $receiverCol = ?)";
            $types .= 's';
            $params[] = $patientNic;
        }

        // Legacy fallback for rows without explicit role columns.
        $clauses[] = "($receiverCol = ? AND $senderCol <> ?)";
        $types .= 'ss';
        $params[] = $patientNic;
        $params[] = $patientNic;

        return ['sql' => implode(' OR ', $clauses), 'types' => $types, 'params' => $params];
    }

    public static function getMessages(string $patientNic): array
    {
        if (trim($patientNic) === '') {
            self::writeLog('messages-error.log', 'ERROR', 'Patient getMessages called with empty NIC.', []);
            return [];
        }

        $threadWhere = self::threadWhere($patientNic);
        $query = "
            SELECT " . ChatMessageSupport::messageSelectSql() . "
            FROM chat_messages
            WHERE (" . $threadWhere['sql'] . ")
              " . self::chatPharmacyFilter() . "
            ORDER BY sent_at ASC, id ASC
            LIMIT 300
        ";

        $queryResult = ChatMessageSupport::fetchAllTimed($query, $threadWhere['types'], $threadWhere['params']);
        if ($queryResult['failed']) {
            self::writeLog('messages-error.log', 'ERROR', 'Patient getMessages query failed.', [
                'patient_nic' => $patientNic,
                'duration_ms' => $queryResult['duration_ms'],
                'db_error' => $queryResult['db_error'],
            ]);
            return [];
        }

        $durationMs = $queryResult['duration_ms'];
        $normalized = ChatMessageSupport::mapPatientThreadRows($queryResult['rows']);
        $rows = $normalized['rows'];
        $pharmacistCount = $normalized['pharmacist_count'];

        // Fallback: if pharmacy-scoped query returns no pharmacist replies, try without pharmacy filter.
        if (empty($rows) && self::hasChatPharmacy() && self::currentPharmacyId() > 0) {
            $fallbackQuery = "
                SELECT " . ChatMessageSupport::messageSelectSql() . "
                FROM chat_messages
                WHERE (" . $threadWhere['sql'] . ")
                ORDER BY sent_at ASC, id ASC
                LIMIT 300
            ";
            $fallbackResult = ChatMessageSupport::fetchAllTimed($fallbackQuery, $threadWhere['types'], $threadWhere['params']);
            if (!empty($fallbackResult['rows'])) {
                $fallbackNormalized = ChatMessageSupport::mapPatientThreadRows($fallbackResult['rows']);
                $fallbackRows = $fallbackNormalized['rows'];
                $fallbackPharmacistCount = $fallbackNormalized['pharmacist_count'];
                if (!empty($fallbackRows)) {
                    $rows = $fallbackRows;
                    $pharmacistCount = $fallbackPharmacistCount;
                    self::writeLog('messages-debug.log', 'DEBUG', 'Patient getMessages used fallback without pharmacy filter.', [
                        'patient_nic' => $patientNic,
                        'pharmacy_id' => self::currentPharmacyId(),
                        'duration_ms' => $durationMs,
                        'fallback_duration_ms' => $fallbackResult['duration_ms'],
                        'result_count' => count($rows),
                        'pharmacist_count' => $pharmacistCount,
                    ]);
                }
            } elseif ($fallbackResult['failed']) {
                self::writeLog('messages-error.log', 'ERROR', 'Patient getMessages fallback query failed.', [
                    'patient_nic' => $patientNic,
                    'duration_ms' => $fallbackResult['duration_ms'],
                    'db_error' => $fallbackResult['db_error'],
                ]);
            }
        }

        if ($durationMs >= 1500 || ChatMessageSupport::shouldWriteDebug('patient_get_messages')) {
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
        $trimmed = trim($message);
        if ($trimmed === '') {
            return false;
        }

        $insert = ChatMessageSupport::buildInsertParts([
            'sender_type' => 'patient',
            'typing' => 'patient',
            'type' => 'patient',
            'sender_id' => $patientNic,
            'sender' => $patientNic,
            'receiver_id' => 'PHARMACIST',
            'receiver' => 'PHARMACIST',
            'message_text' => $trimmed,
            'message' => $trimmed,
            'pharmacy_id' => self::currentPharmacyId(),
        ]);

        if (empty($insert['columns'])) {
            self::writeLog('messages-error.log', 'ERROR', 'Patient sendMessage aborted due missing insert columns.', [
                'patient_nic' => $patientNic,
            ]);
            return false;
        }

        $sql = "INSERT INTO chat_messages (" . implode(', ', $insert['columns']) . ")
                VALUES (" . implode(', ', $insert['values']) . ")";
        $ok = Database::execute($sql, $insert['types'], $insert['params']);
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
        $senderCol = ChatMessageSupport::participantExpr(['sender_id', 'sender']);
        $receiverCol = ChatMessageSupport::participantExpr(['receiver_id', 'receiver']);
        if ($senderCol === null || $receiverCol === null || !ChatMessageSupport::hasColumn('is_read')) {
            return;
        }

        $roleClauses = [];
        if (ChatMessageSupport::hasColumn('sender_type')) {
            $roleClauses[] = "sender_type = 'pharmacist'";
        }
        if (ChatMessageSupport::hasColumn('type')) {
            $roleClauses[] = "type = 'pharmacist'";
        }
        if (ChatMessageSupport::hasColumn('typing')) {
            $roleClauses[] = "typing = 'pharmacist'";
        }
        // Legacy fallback for rows where sender role is not set.
        $roleClauses[] = "$senderCol <> ?";

        Database::execute("
            UPDATE chat_messages
            SET is_read = 1
            WHERE $receiverCol = ?
              AND (" . implode(' OR ', $roleClauses) . ")
              AND is_read = 0
              " . self::chatPharmacyFilter() . "
        ", 'ss', [$patientNic, $patientNic]);
    }

    public static function getActiveMedsForToday(string $patientNic): array
    {
        $today = date('Y-m-d');
        return Database::fetchAll("
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
            WHERE sm.patient_nic = ?
              AND ? BETWEEN ms.start_date
                                  AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
              AND " . (PharmacyContext::tableHasPharmacyId('schedule_master') && self::currentPharmacyId() > 0 ? "sm.pharmacy_id = " . self::currentPharmacyId() : "1=1") . "
            ORDER BY ms.id ASC
        ", 'ss', [$patientNic, $today]);
    }
}
