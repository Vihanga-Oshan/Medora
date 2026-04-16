<?php
/**
 * Patient Dashboard Model
 * All DB queries for the dashboard page.
 * Returns plain arrays — no HTML, no session logic.
 */
require_once ROOT . '/core/GuardianLinkRequestSupport.php';

class DashboardModel
{
    private const PATIENT_TABLE = 'patient';
    private const GUARDIAN_TABLE = 'guardian';

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
        if ($table === 'medicines') {
            return '1=1';
        }
        if (in_array($table, ['schedule_master', 'medication_schedule', 'medication_log', 'notifications'], true)) {
            return "($alias.pharmacy_id IS NULL OR $alias.pharmacy_id = 0 OR $alias.pharmacy_id = " . (int) $pid . ")";
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    /**
     * Get all medication schedules for a patient on a given date.
     */
    public static function getMedicationsByDate(string $nic, string $date): array
    {
        $eventRows = MedicationReminderService::getDoseRowsByDate($nic, $date);
        if (!empty($eventRows)) {
            return $eventRows;
        }

        $rows = Database::fetchAll("
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
                AND ml.dose_date = ?
            WHERE sm.patient_nic = ?
              AND ? BETWEEN ms.start_date
                              AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
              AND " . self::pharmacyCondition('sm', 'schedule_master') . "
              AND " . self::pharmacyCondition('ms', 'medication_schedule') . "
              AND " . self::pharmacyCondition('m', 'medicines') . "
            ORDER BY ms.id ASC
        ", 'sss', [$date, $nic, $date]);
        return $rows;
    }

    /**
     * Get the 3 most recent notifications for a patient.
     */
    public static function getRecentNotifications(string $nic): array
    {
        MedicationReminderService::deliverDueReminders($nic);

        return Database::fetchAll("
            SELECT id, message, is_read, created_at
            FROM notifications
            WHERE patient_nic = ?
              AND " . self::pharmacyCondition('notifications', 'notifications') . "
            ORDER BY created_at DESC
            LIMIT 3
        ", 's', [$nic]);
    }

    /**
     * Get pending guardian link request details, if any.
     */
    public static function getPendingGuardianRequest(string $nic): ?array
    {
        $pending = GuardianLinkRequestSupport::latestPendingForPatient($nic);
        $guardianNic = (string) ($pending['guardian_nic'] ?? '');
        if ($guardianNic === '') {
            return null;
        }

        return Database::fetchOne(
            "SELECT g.nic, g.g_name AS name, g.email
             FROM " . self::GUARDIAN_TABLE . " g
             WHERE g.nic = ?
             LIMIT 1",
            's',
            [$guardianNic]
        );
    }
}
