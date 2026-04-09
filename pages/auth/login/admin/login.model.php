<?php

/**
 * Admin Login Model
 */
class LoginModel
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
        $rs = Database::search("SHOW COLUMNS FROM $safeTable LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    /**
     * Find an admin user by email.
     * Admins must have the 'admin' role.
     */
    public static function findByEmail(string $email): ?array
    {
        Database::setUpConnection();

        // Preferred schema: users table with role='admin'
        if (self::tableExists('users')) {
            $table = 'users';
            $idCol = self::columnExists($table, 'user_id') ? 'user_id' : 'id';
            $emailCol = self::columnExists($table, 'email') ? 'email' : (self::columnExists($table, 'admin_email') ? 'admin_email' : null);
            $passCol = self::columnExists($table, 'password_hash') ? 'password_hash' : (self::columnExists($table, 'password') ? 'password' : null);
            $displayCol = self::columnExists($table, 'display_name') ? 'display_name' : (self::columnExists($table, 'name') ? 'name' : "''");
            $firstCol = self::columnExists($table, 'first_name') ? 'first_name' : "''";

            if ($emailCol !== null && $passCol !== null) {
                $where = ["$emailCol = ?"];
                if (self::columnExists($table, 'role')) {
                    $where[] = "role = 'admin'";
                }
                if (self::columnExists($table, 'is_active')) {
                    $where[] = "is_active = 1";
                }

                $query = "SELECT $idCol AS user_id,
                                 $emailCol AS email,
                                 $passCol AS password_hash,
                                 " . (self::columnExists($table, 'role') ? 'role' : "'admin'") . " AS role,
                                 $displayCol AS display_name,
                                 $firstCol AS first_name
                          FROM $table
                          WHERE " . implode(' AND ', $where) . "
                          LIMIT 1";

                $user = Database::fetchOne($query, 's', [$email]);
                if ($user) {
                    return $user;
                }
            }
        }

        // Fallback schema: standalone admin tables
        $candidateAdminTables = ['admin', 'admins'];
        foreach ($candidateAdminTables as $table) {
            if (!self::tableExists($table)) {
                continue;
            }

            $idCol = self::columnExists($table, 'admin_id') ? 'admin_id' : (self::columnExists($table, 'id') ? 'id' : null);
            $emailCol = self::columnExists($table, 'email') ? 'email' : (self::columnExists($table, 'admin_email') ? 'admin_email' : null);
            $passCol = self::columnExists($table, 'password_hash') ? 'password_hash' : (self::columnExists($table, 'password') ? 'password' : null);
            $nameCol = self::columnExists($table, 'full_name') ? 'full_name' : (self::columnExists($table, 'name') ? 'name' : "''");

            if ($idCol === null || $emailCol === null || $passCol === null) {
                continue;
            }

            $where = ["$emailCol = ?"];
            if (self::columnExists($table, 'status')) {
                $where[] = "status = 'ACTIVE'";
            }
            if (self::columnExists($table, 'is_active')) {
                $where[] = "is_active = 1";
            }

            $user = Database::fetchOne(
                "SELECT $idCol AS user_id,
                        $emailCol AS email,
                        $passCol AS password_hash,
                        'admin' AS role,
                        $nameCol AS display_name,
                        '' AS first_name
                 FROM $table
                 WHERE " . implode(' AND ', $where) . "
                 LIMIT 1",
                 's',
                 [$email]
            );
            if ($user) {
                return $user;
            }
        }

        return null;
    }
}
