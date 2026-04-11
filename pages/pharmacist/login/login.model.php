<?php

/**
 * Pharmacist Login Model
 */
class LoginModel
{
    private static function tableName(): string
    {
        $plural = Database::search("SHOW TABLES LIKE 'pharmacists'");
        if ($plural instanceof mysqli_result && $plural->num_rows > 0) {
            return 'pharmacists';
        }

        $singular = Database::search("SHOW TABLES LIKE 'pharmacist'");
        if ($singular instanceof mysqli_result && $singular->num_rows > 0) {
            return 'pharmacist';
        }

        return 'pharmacists';
    }

    /**
     * Check whether a column exists on the pharmacists table.
     */
    private static function hasColumn(string $column): bool
    {
        $table = self::tableName();
        return Database::fetchOne("SHOW COLUMNS FROM `$table` LIKE ?", 's', [$column]) !== null;
    }

    public static function findById(string $id): ?array
    {
        Database::setUpConnection();
        $table = self::tableName();
        $cleanInput = trim($id);
        $safeId = (int)preg_replace('/\D+/', '', $cleanInput);
        if ($cleanInput === '' || $safeId <= 0) {
            return null;
        }

        // Build a schema-compatible query (some DBs may not have role/status columns yet).
        $hasRole     = self::hasColumn('role');
        $hasStatus   = self::hasColumn('status');
        $hasPassword = self::hasColumn('password');
        $hasPwdHash  = self::hasColumn('password_hash');

        if (!$hasPassword && !$hasPwdHash) {
            return null;
        }

        $passwordColumn = $hasPassword ? 'password' : 'password_hash AS password';
        $roleSelect = $hasRole ? 'role' : "'pharmacist' AS role";

        $hasLicenseNo = self::hasColumn('license_no');
        $whereIdPart = ["id = ?"];
        $types = 'i';
        $params = [$safeId];
        if ($hasLicenseNo) {
            $whereIdPart[] = "license_no = ?";
            $types .= 's';
            $params[] = (string)$safeId;
        }
        $where = ["(" . implode(' OR ', $whereIdPart) . ")"];
        if ($hasRole) {
            $where[] = "role = 'pharmacist'";
        }
        if ($hasStatus) {
            $where[] = "status = 'ACTIVE'";
        }

        $query = "SELECT id, name, email, $passwordColumn, $roleSelect
                  FROM `$table`
                  WHERE " . implode(' AND ', $where) . "
                  LIMIT 1";

        return Database::fetchOne($query, $types, $params);
    }

    public static function hasPendingRequest(string $id): bool
    {
        if (!PharmacyContext::tableExists('pharmacist_requests')) {
            return false;
        }

        $cleanInput = trim($id);
        $safeId = (int)preg_replace('/\D+/', '', $cleanInput);
        if ($cleanInput === '' || $safeId <= 0) {
            return false;
        }

        return Database::fetchOne(
            "SELECT 1 FROM pharmacist_requests WHERE license_no = ? AND status = 'pending' LIMIT 1",
            's',
            [(string)$safeId]
        ) !== null;
    }
}
