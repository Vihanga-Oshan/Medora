<?php

/**
 * Patient Registration Model
 * Uses Medora registration fields with table/column fallback.
 */
class RegisterModel
{
    private static string $lastError = '';

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    public static function existsByNicOrEmail(string $nic, string $email): bool
    {
        Database::setUpConnection();

        $table = self::resolvePatientTable();
        if ($table === null) {
            self::$lastError = 'Patient table not found.';
            return false;
        }

        $normalizedNic = self::normalizeNic($nic);
        $safeNic = Database::$connection->real_escape_string($normalizedNic);
        $safeEmail = Database::$connection->real_escape_string($email);

        $where = "REPLACE(REPLACE(UPPER(nic), ' ', ''), '-', '') = '$safeNic'";
        if (self::columnExists($table, 'email')) {
            $where .= " OR email = '$safeEmail'";
        }

        $rs = Database::search("SELECT 1 FROM $table WHERE $where LIMIT 1");
        return $rs && $rs->num_rows > 0;
    }

    public static function createPatient(array $data): bool
    {
        Database::setUpConnection();
        self::$lastError = '';

        $table = self::resolvePatientTable();
        if ($table === null) {
            self::$lastError = 'Patient table not found.';
            return false;
        }

        $columns = [];
        $values = [];

        self::addColumn($table, $columns, $values, 'nic', $data['nic']);
        self::addColumn($table, $columns, $values, 'name', $data['name']);
        self::addColumn($table, $columns, $values, 'gender', $data['gender']);
        self::addColumn($table, $columns, $values, 'emergency_contact', $data['emergencyContact']);
        self::addColumn($table, $columns, $values, 'email', $data['email']);
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        self::addColumn($table, $columns, $values, 'password', $hashedPassword);
        self::addColumn($table, $columns, $values, 'password_hash', $hashedPassword);
        self::addColumn($table, $columns, $values, 'allergies', $data['allergies'] ?: null);
        self::addColumn($table, $columns, $values, 'chronic_issues', $data['chronic'] ?: null);
        self::addColumn($table, $columns, $values, 'guardian_nic', $data['guardianNic'] ?: null);

        // Compatibility columns seen in some PHP variants.
        self::addColumn($table, $columns, $values, 'phone', $data['emergencyContact'] ?: null);
        self::addColumn($table, $columns, $values, 'address', '');
        self::addColumn($table, $columns, $values, 'link_status', 'UNVERIFIED');

        if (empty($columns)) {
            self::$lastError = 'No compatible columns were found for patient registration.';
            return false;
        }

        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ')';

        $ok = Database::$connection->query($sql);
        if ($ok !== true) {
            self::$lastError = Database::$connection->error;
        }
        return $ok === true;
    }

    private static function addColumn(string $table, array &$columns, array &$values, string $column, $value): void
    {
        if (!self::columnExists($table, $column)) {
            return;
        }

        $columns[] = $column;
        if ($value === null) {
            $values[] = 'NULL';
            return;
        }

        $rawValue = self::fitValueToColumnSize($table, $column, (string)$value);
        $safe = Database::$connection->real_escape_string($rawValue);
        $values[] = "'$safe'";
    }

    private static function fitValueToColumnSize(string $table, string $column, string $value): string
    {
        $safeTable = Database::$connection->real_escape_string($table);
        $safeCol = Database::$connection->real_escape_string($column);
        $rs = Database::search("SHOW COLUMNS FROM $safeTable LIKE '$safeCol'");
        if (!$rs || $rs->num_rows === 0) {
            return $value;
        }

        $col = $rs->fetch_assoc();
        $type = strtolower((string)($col['Type'] ?? ''));
        if (preg_match('/^(var)?char\((\d+)\)$/', $type, $m)) {
            $max = (int)$m[2];
            if ($max > 0 && strlen($value) > $max) {
                return substr($value, 0, $max);
            }
        }

        return $value;
    }

    private static function normalizeNic(string $nic): string
    {
        $nic = strtoupper(trim($nic));
        return preg_replace('/[\s\-]+/', '', $nic) ?? $nic;
    }

    private static function resolvePatientTable(): ?string
    {
        if (self::tableExists('patient')) {
            return 'patient';
        }
        if (self::tableExists('patients')) {
            return 'patients';
        }
        return null;
    }

    private static function tableExists(string $table): bool
    {
        $safe = Database::$connection->real_escape_string($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::$connection->real_escape_string($table);
        $safeCol = Database::$connection->real_escape_string($column);
        $rs = Database::search("SHOW COLUMNS FROM $safeTable LIKE '$safeCol'");
        return $rs && $rs->num_rows > 0;
    }
}
