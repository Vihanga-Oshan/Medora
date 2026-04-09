<?php

class PatientMessagesModel
{
    private static ?array $chatColumnsCache = null;
    private static function currentPharmacyId(): int
    {
        return PharmacyContext::selectedPharmacyId();
    }

    private static function hasChatPharmacy(): bool
    {
        $cols = self::getChatColumns();
        return isset($cols['pharmacy_id']);
    }

    private static function tableExists(string $name): bool
    {
        $safe = Database::escape($name);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function getChatColumns(): array
    {
        if (self::$chatColumnsCache !== null) {
            return self::$chatColumnsCache;
        }

        $cols = [];
        if (!self::tableExists('chat_messages')) {
            self::$chatColumnsCache = $cols;
            return $cols;
        }

        $rs = Database::search("SHOW COLUMNS FROM chat_messages");
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $field = strtolower((string)($row['Field'] ?? ''));
                if ($field !== '') {
                    $cols[$field] = true;
                }
            }
        }

        self::$chatColumnsCache = $cols;
        return $cols;
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

        $safeNic = Database::escape($patientNic);
        $rs = Database::search("
            SELECT id, sender_type, sender_id, receiver_id, message_text, sent_at, is_read
            FROM chat_messages
            WHERE (
                (sender_id = '$safeNic' AND receiver_id = 'PHARMACIST')
                OR (sender_type = 'pharmacist' AND receiver_id = '$safeNic')
            )
              " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND pharmacy_id = " . self::currentPharmacyId() : '') . "
            ORDER BY sent_at ASC, id ASC
            LIMIT 300
        ");

        $rows = [];
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = [
                    'id' => (int)($row['id'] ?? 0),
                    'senderType' => (string)($row['sender_type'] ?? ''),
                    'text' => (string)($row['message_text'] ?? ''),
                    'sentAt' => (string)($row['sent_at'] ?? ''),
                ];
            }
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
        } elseif (isset($cols['sender'])) {
            $insertCols[] = 'sender';
            $insertVals[] = "'$safeNic'";
        }

        if (isset($cols['receiver_id'])) {
            $insertCols[] = 'receiver_id';
            $insertVals[] = "'PHARMACIST'";
        } elseif (isset($cols['receiver'])) {
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
            $insertVals[] = (string)self::currentPharmacyId();
        }

        if (empty($insertCols)) {
            return false;
        }

        $sql = "INSERT INTO chat_messages (" . implode(', ', $insertCols) . ")
                VALUES (" . implode(', ', $insertVals) . ")";
        return Database::iud($sql);
    }

    public static function markPharmacistMessagesRead(string $patientNic): void
    {
        if (!self::canUseMessages()) {
            return;
        }

        $safeNic = Database::escape($patientNic);
        Database::iud("
            UPDATE chat_messages
            SET is_read = 1
            WHERE receiver_id = '$safeNic'
              AND sender_type = 'pharmacist'
              AND is_read = 0
              " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND pharmacy_id = " . self::currentPharmacyId() : '') . "
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
