<?php

/**
 * Patient Login Model
 * NIC based authentication with table fallback.
 */
class LoginModel
{
    public static function findByNic(string $nic): ?array
    {
        Database::setUpConnection();

        $table = self::resolvePatientTable();
        if ($table === null) {
            return null;
        }

        $nameCol = self::columnExists($table, 'name') ? 'name' : 'display_name';
        $passCol = self::columnExists($table, 'password') ? 'password' : 'password_hash';
        $nicCol = self::columnExists($table, 'nic') ? 'nic' : (self::columnExists($table, 'username') ? 'username' : null);

        if ($nicCol === null) {
            return null;
        }

        return Database::fetchOne("
            SELECT $nicCol AS nic, $nameCol AS patient_name, $passCol AS password_value
            FROM `$table`
            WHERE $nicCol = ?
            LIMIT 1
        ", 's', [$nic]);
    }

    private static function resolvePatientTable(): ?string
    {
        if (self::tableExists('patient')) {
            return 'patient';
        }
        if (self::tableExists('patients')) {
            return 'patients';
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
