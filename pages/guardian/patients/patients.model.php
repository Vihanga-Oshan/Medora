<?php
/**
 * Guardian Patient Monitoring Model
 */
class PatientsModel
{
    public static function getLinkedPatients(string $guardianNic): array
    {
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("SELECT * FROM patients WHERE guardian_nic = '$guardianNic'");
        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public static function getPatientProfile(string $nic): ?array
    {
        $nic = Database::$connection->real_escape_string($nic);
        $rs = Database::search("SELECT * FROM patients WHERE nic = '$nic' LIMIT 1");
        return $rs ? $rs->fetch_assoc() : null;
    }

    public static function getScheduleByDate(string $nic, string $date): array
    {
        $nic = Database::$connection->real_escape_string($nic);
        $date = Database::$connection->real_escape_string($date);
        $rs = Database::search("
            SELECT s.*, m.name AS medicine_name
            FROM medication_schedules s
            JOIN medicines m ON s.medicine_id = m.id
            WHERE s.patient_nic = '$nic' AND s.schedule_date = '$date'
        ");
        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public static function linkPatient(string $patientNic, string $guardianNic): bool
    {
        $patientNic = Database::$connection->real_escape_string($patientNic);
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        
        // In this system, we simply update the guardian_nic for the patient.
        // In a real system, there should be a verification step or a specialized many-to-many table.
        return Database::iud("UPDATE patients SET guardian_nic = '$guardianNic' WHERE nic = '$patientNic'");
    }

    public static function unlinkPatient(string $patientNic): bool
    {
        $patientNic = Database::$connection->real_escape_string($patientNic);
        return Database::iud("UPDATE patients SET guardian_nic = NULL WHERE nic = '$patientNic'");
    }
}
