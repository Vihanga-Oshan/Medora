<?php

require_once ROOT . '/core/AppLogger.php';

/**
 * Guardian Login Model
 */
class LoginModel
{
    private static function logDebug(string $message, array $context = []): void
    {
        self::writeLog('guardian-login-debug.log', 'DEBUG', $message, $context);
    }

    private static function logError(string $message, array $context = []): void
    {
        self::writeLog('guardian-login-error.log', 'ERROR', $message, $context);
    }

    private static function writeLog(string $file, string $level, string $message, array $context): void
    {
        AppLogger::write($file, $level, $message, $context);
    }

    public static function findByNic(string $nic): ?array
    {
        try {
            $normalizedNic = strtoupper(preg_replace('/[\s\-]+/', '', $nic) ?? $nic);
            $row = Database::fetchOne("
            SELECT nic, g_name AS guardian_name, password AS password_value, NULL AS password_hash_value
            FROM guardian
            WHERE nic = ?
               OR REPLACE(REPLACE(UPPER(nic), ' ', ''), '-', '') = ?
            LIMIT 1
        ", 'ss', [$nic, $normalizedNic]);

            self::logDebug('Guardian lookup result.', [
                'table' => 'guardian',
                'nic_suffix' => substr($nic, -4),
                'normalized_nic_suffix' => substr($normalizedNic, -4),
                'found' => $row !== null ? 1 : 0,
                'has_password' => isset($row['password_value']) && (string)$row['password_value'] !== '' ? 1 : 0,
                'has_password_hash' => isset($row['password_hash_value']) && (string)$row['password_hash_value'] !== '' ? 1 : 0,
            ]);

            return $row;
        } catch (Throwable $e) {
            self::logError('Exception during guardian lookup.', [
                'nic_suffix' => substr($nic, -4),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
