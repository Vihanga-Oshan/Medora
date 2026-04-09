<?php
/**
 * Patient Dashboard Model
 * All DB queries for the dashboard page.
 * Returns plain arrays — no HTML, no session logic.
 */
class DashboardModel
{
    private static function currentPharmacyId(): int
    {
        return PharmacyContext::selectedPharmacyId();
    }

    private static function pharmacyCondition(string $alias, string $table): string
    {
        $pid = self::currentPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId($table)) {
            return '1=1';
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::escape($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM $safeTable LIKE '$safeCol'");
        return $rs && $rs->num_rows > 0;
    }

    private static function resolvePatientTable(): ?string
    {
        if (self::tableExists('patients')) return 'patients';
        if (self::tableExists('patient')) return 'patient';
        return null;
    }

    private static function resolveGuardianTable(): ?string
    {
        if (self::tableExists('guardians')) return 'guardians';
        if (self::tableExists('guardian')) return 'guardian';
        return null;
    }

    /**
     * Get all medication schedules for a patient on a given date.
     */
    public static function getMedicationsByDate(string $nic, string $date): array
    {
        $nic  = Database::escape($nic);
        $date = Database::escape($date);
        $rows = [];

        if (self::tableExists('medication_schedules')) {
            $rs = Database::search("
                SELECT ms.id, m.name AS medicine_name, ms.dosage, ms.frequency,
                       ms.meal_timing, ms.instructions, ms.status
                FROM medication_schedules ms
                JOIN medicines m ON ms.medicine_id = m.id
                WHERE ms.patient_nic = '$nic'
                  AND ms.schedule_date = '$date'
                  AND " . self::pharmacyCondition('ms', 'medication_schedules') . "
                  AND " . self::pharmacyCondition('m', 'medicines') . "
                ORDER BY FIELD(ms.frequency,'MORNING','AFTERNOON','EVENING','NIGHT')
            ");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) $rows[] = $row;
            }
            if (!empty($rows)) {
                return $rows;
            }
        }

        if (self::tableExists('medication_schedule') && self::tableExists('schedule_master')) {
            $rs = Database::search("
                SELECT
                    ms.id,
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    COALESCE(dc.label, '-') AS dosage,
                    COALESCE(f.label, '-') AS frequency,
                    COALESCE(mt.label, '-') AS meal_timing,
                    COALESCE(ms.instructions, '') AS instructions,
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
                    AND ml.dose_date = '$date'
                WHERE sm.patient_nic = '$nic'
                  AND '$date' BETWEEN ms.start_date
                                  AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
                  AND " . self::pharmacyCondition('sm', 'schedule_master') . "
                  AND " . self::pharmacyCondition('ms', 'medication_schedule') . "
                  AND " . self::pharmacyCondition('m', 'medicines') . "
                ORDER BY ms.id ASC
            ");
            if (!$rs) {
                return [];
            }

            while ($row = $rs->fetch_assoc()) $rows[] = $row;
            return $rows;
        } else {
            return [];
        }
    }

    /**
     * Get the 3 most recent notifications for a patient.
     */
    public static function getRecentNotifications(string $nic): array
    {
        $nic = Database::escape($nic);
        $rs = Database::search("
            SELECT id, message, is_read, created_at
            FROM notifications
            WHERE patient_nic = '$nic'
              AND " . self::pharmacyCondition('notifications', 'notifications') . "
            ORDER BY created_at DESC
            LIMIT 3
        ");

        if (!$rs) {
            return [];
        }

        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    /**
     * Get pending guardian link request details, if any.
     */
    public static function getPendingGuardianRequest(string $nic): ?array
    {
        $nic = Database::escape($nic);
        $patientTable = self::resolvePatientTable();
        $guardianTable = self::resolveGuardianTable();
        if ($patientTable === null || $guardianTable === null) {
            return null;
        }

        if (!self::columnExists($patientTable, 'guardian_nic') || !self::columnExists($patientTable, 'link_status')) {
            return null;
        }

        $guardianNameCol = self::columnExists($guardianTable, 'name') ? 'name' : 'g_name';

        $rs = Database::search("
            SELECT g.nic, g.$guardianNameCol AS name, g.email
            FROM $patientTable p
            JOIN $guardianTable g ON p.guardian_nic = g.nic
            WHERE p.nic = '$nic' AND p.link_status = 'REQUEST_SENT'
            LIMIT 1
        ");

        if (!$rs) {
            return null;
        }

        return $rs ? $rs->fetch_assoc() : null;
    }
}
