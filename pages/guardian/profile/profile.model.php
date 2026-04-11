<?php
/**
 * Guardian Profile Model
 */
class ProfileModel
{
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
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function resolveGuardianTable(): ?string
    {
        if (self::tableExists('guardians')) return 'guardians';
        if (self::tableExists('guardian')) return 'guardian';
        return null;
    }

    public static function getProfile(string $nic): ?array
    {
        Database::setUpConnection();
        $table = self::resolveGuardianTable();
        if ($table === null) {
            return null;
        }

        $nameCol = self::columnExists($table, 'name') ? 'name' : 'g_name';
        $phoneCol = self::columnExists($table, 'phone') ? 'phone' : (self::columnExists($table, 'contact_number') ? 'contact_number' : "''");
        $emailCol = self::columnExists($table, 'email') ? 'email' : "''";

        $nic = Database::$connection->real_escape_string($nic);
        $rs = Database::search("
            SELECT nic, $nameCol AS name, $phoneCol AS phone, $emailCol AS email
            FROM `$table`
            WHERE nic = '$nic'
            LIMIT 1
        ");
        return $rs instanceof mysqli_result ? $rs->fetch_assoc() : null;
    }

    public static function updateProfile(string $nic, string $name, string $phone, string $email): bool
    {
        Database::setUpConnection();
        $table = self::resolveGuardianTable();
        if ($table === null) {
            return false;
        }

        $nameCol = self::columnExists($table, 'name') ? 'name' : 'g_name';
        $phoneCol = self::columnExists($table, 'phone') ? 'phone' : (self::columnExists($table, 'contact_number') ? 'contact_number' : null);
        $emailCol = self::columnExists($table, 'email') ? 'email' : null;

        $nic   = Database::$connection->real_escape_string($nic);
        $name  = Database::$connection->real_escape_string($name);
        $phone = Database::$connection->real_escape_string($phone);
        $email = Database::$connection->real_escape_string($email);

        $sets = [];
        $sets[] = "$nameCol = '$name'";
        if ($phoneCol !== null) {
            $sets[] = "$phoneCol = '$phone'";
        }
        if ($emailCol !== null) {
            $sets[] = "$emailCol = '$email'";
        }

        if (empty($sets)) {
            return false;
        }

        return Database::iud("UPDATE `$table` SET " . implode(', ', $sets) . " WHERE nic = '$nic'");
    }
}
