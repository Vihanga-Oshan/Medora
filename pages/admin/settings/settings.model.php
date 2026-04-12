<?php
/**
 * Admin Settings Model
 */
class SettingsModel
{
    private static function safeTable(string $table): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }

    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = self::safeTable($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function resolveAdminTable(): ?string
    {
        if (self::tableExists('admins')) return 'admins';
        if (self::tableExists('admin')) return 'admin';
        if (self::tableExists('users')) return 'users';
        return null;
    }

    private static function columnsFor(string $table): ?array
    {
        $safeTable = self::safeTable($table);
        if ($safeTable === 'users') {
            $id = self::columnExists($safeTable, 'user_id') ? 'user_id' : (self::columnExists($safeTable, 'id') ? 'id' : null);
            $email = self::columnExists($safeTable, 'email') ? 'email' : (self::columnExists($safeTable, 'admin_email') ? 'admin_email' : null);
            $password = self::columnExists($safeTable, 'password_hash') ? 'password_hash' : (self::columnExists($safeTable, 'password') ? 'password' : null);
            $name = self::columnExists($safeTable, 'display_name') ? 'display_name' : (self::columnExists($safeTable, 'name') ? 'name' : null);
            $role = self::columnExists($safeTable, 'role') ? 'role' : null;

            if ($id && $email && $password) {
                return [
                    'table' => $safeTable,
                    'id' => $id,
                    'email' => $email,
                    'password' => $password,
                    'name' => $name,
                    'role' => $role,
                ];
            }
            return null;
        }

        $id = self::columnExists($safeTable, 'id') ? 'id' : (self::columnExists($safeTable, 'admin_id') ? 'admin_id' : null);
        $email = self::columnExists($safeTable, 'email') ? 'email' : (self::columnExists($safeTable, 'admin_email') ? 'admin_email' : null);
        $password = self::columnExists($safeTable, 'password_hash') ? 'password_hash' : (self::columnExists($safeTable, 'password') ? 'password' : null);
        $name = self::columnExists($safeTable, 'name') ? 'name' : (self::columnExists($safeTable, 'full_name') ? 'full_name' : null);

        if ($id && $email && $password) {
            return [
                'table' => $safeTable,
                'id' => $id,
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'role' => null,
            ];
        }
        return null;
    }

    public static function getCurrentAdmin(int $adminId): ?array
    {
        Database::setUpConnection();
        if ($adminId <= 0) {
            return null;
        }

        $table = self::resolveAdminTable();
        if ($table === null) {
            return null;
        }
        $cols = self::columnsFor($table);
        if ($cols === null) {
            return null;
        }

        $nameSelect = $cols['name'] ? "{$cols['name']} AS full_name" : "'Admin' AS full_name";
        $where = "{$cols['id']} = $adminId";
        if ($cols['role']) {
            $where .= " AND {$cols['role']} = 'admin'";
        }

        $rs = Database::search("
            SELECT {$cols['id']} AS id, {$cols['email']} AS email, {$cols['password']} AS password_hash, $nameSelect
            FROM `{$cols['table']}`
            WHERE $where
            LIMIT 1
        ");
        if (!($rs instanceof mysqli_result) || $rs->num_rows === 0) {
            return null;
        }

        $row = $rs->fetch_assoc();
        return [
            'id' => (int)($row['id'] ?? 0),
            'email' => (string)($row['email'] ?? ''),
            'password_hash' => (string)($row['password_hash'] ?? ''),
            'full_name' => (string)($row['full_name'] ?? 'Admin'),
        ];
    }

    public static function updateEmail(int $adminId, string $email, ?string &$error = null): bool
    {
        Database::setUpConnection();
        $table = self::resolveAdminTable();
        if ($table === null || $adminId <= 0) {
            $error = 'Admin account not found.';
            return false;
        }
        $cols = self::columnsFor($table);
        if ($cols === null) {
            $error = 'Admin account schema is unsupported.';
            return false;
        }

        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
            return false;
        }

        $safeEmail = Database::escape($email);
        $whereRole = $cols['role'] ? " AND {$cols['role']} = 'admin'" : '';
        $rsExisting = Database::search("
            SELECT {$cols['id']} AS id
            FROM `{$cols['table']}`
            WHERE {$cols['email']} = '$safeEmail' AND {$cols['id']} <> $adminId $whereRole
            LIMIT 1
        ");
        if ($rsExisting instanceof mysqli_result && $rsExisting->num_rows > 0) {
            $error = 'That email is already in use by another account.';
            return false;
        }

        return Database::iud("
            UPDATE `{$cols['table']}`
            SET {$cols['email']} = '$safeEmail'
            WHERE {$cols['id']} = $adminId $whereRole
            LIMIT 1
        ");
    }

    private static function verifyPassword(string $input, string $storedHash): bool
    {
        $hash = $storedHash;
        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        if (preg_match('/^\$2[aby]\$/', $hash)) {
            return password_verify($input, $hash);
        }
        if (preg_match('/^[a-f0-9]{64}$/i', $hash)) {
            return hash_equals(strtolower($hash), hash('sha256', $input));
        }
        return hash_equals((string)$hash, (string)$input);
    }

    public static function verifyCurrentPassword(int $adminId, string $currentPassword, ?string &$error = null): bool
    {
        $admin = self::getCurrentAdmin($adminId);
        if ($admin === null) {
            $error = 'Admin account not found.';
            return false;
        }
        if ($currentPassword === '') {
            $error = 'Current password is required.';
            return false;
        }
        if (!self::verifyPassword($currentPassword, (string)$admin['password_hash'])) {
            $error = 'Current password is incorrect.';
            return false;
        }
        return true;
    }

    public static function updatePassword(int $adminId, string $currentPassword, string $newPassword, string $confirmPassword, ?string &$error = null): bool
    {
        Database::setUpConnection();
        $admin = self::getCurrentAdmin($adminId);
        if ($admin === null) {
            $error = 'Admin account not found.';
            return false;
        }

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'All password fields are required to change password.';
            return false;
        }

        if (!self::verifyCurrentPassword($adminId, $currentPassword, $error)) {
            return false;
        }

        if (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
            return false;
        }

        if ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
            return false;
        }

        $table = self::resolveAdminTable();
        $cols = $table ? self::columnsFor($table) : null;
        if ($cols === null) {
            $error = 'Admin account schema is unsupported.';
            return false;
        }

        $hashed = Database::escape(password_hash($newPassword, PASSWORD_BCRYPT));
        $whereRole = $cols['role'] ? " AND {$cols['role']} = 'admin'" : '';

        return Database::iud("
            UPDATE `{$cols['table']}`
            SET {$cols['password']} = '$hashed'
            WHERE {$cols['id']} = $adminId $whereRole
            LIMIT 1
        ");
    }

    // Kept for backward compatibility where key-value settings are still used.
    public static function getAll(): array
    {
        $settings = [];
        if (!self::tableExists('settings')) {
            return $settings;
        }

        $rs = Database::search("SELECT * FROM settings");
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $settings[$row['config_key']] = $row['config_value'];
            }
        }
        return $settings;
    }
}
