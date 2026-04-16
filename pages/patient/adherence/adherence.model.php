<?php
/**
 * Adherence Model
 * Ported from: AdherenceHistoryServlet.java + ScheduleDAO.java
 */
class AdherenceModel
{
    private static function slotLabel(string $slot): string
    {
        $slot = strtolower(trim($slot));
        if ($slot === 'day') {
            return 'Day';
        }
        if ($slot === 'night') {
            return 'Night';
        }
        if ($slot === 'morning') {
            return 'Morning';
        }
        if ($slot === '') {
            return 'General';
        }
        return ucwords(str_replace(['_', '-'], ' ', $slot));
    }

    private static function pharmacyCondition(string $alias, string $table): string
    {
        $pid = PharmacyContext::selectedPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId($table)) {
            return '1=1';
        }
        if (in_array($table, ['medication_log'], true)) {
            return "($alias.pharmacy_id IS NULL OR $alias.pharmacy_id = 0 OR $alias.pharmacy_id = " . (int) $pid . ")";
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    public static function getOverallAdherence(string $nic): int
    {
        $row = Database::fetchOne("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'TAKEN' THEN 1 ELSE 0 END) AS taken
            FROM medication_log
            WHERE patient_nic = ?
              AND status IN ('TAKEN', 'MISSED')
              AND " . self::pharmacyCondition('medication_log', 'medication_log') . "
        ", 's', [$nic]);
        if ($row) {
            $total = (int) ($row['total'] ?? 0);
            $taken = (int) ($row['taken'] ?? 0);
            return $total > 0 ? (int) (($taken / $total) * 100) : 0;
        }
        return 0;
    }

    public static function getWeeklyAdherence(string $nic): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D, M j', strtotime($date));
            $row = Database::fetchOne("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'TAKEN' THEN 1 ELSE 0 END) AS taken
                FROM medication_log
                WHERE patient_nic = ?
                  AND dose_date = ?
                  AND status IN ('TAKEN', 'MISSED')
                  AND " . self::pharmacyCondition('medication_log', 'medication_log') . "
            ", 'ss', [$nic, $date]);

            if (!$row) {
                $days[] = ['day' => $dayName, 'percentage' => 0];
                continue;
            }

            $total = (int) ($row['total'] ?? 0);
            $taken = (int) ($row['taken'] ?? 0);
            $percentage = $total > 0 ? (int) (($taken / $total) * 100) : 0;
            $days[] = ['day' => $dayName, 'percentage' => $percentage];
        }
        return $days;
    }

    public static function getHistory(string $nic, int $limit = 50): array
    {
        $limit = max(1, $limit);
        $rows = Database::fetchAll("
            SELECT
                ml.dose_date AS date,
                COALESCE(NULLIF(TRIM(m.med_name), ''), NULLIF(TRIM(m.name), ''), 'Medication') AS medicine,
                COALESCE(ml.time_slot, 'General') AS time_slot,
                UPPER(ml.status) AS status
            FROM medication_log ml
            JOIN medication_schedule ms ON ml.medication_schedule_id = ms.id
            LEFT JOIN medicines m ON ms.medicine_id = m.id
            WHERE ml.patient_nic = ?
              AND ml.status IN ('TAKEN','MISSED')
              AND " . self::pharmacyCondition('ml', 'medication_log') . "
            ORDER BY ml.dose_date DESC, ml.id DESC
            LIMIT $limit
        ", 's', [$nic]);
        if (empty($rows)) {
            return [];
        }

        $history = [];
        foreach ($rows as $row) {
            $rawDate = (string) ($row['date'] ?? '');
            $history[] = [
                'date' => $rawDate,
                'displayDate' => $rawDate !== '' ? date('M d, Y', strtotime($rawDate)) : '',
                'medicine' => (string) ($row['medicine'] ?? 'Medication'),
                'timeSlot' => self::slotLabel((string) ($row['time_slot'] ?? 'General')),
                'status' => strtoupper((string) ($row['status'] ?? 'PENDING')),
            ];
        }
        return $history;
    }
}
