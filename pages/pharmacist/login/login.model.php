<?php

require_once ROOT . '/core/AppLogger.php';

/**
 * Pharmacist Login Model
 */
class LoginModel
{
    private static function writeLog(string $file, string $level, string $message, array $context = []): void
    {
        AppLogger::write($file, $level, $message, $context);
    }

    private static function tableName(): string
    {
        return 'pharmacist';
    }

    public static function findById(string $id): ?array
    {
        $table = self::tableName();
        $cleanInput = trim($id);
        $safeId = (int) preg_replace('/\D+/', '', $cleanInput);
        if ($cleanInput === '' || $safeId <= 0) {
            self::writeLog('pharmacist-login-error.log', 'ERROR', 'Invalid pharmacist identifier format.', [
                'input' => $cleanInput,
            ]);
            return null;
        }

        $row = Database::fetchOne(
            "SELECT id, name, email, password, created_at
             FROM pharmacist
             WHERE id = ?
             LIMIT 1",
            'i',
            [$safeId]
        );

        if ($row) {
            $row['role'] = 'pharmacist';
        }
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
        $cleanInput = trim($id);
        $safeId = (int) preg_replace('/\D+/', '', $cleanInput);
        if ($cleanInput === '' || $safeId <= 0) {
            return false;
        }

        return Database::fetchOne(
            "SELECT 1 FROM pharmacist_requests WHERE license_no = ? AND status = 'pending' LIMIT 1",
            's',
            [(string) $safeId]
        ) !== null;
    }
}
