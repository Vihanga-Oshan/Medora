<?php

/**
 * Patient Login Model
 * NIC based authentication with table fallback.
 */
class LoginModel
{
    public static function findByNic(string $nic): ?array
    {
        return Database::fetchOne("
            SELECT nic, name AS patient_name, password AS password_value, '' AS password_hash_value
            FROM patient
            WHERE nic = ?
            LIMIT 1
        ", 's', [$nic]);
    }
}
