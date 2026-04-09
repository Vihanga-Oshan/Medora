<?php
/**
 * Admin Dashboard Model (Medora)
 */
class DashboardModel
{
    private static function tableExists(string $table): bool
    {
        Database::setUpConnection();
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        Database::setUpConnection();
        $safeTable = Database::escape($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM $safeTable LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function resolveTable(array $candidates): ?string
    {
        foreach ($candidates as $table) {
            if (self::tableExists($table)) {
                return $table;
            }
        }
        return null;
    }

    private static function safeCount(string $table, ?string $where = null): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $rs = Database::search($sql);
        if (!($rs instanceof mysqli_result)) {
            return 0;
        }
        $row = $rs->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public static function getSummary(): array
    {
        $pharmacistTable = self::resolveTable(['pharmacists', 'pharmacist']);
        $patientTable = self::resolveTable(['patients', 'patient']);
        $guardianTable = self::resolveTable(['guardians', 'guardian']);
        $prescriptionTable = self::resolveTable(['prescriptions', 'prescription']);

        // Pharmacist counts (if status exists, count ACTIVE only)
        $activePharmacists = 0;
        if ($pharmacistTable !== null) {
            $where = self::columnExists($pharmacistTable, 'status') ? "status = 'ACTIVE'" : null;
            $activePharmacists = self::safeCount($pharmacistTable, $where);
        }

        // Patient counts
        $totalPatients = $patientTable ? self::safeCount($patientTable) : 0;

        // Guardian counts
        $totalGuardians = $guardianTable ? self::safeCount($guardianTable) : 0;

        // Prescription Review Status
        $pendingReviews = 0;
        if ($prescriptionTable !== null) {
            $where = self::columnExists($prescriptionTable, 'status') ? "status = 'PENDING'" : null;
            $pendingReviews = self::safeCount($prescriptionTable, $where);
        }

        return [
            'activePharmacists' => $activePharmacists,
            'totalPatients'     => $totalPatients,
            'totalGuardians'    => $totalGuardians,
            'pendingReviews'    => $pendingReviews,
        ];
    }

    public static function getRecentLogs(): array
    {
        // Placeholder for audit logs if table exists, otherwise return empty
        return [];
    }
}
