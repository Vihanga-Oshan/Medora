<?php
/**
 * Guardian Profile Model
 */
class ProfileModel
{
    private const TABLE = 'guardian';

    public static function getProfile(string $nic): ?array
    {
        return Database::fetchOne(
            "SELECT nic, g_name AS name, contact_number AS phone, email
             FROM `" . self::TABLE . "`
             WHERE nic = ?
             LIMIT 1",
            's',
            [$nic]
        );
    }

    public static function updateProfile(string $nic, string $name, string $phone, string $email): bool
    {
        return Database::execute(
            "UPDATE `" . self::TABLE . "` SET g_name = ?, contact_number = ?, email = ? WHERE nic = ?",
            'ssss',
            [$name, $phone, $email, $nic]
        );
    }
}
