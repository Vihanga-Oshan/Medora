<?php
/**
 * Guardian Patient Monitoring Model
 */
class PatientsModel
{
    private const PATIENT_TABLE = 'patient';
    private const REQUEST_TABLE = 'guardian_link_requests';

    private static function normalizeNic(string $nic): string
    {
        $nic = strtoupper(trim($nic));
        return preg_replace('/[\s\-]+/', '', $nic) ?? $nic;
    }

    private static function patientGuardianMatchExpr(string $patientAlias = 'p'): string
    {
        return "REPLACE(REPLACE(UPPER(" . $patientAlias . ".guardian_nic), ' ', ''), '-', '') = ?";
    }

    public static function ensureLinkRequestTable(): void
    {
        Database::iud("
            CREATE TABLE IF NOT EXISTS `" . self::REQUEST_TABLE . "` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                guardian_nic VARCHAR(45) NOT NULL,
                patient_nic VARCHAR(20) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
                guardian_seen TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                responded_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_guardian_status (guardian_nic, status, responded_at),
                INDEX idx_patient_status (patient_nic, status, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public static function getLinkedPatients(string $guardianNic): array
    {
        $guardianNic = self::normalizeNic($guardianNic);
        return Database::fetchAll(
            "SELECT * FROM `" . self::PATIENT_TABLE . "` p
             WHERE " . self::patientGuardianMatchExpr('p') . "
             ORDER BY p.name ASC",
            's',
            [$guardianNic]
        );
    }

    public static function getPatientProfile(string $nic): ?array
    {
        return Database::fetchOne("SELECT * FROM `" . self::PATIENT_TABLE . "` WHERE nic = ? LIMIT 1", 's', [$nic]);
    }

    public static function getScheduleByDate(string $nic, string $date): array
    {
        return Database::fetchAll("
            SELECT
                ms.id,
                COALESCE(m.name, 'Medication') AS medicine_name,
                COALESCE(dc.label, '-') AS dosage,
                COALESCE(f.label, '-') AS frequency,
                COALESCE(mt.label, '') AS meal_timing,
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
            ORDER BY ms.id ASC
        ", 'sss', [$date, $nic, $date]);
    }

    public static function linkPatient(string $patientNic, string $guardianNic): bool
    {
        return Database::execute(
            "UPDATE `" . self::PATIENT_TABLE . "` SET guardian_nic = ? WHERE nic = ?",
            'ss',
            [self::normalizeNic($guardianNic), self::normalizeNic($patientNic)]
        );
    }

    public static function unlinkPatient(string $patientNic): bool
    {
        return Database::execute(
            "UPDATE `" . self::PATIENT_TABLE . "` SET guardian_nic = NULL WHERE nic = ?",
            's',
            [self::normalizeNic($patientNic)]
        );
    }

    public static function sendLinkRequest(string $patientNic, string $guardianNic): bool
    {
        self::ensureLinkRequestTable();
        $patientNic = self::normalizeNic($patientNic);
        $guardianNic = self::normalizeNic($guardianNic);

        Database::execute(
            "UPDATE `" . self::REQUEST_TABLE . "`
             SET status = 'CANCELLED', guardian_seen = 1, updated_at = NOW()
             WHERE REPLACE(REPLACE(UPPER(patient_nic), ' ', ''), '-', '') = ?
               AND REPLACE(REPLACE(UPPER(guardian_nic), ' ', ''), '-', '') = ?
               AND UPPER(status) = 'PENDING'",
            'ss',
            [$patientNic, $guardianNic]
        );

        return Database::execute(
            "INSERT INTO `" . self::REQUEST_TABLE . "` (guardian_nic, patient_nic, status, guardian_seen, created_at, updated_at)
             VALUES (?, ?, 'PENDING', 0, NOW(), NOW())",
            'ss',
            [$guardianNic, $patientNic]
        );
    }
}
