<?php
/**
 * Admin Settings Model
 */
class SettingsModel
{
    public static function getCurrentAdmin(int $adminId): ?array
    {
        if ($adminId <= 0) {
            return null;
        }
        $row = Database::fetchOne("
            SELECT id, email, password AS password_hash, name AS full_name
            FROM admins
            WHERE id = ?
            LIMIT 1
        ", 'i', [$adminId]);
        if (!$row) {
            return null;
        }
        return [
            'id' => (int)($row['id'] ?? 0),
            'email' => (string)($row['email'] ?? ''),
            'password_hash' => (string)($row['password_hash'] ?? ''),
            'full_name' => (string)($row['full_name'] ?? 'Admin'),
        ];
    }

    public static function updateEmail(int $adminId, string $email, ?string &$error = null): bool
    {
        if ($adminId <= 0) {
            $error = 'Admin account not found.';
            return false;
        }

        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
            return false;
        }

        $existing = Database::fetchOne("
            SELECT id
            FROM admins
            WHERE email = ? AND id <> ?
            LIMIT 1
        ", 'si', [$email, $adminId]);
        if ($existing) {
            $error = 'That email is already in use by another account.';
            return false;
        }

        return Database::execute("
            UPDATE admins
            SET email = ?
            WHERE id = ?
            LIMIT 1
        ", 'si', [$email, $adminId]);
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

        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        return Database::execute("
            UPDATE admins
            SET password = ?
            WHERE id = ?
            LIMIT 1
        ", 'si', [$hashed, $adminId]);
    }

    // Kept for backward compatibility where key-value settings are still used.
    public static function getAll(): array
    {
        $settings = [];
        $rows = Database::fetchAll("SELECT * FROM settings");
        foreach ($rows as $row) {
            $settings[$row['config_key']] = $row['config_value'];
        }
        return $settings;
    }
}
