<?php

/**
 * MedicationReminderService
 * Creates dose-level reminder events and delivers due APP notifications.
 */
class MedicationReminderService
{
    private const TABLE = 'medication_reminder_events';

    public static function createEventsForSchedule(array $payload): void
    {
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

        if (!empty($slots)) {
            $slotPlaceholders = implode(', ', array_fill(0, count($slots), '?'));
            Database::execute(
                "DELETE FROM " . self::TABLE . "
                 WHERE patient_nic = ?
                   AND source_type = ?
                   AND source_schedule_id = ?
                   AND dose_date = ?
                   AND status = 'PENDING'
                   AND delivered_at IS NULL
                   AND time_slot NOT IN ($slotPlaceholders)",
                'ssis' . str_repeat('s', count($slots)),
                array_merge([$patientNic, $sourceType, $sourceId, $doseDate], array_values($slots))
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
        self::markPastDueEventsMissed($patientNic);

        self::backfillEventsForPatientDate($patientNic, $date);

        $pid = PharmacyContext::selectedPharmacyId();
        $eventFilter = "e.patient_nic = ? AND e.dose_date = ?";
        $types = 'ss';
        $params = [$patientNic, $date];
        if ($pid > 0) {
            $eventFilter .= " AND (e.pharmacy_id IS NULL OR e.pharmacy_id = ?)";
            $types .= 'i';
            $params[] = $pid;
        }

        $rows = [];
        $sql = "
            SELECT
                e.id AS reminder_event_id,
                ms.id AS id,
                COALESCE(NULLIF(TRIM(m.med_name), ''), NULLIF(TRIM(m.name), ''), 'Medication') AS medicine_name,
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
              AND sm.patient_nic = ?
        ";
        $types .= 's';
        $params[] = $patientNic;
        if ($pid > 0) {
            if (PharmacyContext::tableHasPharmacyId('schedule_master')) {
                $sql .= " AND (sm.pharmacy_id IS NULL OR sm.pharmacy_id = 0 OR sm.pharmacy_id = " . (int) $pid . ")";
            }
            if (PharmacyContext::tableHasPharmacyId('medication_schedule')) {
                $sql .= " AND (ms.pharmacy_id IS NULL OR ms.pharmacy_id = 0 OR ms.pharmacy_id = " . (int) $pid . ")";
            }
        }
        foreach (Database::fetchAll($sql, $types, $params) as $r) {
            $rows[] = self::normalizeDoseRow($r);
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
        self::markPastDueEventsMissed($patientNic);
        self::backfillEventsForPatientDate($patientNic, date('Y-m-d'));

        $patientNic = trim($patientNic);
        if ($patientNic === '') {
            return;
        }

        $where = "e.patient_nic = ? AND e.status = 'PENDING' AND e.delivered_at IS NULL AND e.scheduled_at <= NOW()";
        $types = 's';
        $params = [$patientNic];

        $pid = PharmacyContext::selectedPharmacyId();
        if ($pid > 0) {
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
        $patientNic = trim($patientNic);
        $date = trim($date);
        if ($patientNic === '' || $date === '') {
            return;
        }

        $pid = PharmacyContext::selectedPharmacyId();
        $types = 'sss';
        $params = [$date, $patientNic, $date];

        $sql = "
            SELECT
                ms.id AS schedule_id,
                sm.patient_nic,
                ? AS dose_date,
                COALESCE(f.times_of_day, '') AS times_of_day,
                COALESCE(f.label, '') AS frequency_label,
                COALESCE(mt.label, '') AS meal_timing,
                COALESCE(dc.label, '') AS dosage,
                COALESCE(ms.instructions, '') AS instructions,
                COALESCE(NULLIF(TRIM(m.med_name), ''), NULLIF(TRIM(m.name), ''), 'Medication') AS medicine_name,
                COALESCE(sm.pharmacy_id, 0) AS pharmacy_id
            FROM medication_schedule ms
            JOIN schedule_master sm ON sm.id = ms.schedule_master_id
            LEFT JOIN frequencies f ON f.id = ms.frequency_id
            LEFT JOIN meal_timing mt ON mt.id = ms.meal_timing_id
            LEFT JOIN dosage_categories dc ON dc.id = ms.dosage_id
            LEFT JOIN medicines m ON m.id = ms.medicine_id
            WHERE sm.patient_nic = ?
              AND ? BETWEEN ms.start_date
                              AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
        ";
        if ($pid > 0 && PharmacyContext::tableHasPharmacyId('schedule_master')) {
            $sql .= " AND (sm.pharmacy_id IS NULL OR sm.pharmacy_id = 0 OR sm.pharmacy_id = " . (int) $pid . ")";
        }
        if ($pid > 0 && PharmacyContext::tableHasPharmacyId('medication_schedule')) {
            $sql .= " AND (ms.pharmacy_id IS NULL OR ms.pharmacy_id = 0 OR ms.pharmacy_id = " . (int) $pid . ")";
        }

        foreach (Database::fetchAll($sql, $types, $params) as $row) {
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

    public static function markTakenFromEvent(int $eventId, string $patientNic): bool
    {
        return self::markEventStatus($eventId, $patientNic, 'TAKEN');
    }

    public static function markMissedFromEvent(int $eventId, string $patientNic): bool
    {
        return self::markEventStatus($eventId, $patientNic, 'MISSED');
    }

    private static function markPastDueEventsMissed(string $patientNic): void
    {
        $patientNic = trim($patientNic);
        if ($patientNic === '') {
            return;
        }

        $where = "patient_nic = ? AND status = 'PENDING' AND dose_date < CURDATE()";
        $types = 's';
        $params = [$patientNic];

        $pid = PharmacyContext::selectedPharmacyId();
        if ($pid > 0) {
            $where .= " AND (pharmacy_id IS NULL OR pharmacy_id = 0 OR pharmacy_id = ?)";
            $types .= 'i';
            $params[] = $pid;
        }

        $rows = Database::fetchAll("
            SELECT id
            FROM " . self::TABLE . "
            WHERE $where
            ORDER BY dose_date ASC, scheduled_at ASC, id ASC
            LIMIT 200
        ", $types, $params);

        foreach ($rows as $row) {
            $eventId = (int) ($row['id'] ?? 0);
            if ($eventId > 0) {
                self::markEventStatus($eventId, $patientNic, 'MISSED');
            }
        }
    }

    private static function markEventStatus(int $eventId, string $patientNic, string $targetStatus): bool
    {
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

        if ($targetStatus === 'TAKEN') {
            $ok = Database::execute(
                "UPDATE " . self::TABLE . " SET status = ?, taken_at = NOW(), updated_at = NOW() WHERE id = ? AND patient_nic = ?",
                'sis',
                [$targetStatus, $eventId, $patientNic]
            );
        } else {
            $ok = Database::execute(
                "UPDATE " . self::TABLE . " SET status = ?, taken_at = NULL, updated_at = NOW() WHERE id = ? AND patient_nic = ?",
                'sis',
                [$targetStatus, $eventId, $patientNic]
            );
        }
        if (!$ok) {
            return false;
        }

        if ($event['delivered_notification_id'] ?? null) {
            $notifId = (int) $event['delivered_notification_id'];
            if ($notifId > 0) {
                Database::execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND patient_nic = ?", 'is', [$notifId, $patientNic]);
            }
        }

        $sourceType = (string) ($event['source_type'] ?? '');
        $sourceId = (int) ($event['source_schedule_id'] ?? 0);
        $doseDate = (string) ($event['dose_date'] ?? date('Y-m-d'));
        $slot = (string) ($event['time_slot'] ?? 'general');
        $pharmacyId = (int) ($event['pharmacy_id'] ?? 0);

        if ($sourceType === 'legacy' && $sourceId > 0) {
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
                        'issss',
                        [$sourceId, $patientNic, $doseDate, $targetStatus, $slot]
                    );
                }
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
            if (empty($slots)) {
                $slots = array_merge($slots, self::slotsFromClockTimes($raw));
            }
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
            $slots = array_merge($slots, self::slotsFromFrequencyLabel($label));
        }
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

    private static function slotsFromClockTimes(string $text): array
    {
        preg_match_all('/\b(?:[01]?\d|2[0-3]):[0-5]\d\b/', strtolower($text), $matches);
        $times = $matches[0] ?? [];
        $count = count($times);

        if ($count <= 0) {
            return [];
        }
        if ($count === 1) {
            return ['morning'];
        }
        if ($count === 2) {
            return ['morning', 'night'];
        }
        return ['morning', 'day', 'night'];
    }

    private static function slotsFromFrequencyLabel(string $label): array
    {
        $label = strtolower(trim($label));
        if ($label === '') {
            return [];
        }

        if (preg_match('/\b(once|one time|daily|every day)\b/', $label)) {
            return ['morning'];
        }
        if (preg_match('/\b(twice|two times|2 times)\b/', $label)) {
            return ['morning', 'night'];
        }
        if (preg_match('/\b(three times|thrice|3 times|every 8 hours)\b/', $label)) {
            return ['morning', 'day', 'night'];
        }
        if (preg_match('/\b(every 6 hours|four times|4 times)\b/', $label)) {
            return ['morning', 'day', 'night'];
        }

        return [];
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
        if ($slot === 'night') {
            return '19:00';
        }
        if ($slot === 'day') {
            return '13:00';
        }
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

}

