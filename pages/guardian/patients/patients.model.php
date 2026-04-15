<?php
/**
 * Guardian Patient Monitoring Model
 */
class PatientsModel
{
    private const PATIENT_TABLE = 'patient';

    public static function getLinkedPatients(string $guardianNic): array
    {
        return Database::fetchAll("SELECT * FROM `" . self::PATIENT_TABLE . "` WHERE guardian_nic = ?", 's', [$guardianNic]);
    }

    public static function getPatientProfile(string $nic): ?array
    {
        return Database::fetchOne("SELECT * FROM `" . self::PATIENT_TABLE . "` WHERE nic = ? LIMIT 1", 's', [$nic]);
    }

    public static function getScheduleByDate(string $nic, string $date): array
    {
        Database::setUpConnection();
        $nic = Database::$connection->real_escape_string($nic);
        $date = Database::$connection->real_escape_string($date);

        if (PharmacyContext::tableExists('medication_schedules')) {
            $rs = Database::search("
                SELECT
                    s.id,
                    m.name AS medicine_name,
                    COALESCE(s.dosage, '-') AS dosage,
                    COALESCE(s.frequency, '-') AS frequency,
                    COALESCE(s.meal_timing, '') AS meal_timing,
                    COALESCE(s.instructions, '') AS instructions,
                    COALESCE(UPPER(s.status), 'PENDING') AS status
                FROM medication_schedules s
                JOIN medicines m ON s.medicine_id = m.id
                WHERE s.patient_nic = '$nic' AND s.schedule_date = '$date'
            ");

            if ($rs instanceof mysqli_result) {
                $rows = [];
                while ($row = $rs->fetch_assoc())
                    $rows[] = $row;
                return $rows;
            }
        }

        if (PharmacyContext::tableExists('medication_schedule') && PharmacyContext::tableExists('schedule_master')) {
            $statusExpr = PharmacyContext::tableExists('medication_log')
                ? "COALESCE(UPPER(ml.status), 'PENDING')"
                : "'PENDING'";

            $joinLog = PharmacyContext::tableExists('medication_log')
                ? "LEFT JOIN medication_log ml
                    ON ml.medication_schedule_id = ms.id
                   AND ml.patient_nic = sm.patient_nic
                   AND ml.dose_date = '$date'"
                : '';

            $rs = Database::search("
                SELECT
                    ms.id,
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    COALESCE(dc.label, '-') AS dosage,
                    COALESCE(f.label, '-') AS frequency,
                    COALESCE(mt.label, '') AS meal_timing,
                    COALESCE(ms.instructions, '') AS instructions,
                    $statusExpr AS status
                FROM medication_schedule ms
                JOIN schedule_master sm ON sm.id = ms.schedule_master_id
                LEFT JOIN medicines m ON ms.medicine_id = m.id
                LEFT JOIN dosage_categories dc ON ms.dosage_id = dc.id
                LEFT JOIN frequencies f ON ms.frequency_id = f.id
                LEFT JOIN meal_timing mt ON ms.meal_timing_id = mt.id
                $joinLog
                WHERE sm.patient_nic = '$nic'
                  AND '$date' BETWEEN ms.start_date
                                  AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
                ORDER BY ms.id ASC
            ");

            if ($rs instanceof mysqli_result) {
                $rows = [];
                while ($row = $rs->fetch_assoc())
                    $rows[] = $row;
                return $rows;
            }
        }

        return [];
    }

    public static function linkPatient(string $patientNic, string $guardianNic): bool
    {
        Database::setUpConnection();
        $patientNic = Database::$connection->real_escape_string($patientNic);
        $guardianNic = Database::$connection->real_escape_string($guardianNic);

        $sql = "UPDATE `" . self::PATIENT_TABLE . "` SET guardian_nic = '$guardianNic', link_status = 'LINKED' WHERE nic = '$patientNic'";
        return Database::iud($sql);
    }

    public static function unlinkPatient(string $patientNic): bool
    {
        Database::setUpConnection();
        $patientNic = Database::$connection->real_escape_string($patientNic);
        $sql = "UPDATE `" . self::PATIENT_TABLE . "` SET guardian_nic = NULL, link_status = NULL WHERE nic = '$patientNic'";
        return Database::iud($sql);
    }
}
