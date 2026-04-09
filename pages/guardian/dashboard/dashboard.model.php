<?php
/**
 * Guardian Dashboard Model
 */
class DashboardModel
{
    public static function getPatientsByGuardian(string $guardianNic): array
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT nic, name, gender, chronic_issues 
            FROM patients 
            WHERE guardian_nic = '$guardianNic'
        ");
        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public static function getRecentAlertsByGuardian(string $guardianNic, int $limit = 5): array
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT n.id, n.message, n.type, n.created_at, n.is_read, p.name AS patient_name
            FROM notifications n
            JOIN patients p ON n.patient_nic = p.nic
            WHERE p.guardian_nic = '$guardianNic'
            ORDER BY n.created_at DESC
            LIMIT $limit
        ");
        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public static function getUnreadAlertsCount(string $guardianNic): int
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT COUNT(*) AS cnt 
            FROM notifications n
            JOIN patients p ON n.patient_nic = p.nic
            WHERE p.guardian_nic = '$guardianNic' AND n.is_read = 0
        ");
        return (int)$rs->fetch_assoc()['cnt'];
    }

    public static function getAverageAdherence(string $guardianNic): int
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT 
                COUNT(CASE WHEN s.status = 'TAKEN' THEN 1 END) AS taken_count,
                COUNT(*) AS total_count
            FROM medication_schedules s
            JOIN patients p ON s.patient_nic = p.nic
            WHERE p.guardian_nic = '$guardianNic'
        ");
        $row = $rs->fetch_assoc();
        if ($row['total_count'] == 0) return 100; // Default to 100 if no data
        return (int)(($row['taken_count'] / $row['total_count']) * 100);
    }
}
