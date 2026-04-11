<?php
/**
 * Guardian Alerts Model
 */
class AlertsModel
{
    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function resolvePatientTable(): ?string
    {
        if (self::tableExists('patients')) return 'patients';
        if (self::tableExists('patient')) return 'patient';
        return null;
    }

    public static function getNotificationsByGuardian(string $guardianNic): array
    {
        Database::setUpConnection();
        $patientTable = self::resolvePatientTable();
        if (!self::tableExists('notifications') || $patientTable === null) {
            return [];
        }

        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT n.*, p.name AS patient_name, '' AS patient_phone
            FROM notifications n
            JOIN `$patientTable` p ON n.patient_nic = p.nic
            WHERE p.guardian_nic = '$guardianNic'
            ORDER BY n.created_at DESC
        ");
        if (!($rs instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public static function markAsRead(int $id): bool
    {
        if (!self::tableExists('notifications')) {
            return false;
        }
        return Database::iud("UPDATE notifications SET is_read = 1 WHERE id = $id");
    }

    public static function markAllRead(string $guardianNic): bool
    {
        Database::setUpConnection();
        $patientTable = self::resolvePatientTable();
        if (!self::tableExists('notifications') || $patientTable === null) {
            return false;
        }

        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        return Database::iud("
            UPDATE notifications n
            JOIN `$patientTable` p ON n.patient_nic = p.nic
            SET n.is_read = 1
            WHERE p.guardian_nic = '$guardianNic' AND n.is_read = 0
        ");
    }
}
