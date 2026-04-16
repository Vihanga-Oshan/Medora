<?php

class PharmacistViewScheduleModel
{
    private const NIC_COLLATION = 'utf8mb4_unicode_ci';

    private static function currentPharmacyId(): int
    {
        if (isset($GLOBALS['currentPharmacyId']) && (int) $GLOBALS['currentPharmacyId'] > 0) {
            return (int) $GLOBALS['currentPharmacyId'];
        }

        $auth = Auth::getUser();
        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            return $fromToken;
        }
        return PharmacyContext::resolvePharmacistPharmacyId((int) ($auth['id'] ?? 0));
    }

    private static function pharmacyCondition(string $alias, string $table): string
    {
        $pid = self::currentPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId($table)) {
            return '1=1';
        }
        if (in_array($table, ['schedule_master', 'medication_schedule', 'medication_log'], true)) {
            return "($alias.pharmacy_id IS NULL OR $alias.pharmacy_id = 0 OR $alias.pharmacy_id = " . (int) $pid . ")";
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    private static function patientBelongsToCurrentPharmacy(string $nic): bool
    {
        $pid = self::currentPharmacyId();
        if ($pid <= 0 || $nic === '') {
            return false;
        }

        $row = Database::fetchOne("
            SELECT 1 AS allowed
            FROM patient p
            WHERE p.nic = ?
              AND (
                    EXISTS (
                        SELECT 1
                        FROM patient_pharmacy_selection pps
                        WHERE pps.patient_nic COLLATE " . self::NIC_COLLATION . " = p.nic COLLATE " . self::NIC_COLLATION . "
                          AND pps.is_active = 1
                          AND pps.pharmacy_id = ?
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM prescriptions pr
                        WHERE pr.patient_nic COLLATE " . self::NIC_COLLATION . " = p.nic COLLATE " . self::NIC_COLLATION . "
                          AND pr.pharmacy_id = ?
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM schedule_master sm
                        WHERE sm.patient_nic COLLATE " . self::NIC_COLLATION . " = p.nic COLLATE " . self::NIC_COLLATION . "
                          AND sm.pharmacy_id = ?
                    )
                )
            LIMIT 1
        ", 'siii', [$nic, $pid, $pid, $pid]);

        return $row !== null;
    }

    public static function getPatient(string $nic): ?array
    {
        $nic = trim($nic);
        if ($nic === '' || !self::patientBelongsToCurrentPharmacy($nic)) {
            return null;
        }

        return Database::fetchOne("
            SELECT p.nic, p.name, p.email, p.emergency_contact
            FROM patient p
            WHERE p.nic = ?
            LIMIT 1
        ", 's', [$nic]);
    }

    public static function getSchedulesByDate(string $nic, string $date): array
    {
        return Database::fetchAll("
            SELECT
                ms.id,
                COALESCE(NULLIF(TRIM(m.med_name), ''), NULLIF(TRIM(m.name), ''), 'Medication') AS medicine_name,
                COALESCE(dc.label, '-') AS dosage,
                COALESCE(f.label, '-') AS frequency,
                COALESCE(mt.label, '-') AS meal_timing,
                COALESCE(ms.instructions, '') AS instructions,
                ms.start_date,
                ms.end_date,
                ms.duration_days,
                COALESCE(UPPER(ml.status), 'PENDING') AS status
            FROM medication_schedule ms
            JOIN schedule_master sm ON sm.id = ms.schedule_master_id
            LEFT JOIN medicines m ON ms.medicine_id = m.id
            LEFT JOIN dosage_categories dc ON ms.dosage_id = dc.id
            LEFT JOIN frequencies f ON ms.frequency_id = f.id
            LEFT JOIN meal_timing mt ON ms.meal_timing_id = mt.id
            LEFT JOIN medication_log ml
              ON ml.medication_schedule_id = ms.id
             AND ml.patient_nic = sm.patient_nic
             AND ml.dose_date = ?
            WHERE sm.patient_nic = ?
              AND ? BETWEEN ms.start_date
                          AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
              AND " . self::pharmacyCondition('sm', 'schedule_master') . "
              AND " . self::pharmacyCondition('ms', 'medication_schedule') . "
              AND " . self::pharmacyCondition('ml', 'medication_log') . "
            ORDER BY ms.start_date ASC, ms.id ASC
        ", 'sss', [$date, $nic, $date]);
    }
}
