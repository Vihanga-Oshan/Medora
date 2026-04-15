<?php

/**
 * Patient Registration Model
 * Uses Medora registration fields with table/column fallback.
 */
class RegisterModel
{
    private static string $lastError = '';
    private const TABLE = 'patient';

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    public static function existsByNicOrEmail(string $nic, string $email): bool
    {
        $normalizedNic = self::normalizeNic($nic);
        $row = Database::fetchOne(
            "SELECT 1 FROM " . self::TABLE . " WHERE REPLACE(REPLACE(UPPER(nic), ' ', ''), '-', '') = ? OR email = ? LIMIT 1",
            'ss',
            [$normalizedNic, $email]
        );
        return $row !== null;
    }

    public static function createPatient(array $data): bool
    {
        self::$lastError = '';

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $ok = Database::execute(
            "INSERT INTO " . self::TABLE . " (nic, name, gender, emergency_contact, email, password, allergies, chronic_issues, guardian_nic)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'sssssssss',
            [
                (string) $data['nic'],
                (string) $data['name'],
                (string) $data['gender'],
                (string) $data['emergencyContact'],
                (string) $data['email'],
                $hashedPassword,
                $data['allergies'] !== '' ? (string) $data['allergies'] : null,
                $data['chronic'] !== '' ? (string) $data['chronic'] : null,
                $data['guardianNic'] !== '' ? (string) $data['guardianNic'] : null,
            ]
        );
        if (!$ok) {
            self::$lastError = Database::$connection->error ?? '';
        }
        return $ok;
    }

    private static function normalizeNic(string $nic): string
    {
        $nic = strtoupper(trim($nic));
        return preg_replace('/[\s\-]+/', '', $nic) ?? $nic;
    }

}
