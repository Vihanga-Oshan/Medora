<?php
/**
 * Guardian Profile Model
 */
class ProfileModel
{
    private const TABLE = 'guardian';

    public static function getProfile(string $nic): ?array
    {
        return Database::fetchOne(
            "SELECT nic, g_name AS name, contact_number AS phone, email
             FROM `" . self::TABLE . "`
             WHERE nic = ?
             LIMIT 1",
            's',
            [$nic]
        );
    }

    public static function updateName(string $nic, string $name, ?string &$error = null): bool
    {
        $name = trim($name);
        if ($name === '') {
            $error = 'Name is required.';
            return false;
        }

        return Database::execute(
            "UPDATE `" . self::TABLE . "` SET g_name = ? WHERE nic = ?",
            'ss',
            [$name, $nic]
        );
    }

    public static function updateEmail(string $nic, string $email, ?string &$error = null): bool
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
            return false;
        }

        $dupe = Database::fetchOne(
            "SELECT nic FROM `" . self::TABLE . "` WHERE email = ? AND nic <> ? LIMIT 1",
            'ss',
            [$email, $nic]
        );
        if ($dupe) {
            $error = 'That email is already in use by another guardian.';
            return false;
        }

        return Database::execute(
            "UPDATE `" . self::TABLE . "` SET email = ? WHERE nic = ?",
            'ss',
            [$email, $nic]
        );
    }

    public static function updatePhone(string $nic, string $phone, ?string &$error = null): bool
    {
        $phone = trim($phone);
        if ($phone === '') {
            $error = 'Phone number is required.';
            return false;
        }

        return Database::execute(
            "UPDATE `" . self::TABLE . "` SET contact_number = ? WHERE nic = ?",
            'ss',
            [$phone, $nic]
        );
    }

    private static function getStoredPassword(string $nic): ?string
    {
        $row = Database::fetchOne(
            "SELECT password FROM `" . self::TABLE . "` WHERE nic = ? LIMIT 1",
            's',
            [$nic]
        );
        $hash = trim((string)($row['password'] ?? ''));
        return $hash === '' ? null : $hash;
    }

    private static function verifyPassword(string $input, string $stored): bool
    {
        $hash = $stored;
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

    public static function updatePassword(string $nic, string $currentPassword, string $newPassword, string $confirmPassword, ?string &$error = null): bool
    {
        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'All password fields are required.';
            return false;
        }

        $stored = self::getStoredPassword($nic);
        if ($stored === null) {
            $error = 'Guardian account not found.';
            return false;
        }

        if (!self::verifyPassword($currentPassword, $stored)) {
            $error = 'Current password is incorrect.';
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

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        return Database::execute(
            "UPDATE `" . self::TABLE . "` SET password = ? WHERE nic = ?",
            'ss',
            [$newHash, $nic]
        );
    }

    public static function verifyCurrentPassword(string $nic, string $currentPassword, ?string &$error = null): bool
    {
        if ($currentPassword === '') {
            $error = 'Current password is required.';
            return false;
        }
        $stored = self::getStoredPassword($nic);
        if ($stored === null) {
            $error = 'Guardian account not found.';
            return false;
        }
        if (!self::verifyPassword($currentPassword, $stored)) {
            $error = 'Current password is incorrect.';
            return false;
        }
        return true;
    }
}
