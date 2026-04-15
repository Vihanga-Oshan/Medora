<?php

/**
 * Guardian Registration Model
 */
class RegisterModel
{
    private static string $lastError = '';
    private const TABLE = 'guardian';

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    public static function existsByNicOrEmail(string $nic, string $email): bool
    {
        $row = Database::fetchOne(
            "SELECT 1 FROM " . self::TABLE . " WHERE nic = ? OR email = ? LIMIT 1",
            'ss',
            [$nic, $email]
        );
        return $row !== null;
    }

    public static function createGuardian(array $data): bool
    {
        self::$lastError = '';

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $ok = Database::execute(
            "INSERT INTO " . self::TABLE . " (nic, g_name, contact_number, email, password)
             VALUES (?, ?, ?, ?, ?)",
            'sssss',
            [
                (string) $data['nic'],
                (string) $data['name'],
                (string) $data['contactNumber'],
                $data['email'] !== '' ? (string) $data['email'] : null,
                $hashedPassword,
            ]
        );
        if (!$ok) {
            self::$lastError = Database::$connection->error ?? '';
        }
        return $ok;
    }

}
