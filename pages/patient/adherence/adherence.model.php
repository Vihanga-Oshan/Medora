<?php
/**
 * Adherence Model
 * Ported from: AdherenceHistoryServlet.java + ScheduleDAO.java
 */
class AdherenceModel
{
    private static function pharmacyCondition(string $alias, string $table): string
    {
        $pid = PharmacyContext::selectedPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId($table)) {
            return '1=1';
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    private static function tableExists(string $table): bool
    {
        return in_array($table, ['medication_log', 'medication_schedules', 'medication_schedule', 'schedule_master', 'medication_reminder_events', 'patient_pharmacy_selection'], true);
    }

    public static function getOverallAdherence(string $nic): int
    {
        $nic = Database::escape($nic);

        // Java-compatible source of truth.
        if (self::tableExists('medication_log')) {
            $rs = Database::search("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'TAKEN' THEN 1 ELSE 0 END) AS taken
                FROM medication_log
                WHERE patient_nic = '$nic'
                  AND status IN ('TAKEN', 'MISSED')
                  AND " . self::pharmacyCondition('medication_log', 'medication_log') . "
            ");
            if ($rs instanceof mysqli_result) {
                $row = $rs->fetch_assoc();
                $total = (int) ($row['total'] ?? 0);
                $taken = (int) ($row['taken'] ?? 0);
                return $total > 0 ? (int) (($taken / $total) * 100) : 0;
            }
        }

        if (!self::tableExists('medication_schedules')) {
            return 0;
        }

        $rs = Database::search("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'TAKEN' THEN 1 ELSE 0 END) AS taken
            FROM medication_schedules
            WHERE patient_nic = '$nic'
              AND " . self::pharmacyCondition('medication_schedules', 'medication_schedules') . "
        ");
        if (!($rs instanceof mysqli_result)) {
            return 0;
        }

        $row = $rs->fetch_assoc();
        $total = (int) ($row['total'] ?? 0);
        $taken = (int) ($row['taken'] ?? 0);
        return $total > 0 ? (int) (($taken / $total) * 100) : 0;
    }

    public static function getWeeklyAdherence(string $nic): array
    {
        $nic = Database::escape($nic);
        $days = [];
        $useLog = self::tableExists('medication_log');
        $useExpanded = self::tableExists('medication_schedules');

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D, M j', strtotime($date));
            if ($useLog) {
                $rs = Database::search("
                    SELECT
                        COUNT(*) AS total,
                        SUM(CASE WHEN status = 'TAKEN' THEN 1 ELSE 0 END) AS taken
                    FROM medication_log
                    WHERE patient_nic = '$nic'
                      AND dose_date = '$date'
                      AND status IN ('TAKEN', 'MISSED')
                      AND " . self::pharmacyCondition('medication_log', 'medication_log') . "
                ");
            } elseif ($useExpanded) {
                $rs = Database::search("
                    SELECT
                        COUNT(*) AS total,
                        SUM(CASE WHEN status = 'TAKEN' THEN 1 ELSE 0 END) AS taken
                    FROM medication_schedules
                    WHERE patient_nic = '$nic' AND schedule_date = '$date'
                      AND " . self::pharmacyCondition('medication_schedules', 'medication_schedules') . "
                ");
            } else {
                $rs = false;
            }

            if (!($rs instanceof mysqli_result)) {
                $days[] = ['day' => $dayName, 'percentage' => 0];
                continue;
            }

            $row = $rs->fetch_assoc();
            $total = (int) $row['total'];
            $taken = (int) $row['taken'];
            $percentage = $total > 0 ? (int) (($taken / $total) * 100) : 0;
            $days[] = ['day' => $dayName, 'percentage' => $percentage];
        }
        return $days;
    }

    public static function getHistory(string $nic, int $limit = 50): array
    {
        $nic = Database::escape($nic);

        if (self::tableExists('medication_log') && self::tableExists('medication_schedule')) {
            $rs = Database::search("
                SELECT
                    ml.dose_date AS date,
                    CONCAT(COALESCE(m.name, 'Medication'), ' (', COALESCE(ml.time_slot, 'General'), ')') AS medicine,
                    UPPER(ml.status) AS status
                FROM medication_log ml
                JOIN medication_schedule ms ON ml.medication_schedule_id = ms.id
                LEFT JOIN medicines m ON ms.medicine_id = m.id
                WHERE ml.patient_nic = '$nic'
                  AND ml.status IN ('TAKEN','MISSED')
                  AND " . self::pharmacyCondition('ml', 'medication_log') . "
                ORDER BY ml.dose_date DESC, ml.id DESC
                LIMIT $limit
            ");
            if ($rs instanceof mysqli_result) {
                $rows = [];
                while ($row = $rs->fetch_assoc())
                    $rows[] = $row;
                return $rows;
            }
        }

        if (!self::tableExists('medication_schedules')) {
            return [];
        }

        $rs = Database::search("
            SELECT ms.schedule_date AS date,
                   CONCAT(m.name, ' (', ms.frequency, ')') AS medicine,
                   ms.status
            FROM medication_schedules ms
            JOIN medicines m ON ms.medicine_id = m.id
            WHERE ms.patient_nic = '$nic'
              AND ms.status IN ('TAKEN','MISSED')
              AND " . self::pharmacyCondition('ms', 'medication_schedules') . "
            ORDER BY ms.schedule_date DESC, ms.id DESC
            LIMIT $limit
        ");
        if (!($rs instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $rs->fetch_assoc())
            $rows[] = $row;
        return $rows;
    }
}
