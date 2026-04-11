<?php

/**
 * Pharmacist Login Model
 */
class LoginModel
{
    private static function writeLog(string $file, string $level, string $message, array $context = []): void
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 3);
        $logDir = $rootDir . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($context, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        @file_put_contents($logDir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }

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
        $safeTable = Database::escape($table);
        $safeColumn = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeColumn'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    public static function findById(string $id): ?array
    {
        Database::setUpConnection();
        $table = self::tableName();
        $cleanInput = trim($id);
        $safeId = (int)preg_replace('/\D+/', '', $cleanInput);
        if ($cleanInput === '' || $safeId <= 0) {
            self::writeLog('pharmacist-login-error.log', 'ERROR', 'Invalid pharmacist identifier format.', [
                'input' => $cleanInput,
            ]);
            return null;
        }

        // Build a schema-compatible query (some DBs may not have role/status columns yet).
        $hasRole     = self::hasColumn('role');
        $hasStatus   = self::hasColumn('status');
        $hasPassword = self::hasColumn('password');
        $hasPwdHash  = self::hasColumn('password_hash');

        if (!$hasPassword && !$hasPwdHash) {
            self::writeLog('pharmacist-login-error.log', 'ERROR', 'No password columns available on pharmacist table.', [
                'table' => $table,
            ]);
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

        $row = Database::fetchOne($query, $types, $params);
        self::writeLog('pharmacist-login-debug.log', 'DEBUG', 'Pharmacist lookup result.', [
            'table' => $table,
            'id' => $safeId,
            'found' => $row !== null ? 1 : 0,
            'has_password' => $row && !empty($row['password']) ? 1 : 0,
        ]);
        return $row;
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
