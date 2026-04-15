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

        $patientNic = trim((string) ($payload['patient_nic'] ?? ''));
        $sourceType = trim((string) ($payload['source_type'] ?? ''));
        $sourceId = (int) ($payload['source_schedule_id'] ?? 0);
        $doseDate = trim((string) ($payload['dose_date'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));
        $timesOfDay = trim((string) ($payload['times_of_day'] ?? ''));
        $frequencyLabel = trim((string) ($payload['frequency_label'] ?? ''));
        $pharmacyId = (int) ($payload['pharmacy_id'] ?? 0);

        if ($patientNic === '' || $sourceType === '' || $sourceId <= 0 || $doseDate === '') {
            return;
        }

        $slots = self::resolveSlots($timesOfDay, $frequencyLabel);
        if (empty($slots)) {
            $slots = ['morning'];
        }

        $safeSlots = array_map(static fn($s) => "'" . Database::escape((string)$s) . "'", $slots);
        if (!empty($safeSlots)) {
            $slotList = implode(', ', $safeSlots);
            Database::execute(
                "DELETE FROM " . self::TABLE . "
                 WHERE patient_nic = ?
                   AND source_type = ?
                   AND source_schedule_id = ?
                   AND dose_date = ?
                   AND status = 'PENDING'
                   AND delivered_at IS NULL
                   AND time_slot NOT IN ($slotList)",
                'ssis',
                [$patientNic, $sourceType, $sourceId, $doseDate]
            );
        }

        foreach ($slots as $slot) {
            $scheduledAt = $doseDate . ' ' . self::slotTime($slot) . ':00';
            $eventMessage = $message !== '' ? $message : self::defaultMessage($slot);
            $safeMessage = self::messageForSlot($eventMessage, $slot);

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
                'ssissssississ',
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

    public static function getDoseRowsByDate(string $patientNic, string $date): array
    {
        self::ensureSchema();
        if (!self::tableExists(self::TABLE)) {
            return [];
        }

        self::backfillEventsForPatientDate($patientNic, $date);

        $nic = Database::escape($patientNic);
        $day = Database::escape($date);
        $pid = PharmacyContext::selectedPharmacyId();
        $eventFilter = "e.patient_nic = '$nic' AND e.dose_date = '$day'";
        if ($pid > 0 && self::columnExists(self::TABLE, 'pharmacy_id')) {
            $eventFilter .= " AND (e.pharmacy_id IS NULL OR e.pharmacy_id = " . (int) $pid . ")";
        }

        $rows = [];

        if (self::tableExists('medication_schedules')) {
            $sql = "
                SELECT
                    e.id AS reminder_event_id,
                    ms.id AS id,
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    COALESCE(ms.dosage, '-') AS dosage,
                    '' AS frequency_raw,
                    e.time_slot AS frequency_slot,
                    COALESCE(ms.meal_timing, '-') AS meal_timing,
                    COALESCE(ms.instructions, '') AS instructions,
                    e.dose_date AS schedule_date,
                    UPPER(e.status) AS status,
                    e.scheduled_at
                FROM " . self::TABLE . " e
                JOIN medication_schedules ms
                  ON e.source_type = 'expanded' AND ms.id = e.source_schedule_id
                LEFT JOIN medicines m ON ms.medicine_id = m.id
                WHERE $eventFilter
                  AND e.source_type = 'expanded'
            ";
            $rs = Database::search($sql);
            if ($rs instanceof mysqli_result) {
                while ($r = $rs->fetch_assoc()) {
                    $rows[] = self::normalizeDoseRow($r);
                }
            }
        }

        if (self::tableExists('medication_schedule') && self::tableExists('schedule_master')) {
            $sql = "
                SELECT
                    e.id AS reminder_event_id,
                    ms.id AS id,
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    COALESCE(dc.label, '-') AS dosage,
                    COALESCE(f.label, '-') AS frequency_raw,
                    e.time_slot AS frequency_slot,
                    COALESCE(mt.label, '-') AS meal_timing,
                    COALESCE(ms.instructions, '') AS instructions,
                    e.dose_date AS schedule_date,
                    UPPER(e.status) AS status,
                    e.scheduled_at
                FROM " . self::TABLE . " e
                JOIN medication_schedule ms
                  ON e.source_type = 'legacy' AND ms.id = e.source_schedule_id
                JOIN schedule_master sm ON sm.id = ms.schedule_master_id
                LEFT JOIN medicines m ON ms.medicine_id = m.id
                LEFT JOIN dosage_categories dc ON ms.dosage_id = dc.id
                LEFT JOIN frequencies f ON ms.frequency_id = f.id
                LEFT JOIN meal_timing mt ON ms.meal_timing_id = mt.id
                WHERE $eventFilter
                  AND e.source_type = 'legacy'
                  AND sm.patient_nic = '$nic'
            ";
            if ($pid > 0) {
                if (PharmacyContext::tableHasPharmacyId('schedule_master')) {
                    $sql .= " AND sm.pharmacy_id = " . (int) $pid;
                }
                if (PharmacyContext::tableHasPharmacyId('medication_schedule')) {
                    $sql .= " AND ms.pharmacy_id = " . (int) $pid;
                }
                if (PharmacyContext::tableHasPharmacyId('medicines')) {
                    $sql .= " AND (m.pharmacy_id IS NULL OR m.pharmacy_id = " . (int) $pid . ")";
                }
            }
            $rs = Database::search($sql);
            if ($rs instanceof mysqli_result) {
                while ($r = $rs->fetch_assoc()) {
                    $rows[] = self::normalizeDoseRow($r);
                }
            }
        }

        usort($rows, static function (array $a, array $b): int {
            $ta = strtotime((string) ($a['scheduled_at'] ?? ''));
            $tb = strtotime((string) ($b['scheduled_at'] ?? ''));
            if ($ta === $tb) {
                return ((int) ($a['reminder_event_id'] ?? 0)) <=> ((int) ($b['reminder_event_id'] ?? 0));
            }
            return $ta <=> $tb;
        });

        return $rows;
    }

    public static function deliverDueReminders(string $patientNic): void
    {
        self::ensureSchema();
        self::backfillEventsForPatientDate($patientNic, date('Y-m-d'));

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
            $eventId = (int) ($event['id'] ?? 0);
            if ($eventId <= 0) {
                continue;
            }

            $message = trim((string) ($event['message'] ?? 'Medication reminder'));
            $pharmacyId = (int) ($event['pharmacy_id'] ?? 0);

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

            $notificationId = (int) (Database::$connection->insert_id ?? 0);
            Database::execute(
                "UPDATE " . self::TABLE . " SET delivered_at = NOW(), delivered_notification_id = ? WHERE id = ? AND delivered_at IS NULL",
                'ii',
                [$notificationId, $eventId]
            );
        }
    }

    public static function backfillEventsForPatientDate(string $patientNic, string $date): void
    {
        self::ensureSchema();
        if (!self::tableExists(self::TABLE)) {
            return;
        }

        $patientNic = trim($patientNic);
        $date = trim($date);
        if ($patientNic === '' || $date === '') {
            return;
        }

        $pid = PharmacyContext::selectedPharmacyId();
        $safeNic = Database::escape($patientNic);
        $safeDate = Database::escape($date);

        // Expanded table backfill.
        if (self::tableExists('medication_schedules')) {
            $sql = "
                SELECT
                    ms.id AS schedule_id,
                    ms.patient_nic,
                    ms.schedule_date,
                    COALESCE(ms.frequency, '') AS frequency_label,
                    COALESCE(ms.meal_timing, '') AS meal_timing,
                    COALESCE(ms.dosage, '') AS dosage,
                    COALESCE(ms.instructions, '') AS instructions,
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    " . (self::columnExists('medication_schedules', 'pharmacy_id') ? "COALESCE(ms.pharmacy_id, 0)" : "0") . " AS pharmacy_id
                FROM medication_schedules ms
                LEFT JOIN medicines m ON m.id = ms.medicine_id
                WHERE ms.patient_nic = '$safeNic'
                  AND ms.schedule_date = '$safeDate'
            ";
            if ($pid > 0 && PharmacyContext::tableHasPharmacyId('medication_schedules')) {
                $sql .= " AND ms.pharmacy_id = " . (int) $pid;
            }
            if ($pid > 0 && PharmacyContext::tableHasPharmacyId('medicines')) {
                $sql .= " AND (m.pharmacy_id IS NULL OR m.pharmacy_id = " . (int) $pid . ")";
            }

            $rs = Database::search($sql);
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $message = self::composeReminderMessage(
                        (string) ($row['medicine_name'] ?? 'Medication'),
                        (string) ($row['dosage'] ?? ''),
                        (string) ($row['meal_timing'] ?? '')
                    );
                    self::createEventsForSchedule([
                        'patient_nic' => (string) ($row['patient_nic'] ?? $patientNic),
                        'source_type' => 'expanded',
                        'source_schedule_id' => (int) ($row['schedule_id'] ?? 0),
                        'dose_date' => (string) ($row['schedule_date'] ?? $date),
                        'times_of_day' => '',
                        'frequency_label' => (string) ($row['frequency_label'] ?? ''),
                        'message' => $message,
                        'pharmacy_id' => (int) ($row['pharmacy_id'] ?? 0),
                    ]);
                }
            }
        }

        // Legacy table backfill.
        if (self::tableExists('medication_schedule') && self::tableExists('schedule_master')) {
            $sql = "
                SELECT
                    ms.id AS schedule_id,
                    sm.patient_nic,
                    '$safeDate' AS dose_date,
                    COALESCE(f.times_of_day, '') AS times_of_day,
                    COALESCE(f.label, '') AS frequency_label,
                    COALESCE(mt.label, '') AS meal_timing,
                    COALESCE(dc.label, '') AS dosage,
                    COALESCE(ms.instructions, '') AS instructions,
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    " . (self::columnExists('schedule_master', 'pharmacy_id') ? "COALESCE(sm.pharmacy_id, 0)" : "0") . " AS pharmacy_id
                FROM medication_schedule ms
                JOIN schedule_master sm ON sm.id = ms.schedule_master_id
                LEFT JOIN frequencies f ON f.id = ms.frequency_id
                LEFT JOIN meal_timing mt ON mt.id = ms.meal_timing_id
                LEFT JOIN dosage_categories dc ON dc.id = ms.dosage_id
                LEFT JOIN medicines m ON m.id = ms.medicine_id
                WHERE sm.patient_nic = '$safeNic'
                  AND '$safeDate' BETWEEN ms.start_date
                                  AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
            ";
            if ($pid > 0 && PharmacyContext::tableHasPharmacyId('schedule_master')) {
                $sql .= " AND sm.pharmacy_id = " . (int) $pid;
            }
            if ($pid > 0 && PharmacyContext::tableHasPharmacyId('medication_schedule')) {
                $sql .= " AND ms.pharmacy_id = " . (int) $pid;
            }
            if ($pid > 0 && PharmacyContext::tableHasPharmacyId('medicines')) {
                $sql .= " AND (m.pharmacy_id IS NULL OR m.pharmacy_id = " . (int) $pid . ")";
            }

            $rs = Database::search($sql);
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $message = self::composeReminderMessage(
                        (string) ($row['medicine_name'] ?? 'Medication'),
                        (string) ($row['dosage'] ?? ''),
                        (string) ($row['meal_timing'] ?? '')
                    );
                    self::createEventsForSchedule([
                        'patient_nic' => (string) ($row['patient_nic'] ?? $patientNic),
                        'source_type' => 'legacy',
                        'source_schedule_id' => (int) ($row['schedule_id'] ?? 0),
                        'dose_date' => (string) ($row['dose_date'] ?? $date),
                        'times_of_day' => (string) ($row['times_of_day'] ?? ''),
                        'frequency_label' => (string) ($row['frequency_label'] ?? ''),
                        'message' => $message,
                        'pharmacy_id' => (int) ($row['pharmacy_id'] ?? 0),
                    ]);
                }
            }
        }
    }

    public static function markTakenFromEvent(int $eventId, string $patientNic): bool
    {
        return self::markEventStatus($eventId, $patientNic, 'TAKEN');
    }

    public static function markMissedFromEvent(int $eventId, string $patientNic): bool
    {
        return self::markEventStatus($eventId, $patientNic, 'MISSED');
    }

    private static function markEventStatus(int $eventId, string $patientNic, string $targetStatus): bool
    {
        self::ensureSchema();
        $event = Database::fetchOne("
            SELECT id, source_type, source_schedule_id, dose_date, time_slot, status, pharmacy_id, delivered_notification_id
            FROM " . self::TABLE . "
            WHERE id = ? AND patient_nic = ?
            LIMIT 1
        ", 'is', [$eventId, $patientNic]);

        if (!$event) {
            return false;
        }

        $targetStatus = strtoupper($targetStatus);
        if (!in_array($targetStatus, ['TAKEN', 'MISSED'], true)) {
            return false;
        }

        if (strtoupper((string) ($event['status'] ?? '')) === $targetStatus) {
            return true;
        }

        $ok = Database::execute(
            "UPDATE " . self::TABLE . " SET status = ?, taken_at = NOW(), updated_at = NOW() WHERE id = ? AND patient_nic = ?",
            'sis',
            [$targetStatus, $eventId, $patientNic]
        );
        if (!$ok) {
            return false;
        }

        if ($event['delivered_notification_id'] ?? null) {
            $notifId = (int) $event['delivered_notification_id'];
            if ($notifId > 0 && self::tableExists('notifications')) {
                Database::execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND patient_nic = ?", 'is', [$notifId, $patientNic]);
            }
        }

        $sourceType = (string) ($event['source_type'] ?? '');
        $sourceId = (int) ($event['source_schedule_id'] ?? 0);
        $doseDate = (string) ($event['dose_date'] ?? date('Y-m-d'));
        $slot = (string) ($event['time_slot'] ?? 'general');
        $pharmacyId = (int) ($event['pharmacy_id'] ?? 0);

        if ($sourceType === 'legacy' && $sourceId > 0 && self::tableExists('medication_log')) {
            $existing = Database::fetchOne(
                "SELECT id FROM medication_log WHERE medication_schedule_id = ? AND patient_nic = ? AND dose_date = ? AND time_slot = ? LIMIT 1",
                'isss',
                [$sourceId, $patientNic, $doseDate, $slot]
            );
            if ($existing) {
                $logId = (int) ($existing['id'] ?? 0);
                if ($logId > 0) {
                    Database::execute("UPDATE medication_log SET status = ?, updated_at = NOW() WHERE id = ?", 'si', [$targetStatus, $logId]);
                }
            } else {
                if (PharmacyContext::tableHasPharmacyId('medication_log') && $pharmacyId > 0) {
                    Database::execute(
                        "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at, pharmacy_id)
                         VALUES (?, ?, ?, ?, ?, NOW(), ?)",
                        'issssi',
                        [$sourceId, $patientNic, $doseDate, $targetStatus, $slot, $pharmacyId]
                    );
                } else {
                    Database::execute(
                        "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at)
                         VALUES (?, ?, ?, ?, ?, NOW())",
                        'isss',
                        [$sourceId, $patientNic, $doseDate, $targetStatus, $slot]
                    );
                }
            }
        }

        if ($sourceType === 'expanded' && $sourceId > 0 && self::tableExists('medication_schedules')) {
            $pending = Database::fetchOne(
                "SELECT COUNT(*) AS c FROM " . self::TABLE . " WHERE source_type = 'expanded' AND source_schedule_id = ? AND dose_date = ? AND status = 'PENDING'",
                'is',
                [$sourceId, $doseDate]
            );
            $remaining = (int) ($pending['c'] ?? 0);
            if ($remaining <= 0 && self::columnExists('medication_schedules', 'status')) {
                $final = Database::fetchOne(
                    "SELECT COUNT(*) AS c FROM " . self::TABLE . " WHERE source_type = 'expanded' AND source_schedule_id = ? AND dose_date = ? AND status = 'MISSED'",
                    'is',
                    [$sourceId, $doseDate]
                );
                $finalStatus = ((int) ($final['c'] ?? 0) > 0) ? 'MISSED' : 'TAKEN';
                Database::execute("UPDATE medication_schedules SET status = ? WHERE id = ? AND patient_nic = ?", 'sis', [$finalStatus, $sourceId, $patientNic]);
            }
        }

        return true;
    }

    private static function resolveSlots(string $timesOfDay, string $frequencyLabel): array
    {
        $slots = [];
        $raw = strtolower(trim($timesOfDay));
        if ($raw !== '') {
            $slots = array_merge($slots, self::extractSlotsFromText($raw));
        }

        if (!empty($slots)) {
            return self::uniqueOrderedSlots($slots);
        }

        $label = strtolower(trim($frequencyLabel));
        if ($label === '') {
            return [];
        }

        $slots = array_merge($slots, self::extractSlotsFromText($label));
        if (empty($slots)) {
            $slots[] = 'morning';
        }

        return self::uniqueOrderedSlots($slots);
    }

    private static function normalizeSlot(string $token): ?string
    {
        $t = strtolower(trim($token));
        if ($t === 'morning')
            return 'morning';
        if ($t === 'day' || $t === 'daytime' || $t === 'afternoon' || $t === 'noon')
            return 'day';
        if ($t === 'night' || $t === 'evening')
            return 'night';
        return null;
    }

    private static function extractSlotsFromText(string $text): array
    {
        $slots = [];
        $text = strtolower(trim($text));
        if ($text === '') {
            return $slots;
        }

        // Normalize common separators like "&", "/", "and", "|" into commas.
        $normalized = preg_replace('/\s*(?:&|\/|\||\band\b)\s*/i', ',', $text) ?? $text;
        $tokens = preg_split('/\s*,\s*/', $normalized) ?: [];
        if (empty($tokens)) {
            $tokens = [$normalized];
        }

        foreach ($tokens as $token) {
            $token = strtolower(trim($token));
            if ($token === '') {
                continue;
            }

            $slot = self::normalizeSlot($token);
            if ($slot !== null) {
                $slots[] = $slot;
                continue;
            }

            // Fuzzy containment for phrases like "day time", "night dose", "day+night".
            if (preg_match('/\bmorning\b/', $token)) {
                $slots[] = 'morning';
            }
            if (preg_match('/\b(day|daytime|afternoon|noon)\b/', $token)) {
                $slots[] = 'day';
            }
            if (preg_match('/\b(night|evening)\b/', $token)) {
                $slots[] = 'night';
            }
        }

        return $slots;
    }

    private static function uniqueOrderedSlots(array $slots): array
    {
        $seen = [];
        $ordered = [];
        foreach (['morning', 'day', 'night'] as $allowed) {
            foreach ($slots as $slot) {
                $slot = strtolower(trim((string) $slot));
                if ($slot !== $allowed || isset($seen[$slot])) {
                    continue;
                }
                $seen[$slot] = true;
                $ordered[] = $slot;
            }
        }
        return $ordered;
    }

    private static function slotTime(string $slot): string
    {
        if ($slot === 'night')
            return '18:00';
        if ($slot === 'day')
            return '13:00';
        return '08:00';
    }

    private static function defaultMessage(string $slot): string
    {
        return 'Time to take your ' . ucfirst($slot) . ' medication dose.';
    }

    private static function messageForSlot(string $message, string $slot): string
    {
        $prefix = ucfirst(self::slotLabel($slot)) . ' dose: ';
        $trimmed = trim($message);
        if (stripos($trimmed, $prefix) === 0) {
            return $trimmed;
        }
        return $prefix . $trimmed;
    }

    private static function composeReminderMessage(string $medicineName, string $dosage, string $mealTiming): string
    {
        $medicineName = trim($medicineName) !== '' ? trim($medicineName) : 'medication';
        $dosage = trim($dosage);
        $mealTiming = trim($mealTiming);
        $parts = ["Time to take $medicineName"];
        if ($dosage !== '') {
            $parts[] = "($dosage)";
        }
        if ($mealTiming !== '') {
            $parts[] = "- $mealTiming";
        }
        return implode(' ', $parts) . '.';
    }

    private static function slotLabel(string $slot): string
    {
        $s = strtolower(trim($slot));
        if ($s === 'day')
            return 'Day';
        if ($s === 'night')
            return 'Night';
        return 'Morning';
    }

    private static function normalizeDoseRow(array $row): array
    {
        $slot = strtolower(trim((string) ($row['frequency_slot'] ?? 'morning')));
        $row['frequency'] = self::slotLabel($slot);
        $row['status'] = strtoupper((string) ($row['status'] ?? 'PENDING'));
        return $row;
    }

    private static function tableExists(string $table): bool
    {
        return in_array($table, ['medication_reminder_events', 'medication_schedules', 'medication_schedule', 'schedule_master', 'medication_log', 'notifications', 'medicines', 'frequencies', 'meal_timing', 'dosage_categories'], true);
    }

    private static function columnExists(string $table, string $column): bool
    {
        $schema = [
            'medication_reminder_events' => ['pharmacy_id', 'delivered_notification_id', 'status', 'taken_at', 'updated_at'],
            'medication_schedules' => ['id', 'patient_nic', 'medicine_id', 'dosage', 'frequency', 'meal_timing', 'schedule_date', 'status', 'instructions', 'prescription_id', 'created_at', 'pharmacy_id'],
            'medication_schedule' => ['schedule_master_id', 'medicine_id', 'dosage_id', 'frequency_id', 'meal_timing_id', 'start_date', 'end_date', 'duration_days', 'instructions', 'pharmacy_id'],
            'schedule_master' => ['prescription_id', 'patient_nic', 'pharmacist_id', 'created_at', 'updated_at', 'pharmacy_id'],
            'medication_log' => ['medication_schedule_id', 'patient_nic', 'dose_date', 'status', 'updated_at', 'time_slot', 'pharmacy_id'],
            'notifications' => ['patient_nic', 'message', 'type', 'is_read', 'created_at', 'pharmacy_id'],
        ];

        return in_array($column, $schema[$table] ?? [], true);
    }
}

