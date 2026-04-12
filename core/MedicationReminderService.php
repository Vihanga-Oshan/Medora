<?php

/**
 * MedicationReminderService
 * Creates dose-level reminder events and delivers due APP notifications.
 */
class MedicationReminderService
{
    private const TABLE = 'medication_reminder_events';

    public static function ensureSchema(): void
    {
        if (!self::tableExists(self::TABLE)) {
            Database::iud("
                CREATE TABLE IF NOT EXISTS medication_reminder_events (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    patient_nic VARCHAR(50) NOT NULL,
                    source_type VARCHAR(20) NOT NULL,
                    source_schedule_id INT NOT NULL,
                    dose_date DATE NOT NULL,
                    time_slot VARCHAR(20) NOT NULL,
                    scheduled_at DATETIME NOT NULL,
                    message TEXT NOT NULL,
                    status ENUM('PENDING','TAKEN','MISSED') NOT NULL DEFAULT 'PENDING',
                    delivered_at DATETIME NULL,
                    delivered_notification_id INT NULL,
                    pharmacy_id INT NULL,
                    taken_at DATETIME NULL,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_patient_due (patient_nic, scheduled_at, status),
                    INDEX idx_source (source_type, source_schedule_id, dose_date),
                    INDEX idx_notification (delivered_notification_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    }

    public static function createEventsForSchedule(array $payload): void
    {
        self::ensureSchema();

        $patientNic = trim((string)($payload['patient_nic'] ?? ''));
        $sourceType = trim((string)($payload['source_type'] ?? ''));
        $sourceId = (int)($payload['source_schedule_id'] ?? 0);
        $doseDate = trim((string)($payload['dose_date'] ?? ''));
        $message = trim((string)($payload['message'] ?? ''));
        $timesOfDay = trim((string)($payload['times_of_day'] ?? ''));
        $frequencyLabel = trim((string)($payload['frequency_label'] ?? ''));
        $pharmacyId = (int)($payload['pharmacy_id'] ?? 0);

        if ($patientNic === '' || $sourceType === '' || $sourceId <= 0 || $doseDate === '') {
            return;
        }

        $slots = self::resolveSlots($timesOfDay, $frequencyLabel);
        if (empty($slots)) {
            $slots = ['morning'];
        }

        foreach ($slots as $slot) {
            $scheduledAt = $doseDate . ' ' . self::slotTime($slot) . ':00';
            $eventMessage = $message !== '' ? $message : self::defaultMessage($slot);
            $safeMessage = self::appendSlot($eventMessage, $slot);

            $sql = "
                INSERT INTO " . self::TABLE . " (
                    patient_nic, source_type, source_schedule_id, dose_date, time_slot, scheduled_at, message, status, pharmacy_id
                )
                SELECT ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?
                FROM DUAL
                WHERE NOT EXISTS (
                    SELECT 1 FROM " . self::TABLE . "
                    WHERE patient_nic = ?
                      AND source_type = ?
                      AND source_schedule_id = ?
                      AND dose_date = ?
                      AND time_slot = ?
                    LIMIT 1
                )
            ";
            Database::execute(
                $sql,
                'ssissssisssis',
                [
                    $patientNic,
                    $sourceType,
                    $sourceId,
                    $doseDate,
                    $slot,
                    $scheduledAt,
                    $safeMessage,
                    $pharmacyId,
                    $patientNic,
                    $sourceType,
                    $sourceId,
                    $doseDate,
                    $slot
                ]
            );
        }
    }

    public static function deliverDueReminders(string $patientNic): void
    {
        self::ensureSchema();

        $patientNic = trim($patientNic);
        if ($patientNic === '' || !self::tableExists('notifications')) {
            return;
        }

        $where = "e.patient_nic = ? AND e.status = 'PENDING' AND e.delivered_at IS NULL AND e.scheduled_at <= NOW()";
        $types = 's';
        $params = [$patientNic];

        $pid = PharmacyContext::selectedPharmacyId();
        if ($pid > 0 && self::columnExists(self::TABLE, 'pharmacy_id')) {
            $where .= " AND (e.pharmacy_id IS NULL OR e.pharmacy_id = ?)";
            $types .= 'i';
            $params[] = $pid;
        }

        $events = Database::fetchAll("
            SELECT e.id, e.message, e.time_slot, e.pharmacy_id
            FROM " . self::TABLE . " e
            WHERE $where
            ORDER BY e.scheduled_at ASC, e.id ASC
            LIMIT 50
        ", $types, $params);

        if (empty($events)) {
            return;
        }

        foreach ($events as $event) {
            $eventId = (int)($event['id'] ?? 0);
            if ($eventId <= 0) {
                continue;
            }

            $message = trim((string)($event['message'] ?? 'Medication reminder'));
            $pharmacyId = (int)($event['pharmacy_id'] ?? 0);

            if (PharmacyContext::tableHasPharmacyId('notifications') && $pharmacyId > 0) {
                $ok = Database::execute(
                    "INSERT INTO notifications (patient_nic, message, type, is_read, created_at, pharmacy_id) VALUES (?, ?, 'REMINDER', 0, NOW(), ?)",
                    'ssi',
                    [$patientNic, $message, $pharmacyId]
                );
            } else {
                $ok = Database::execute(
                    "INSERT INTO notifications (patient_nic, message, type, is_read, created_at) VALUES (?, ?, 'REMINDER', 0, NOW())",
                    'ss',
                    [$patientNic, $message]
                );
            }

            if (!$ok) {
                continue;
            }

            $notificationId = (int)(Database::$connection->insert_id ?? 0);
            Database::execute(
                "UPDATE " . self::TABLE . " SET delivered_at = NOW(), delivered_notification_id = ? WHERE id = ? AND delivered_at IS NULL",
                'ii',
                [$notificationId, $eventId]
            );
        }
    }

    public static function markTakenFromEvent(int $eventId, string $patientNic): bool
    {
        self::ensureSchema();
        $event = Database::fetchOne("
            SELECT id, source_type, source_schedule_id, dose_date, time_slot, status, pharmacy_id
            FROM " . self::TABLE . "
            WHERE id = ? AND patient_nic = ?
            LIMIT 1
        ", 'is', [$eventId, $patientNic]);

        if (!$event) {
            return false;
        }

        if (strtoupper((string)($event['status'] ?? '')) === 'TAKEN') {
            return true;
        }

        $ok = Database::execute(
            "UPDATE " . self::TABLE . " SET status = 'TAKEN', taken_at = NOW(), updated_at = NOW() WHERE id = ? AND patient_nic = ?",
            'is',
            [$eventId, $patientNic]
        );
        if (!$ok) {
            return false;
        }

        $sourceType = (string)($event['source_type'] ?? '');
        $sourceId = (int)($event['source_schedule_id'] ?? 0);
        $doseDate = (string)($event['dose_date'] ?? date('Y-m-d'));
        $slot = (string)($event['time_slot'] ?? 'general');
        $pharmacyId = (int)($event['pharmacy_id'] ?? 0);

        if ($sourceType === 'legacy' && $sourceId > 0 && self::tableExists('medication_log')) {
            $existing = Database::fetchOne(
                "SELECT id FROM medication_log WHERE medication_schedule_id = ? AND patient_nic = ? AND dose_date = ? AND time_slot = ? LIMIT 1",
                'isss',
                [$sourceId, $patientNic, $doseDate, $slot]
            );
            if ($existing) {
                $logId = (int)($existing['id'] ?? 0);
                if ($logId > 0) {
                    Database::execute("UPDATE medication_log SET status = 'TAKEN', updated_at = NOW() WHERE id = ?", 'i', [$logId]);
                }
            } else {
                if (PharmacyContext::tableHasPharmacyId('medication_log') && $pharmacyId > 0) {
                    Database::execute(
                        "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at, pharmacy_id)
                         VALUES (?, ?, ?, 'TAKEN', ?, NOW(), ?)",
                        'isssi',
                        [$sourceId, $patientNic, $doseDate, $slot, $pharmacyId]
                    );
                } else {
                    Database::execute(
                        "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at)
                         VALUES (?, ?, ?, 'TAKEN', ?, NOW())",
                        'isss',
                        [$sourceId, $patientNic, $doseDate, $slot]
                    );
                }
            }
        }

        if ($sourceType === 'expanded' && $sourceId > 0 && self::tableExists('medication_schedules')) {
            $pending = Database::fetchOne(
                "SELECT COUNT(*) AS c FROM " . self::TABLE . " WHERE source_type = 'expanded' AND source_schedule_id = ? AND dose_date = ? AND status <> 'TAKEN'",
                'is',
                [$sourceId, $doseDate]
            );
            $remaining = (int)($pending['c'] ?? 0);
            if ($remaining <= 0 && self::columnExists('medication_schedules', 'status')) {
                Database::execute("UPDATE medication_schedules SET status = 'TAKEN' WHERE id = ? AND patient_nic = ?", 'is', [$sourceId, $patientNic]);
            }
        }

        return true;
    }

    private static function resolveSlots(string $timesOfDay, string $frequencyLabel): array
    {
        $slots = [];
        $raw = strtolower(trim($timesOfDay));
        if ($raw !== '') {
            $tokens = preg_split('/\s*,\s*/', $raw) ?: [];
            foreach ($tokens as $token) {
                $slot = self::normalizeSlot($token);
                if ($slot !== null) {
                    $slots[] = $slot;
                }
            }
        }

        if (!empty($slots)) {
            return array_values(array_unique($slots));
        }

        $label = strtolower(trim($frequencyLabel));
        if ($label === '') {
            return [];
        }

        if (str_contains($label, 'morning')) $slots[] = 'morning';
        if (str_contains($label, 'day')) $slots[] = 'day';
        if (str_contains($label, 'night')) $slots[] = 'night';
        if (empty($slots)) $slots[] = 'morning';

        return array_values(array_unique($slots));
    }

    private static function normalizeSlot(string $token): ?string
    {
        $t = strtolower(trim($token));
        if ($t === 'morning') return 'morning';
        if ($t === 'day' || $t === 'daytime' || $t === 'afternoon' || $t === 'noon') return 'day';
        if ($t === 'night' || $t === 'evening') return 'night';
        return null;
    }

    private static function slotTime(string $slot): string
    {
        if ($slot === 'night') return '18:00';
        if ($slot === 'day') return '13:00';
        return '08:00';
    }

    private static function defaultMessage(string $slot): string
    {
        return 'Time to take your ' . ucfirst($slot) . ' medication dose.';
    }

    private static function appendSlot(string $message, string $slot): string
    {
        $suffix = ' [' . strtoupper($slot) . ']';
        if (str_contains($message, $suffix)) {
            return $message;
        }
        return rtrim($message) . $suffix;
    }

    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        if (!self::tableExists($table)) {
            return false;
        }
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $safeColumn = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeColumn'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }
}

