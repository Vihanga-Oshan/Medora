<?php

/**
 * Guardian Login Model
 */
class LoginModel
{
    public static function findByNic(string $nic): ?array
    {
        Database::setUpConnection();

        $table = self::resolveGuardianTable();
        if ($table === null) {
            return null;
        }

        $nameCol = self::columnExists($table, 'name') ? 'name' : 'g_name';
        $passCol = self::columnExists($table, 'password') ? 'password' : 'password_hash';

        return Database::fetchOne("
            SELECT nic, $nameCol AS guardian_name, $passCol AS password_value
            FROM `$table`
            WHERE nic = ?
            LIMIT 1
        ", 's', [$nic]);
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
        return Database::fetchOne("SHOW TABLES LIKE ?", 's', [$table]) !== null;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        if ($safeTable === '') {
            return false;
        }
        return Database::fetchOne("SHOW COLUMNS FROM `$safeTable` LIKE ?", 's', [$column]) !== null;
    }
}
