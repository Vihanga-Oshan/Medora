<?php

/**
 * Admin Login Model
 */
class LoginModel
{
    /**
     * Find an admin user by email.
     * Canonical schema uses the admins table.
     */
    public static function findByEmail(string $email): ?array
    {
        return Database::fetchOne(
            "SELECT id AS user_id,
                    email,
                    password AS password_hash,
                    'admin' AS role,
                    name AS display_name,
                    '' AS first_name
             FROM admins
             WHERE email = ?
             LIMIT 1",
            's',
            [$email]
        );
    }
}
