<?php

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
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 3);
        $logDir = $rootDir . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $safeContext = [];
        foreach ($context as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $safeContext[$k] = $v;
            } else {
                $safeContext[$k] = json_encode($v);
            }
        }

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($safeContext, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        @file_put_contents($logDir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function findByNic(string $nic): ?array
    {
        try {
            Database::setUpConnection();

            $table = self::resolveGuardianTable();
            if ($table === null) {
                self::logError('No guardian table resolved.', ['nic_suffix' => substr($nic, -4)]);
                return null;
            }

            $nameCol = self::columnExists($table, 'name') ? 'name' : 'g_name';
            $passCol = self::columnExists($table, 'password') ? 'password' : null;
            $passHashCol = self::columnExists($table, 'password_hash') ? 'password_hash' : null;
            $passExpr = $passCol !== null ? $passCol : 'NULL';
            $passHashExpr = $passHashCol !== null ? $passHashCol : 'NULL';

            $normalizedNic = strtoupper(preg_replace('/[\s\-]+/', '', $nic) ?? $nic);
            $row = Database::fetchOne("
            SELECT nic, $nameCol AS guardian_name, $passExpr AS password_value, $passHashExpr AS password_hash_value
            FROM `$table`
            WHERE nic = ?
               OR REPLACE(REPLACE(UPPER(nic), ' ', ''), '-', '') = ?
            LIMIT 1
        ", 'ss', [$nic, $normalizedNic]);

            self::logDebug('Guardian lookup result.', [
                'table' => $table,
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

    private static function resolveGuardianTable(): ?string
    {
        if (self::tableExists('guardians')) {
            return 'guardians';
        }
        if (self::tableExists('guardian')) {
            return 'guardian';
        }
        return null;
    }

    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::escape($table);
        $safeCol = Database::escape($column);
        if ($safeTable === '') {
            return false;
        }
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }
}
