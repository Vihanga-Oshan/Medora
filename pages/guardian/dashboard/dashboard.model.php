<?php
/**
 * Guardian Dashboard Model
 */
class DashboardModel
{
    private const PATIENT_TABLE = 'patient';

    public static function getPatientsByGuardian(string $guardianNic): array
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT nic, name, gender, chronic_issues
            FROM `" . self::PATIENT_TABLE . "`
            WHERE guardian_nic = '$guardianNic'
        ");
        if (!($rs instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public static function getRecentAlertsByGuardian(string $guardianNic, int $limit = 5): array
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $limit = max(1, (int)$limit);
        $rs = Database::search("
            SELECT n.id, n.message, n.type, n.created_at, n.is_read, p.name AS patient_name
            FROM notifications n
            JOIN `" . self::PATIENT_TABLE . "` p ON n.patient_nic = p.nic
            WHERE p.guardian_nic = '$guardianNic'
            ORDER BY n.created_at DESC
            LIMIT $limit
        ");
        if (!($rs instanceof mysqli_result)) {
            return [];
        }

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
            JOIN `" . self::PATIENT_TABLE . "` p ON n.patient_nic = p.nic
            WHERE p.guardian_nic = '$guardianNic' AND n.is_read = 0
        ");
        if (!($rs instanceof mysqli_result)) {
            return 0;
        }

        $row = $rs->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public static function getAverageAdherence(string $guardianNic): int
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);

        if (PharmacyContext::tableExists('medication_schedules')) {
            $rs = Database::search("
                SELECT 
                    COUNT(CASE WHEN s.status = 'TAKEN' THEN 1 END) AS taken_count,
                    COUNT(*) AS total_count
                FROM medication_schedules s
                JOIN `" . self::PATIENT_TABLE . "` p ON s.patient_nic = p.nic
                WHERE p.guardian_nic = '$guardianNic'
            ");

            if ($rs instanceof mysqli_result) {
                $row = $rs->fetch_assoc();
                $total = (int)($row['total_count'] ?? 0);
                $taken = (int)($row['taken_count'] ?? 0);
                if ($total === 0) return 100;
                return (int)(($taken / $total) * 100);
            }
        }

        if (PharmacyContext::tableExists('medication_log')) {
            $rs = Database::search("
                SELECT
                    COUNT(CASE WHEN ml.status = 'TAKEN' THEN 1 END) AS taken_count,
                    COUNT(*) AS total_count
                FROM medication_log ml
                JOIN `" . self::PATIENT_TABLE . "` p ON ml.patient_nic = p.nic
                WHERE p.guardian_nic = '$guardianNic'
            ");

            if ($rs instanceof mysqli_result) {
                $row = $rs->fetch_assoc();
                $total = (int)($row['total_count'] ?? 0);
                $taken = (int)($row['taken_count'] ?? 0);
                if ($total === 0) return 100;
                return (int)(($taken / $total) * 100);
            }
        }

        return 100;
    }
}
