<?php

/**
 * InputValidator
 * Basic server-side validation helpers for auth flows.
 */
class InputValidator
{
    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function isValidEmail(string $email): bool
    {
        $email = self::normalizeEmail($email);
        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function passwordError(string $password, int $minLength = 8): ?string
    {
        if (strlen($password) < $minLength) {
            return "Password must be at least $minLength characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must include at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must include at least one lowercase letter.';
        }
        if (!preg_match('/\d/', $password)) {
            return 'Password must include at least one number.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must include at least one special character.';
        }
        return null;
    }

    public static function isValidNic(string $nic): bool
    {
        $nic = strtoupper(trim($nic));
        $nic = preg_replace('/[\s\-]+/', '', $nic) ?? $nic;
        if ($nic === '') {
            return false;
        }

        return (bool) preg_match('/^(\d{9}[VX]|\d{12})$/', $nic);
    }

    public static function isValidPhone(string $phone): bool
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    public static function isValidLicenseDigits(string $license): bool
    {
        return (bool) preg_match('/^\d{4}$/', $license);
    }

    public static function isTruthyRememberMe($value): bool
    {
        return in_array((string) $value, ['1', 'on', 'true', 'yes'], true);
    }
}
