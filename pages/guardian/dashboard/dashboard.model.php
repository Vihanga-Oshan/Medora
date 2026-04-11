<?php
/**
 * Guardian Dashboard Model
 */
class DashboardModel
{
    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::escape($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function resolvePatientTable(): ?string
    {
        if (self::tableExists('patients')) return 'patients';
        if (self::tableExists('patient')) return 'patient';
        return null;
    }

    public static function getPatientsByGuardian(string $guardianNic): array
    {
        Database::setUpConnection();
        $patientTable = self::resolvePatientTable();
        if ($patientTable === null) {
            return [];
        }

        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $chronicCol = self::columnExists($patientTable, 'chronic_issues') ? 'chronic_issues' : "''";
        $genderCol = self::columnExists($patientTable, 'gender') ? 'gender' : "''";

        $rs = Database::search("
            SELECT nic, name, $genderCol AS gender, $chronicCol AS chronic_issues
            FROM `$patientTable`
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
        $patientTable = self::resolvePatientTable();
        if (!self::tableExists('notifications') || $patientTable === null) {
            return [];
        }

        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $limit = max(1, (int)$limit);
        $rs = Database::search("
            SELECT n.id, n.message, n.type, n.created_at, n.is_read, p.name AS patient_name
            FROM notifications n
            JOIN `$patientTable` p ON n.patient_nic = p.nic
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
        $patientTable = self::resolvePatientTable();
        if (!self::tableExists('notifications') || $patientTable === null) {
            return 0;
        }

        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT COUNT(*) AS cnt 
            FROM notifications n
            JOIN `$patientTable` p ON n.patient_nic = p.nic
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
        $patientTable = self::resolvePatientTable();
        if ($patientTable === null) {
            return 100;
        }

        $guardianNic = Database::$connection->real_escape_string($guardianNic);

        if (self::tableExists('medication_schedules')) {
            $rs = Database::search("
                SELECT 
                    COUNT(CASE WHEN s.status = 'TAKEN' THEN 1 END) AS taken_count,
                    COUNT(*) AS total_count
                FROM medication_schedules s
                JOIN `$patientTable` p ON s.patient_nic = p.nic
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

        if (self::tableExists('medication_log')) {
            $rs = Database::search("
                SELECT
                    COUNT(CASE WHEN ml.status = 'TAKEN' THEN 1 END) AS taken_count,
                    COUNT(*) AS total_count
                FROM medication_log ml
                JOIN `$patientTable` p ON ml.patient_nic = p.nic
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
