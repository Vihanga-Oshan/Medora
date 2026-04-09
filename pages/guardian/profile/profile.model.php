<?php
/**
 * Guardian Profile Model
 */
class ProfileModel
{
    public static function getProfile(string $nic): ?array
    {
        Database::setUpConnection();
        $nic = Database::$connection->real_escape_string($nic);
        $rs = Database::search("SELECT * FROM guardians WHERE nic = '$nic' LIMIT 1");
        return $rs ? $rs->fetch_assoc() : null;
    }

    public static function updateProfile(string $nic, string $name, string $phone, string $email): bool
    {
        Database::setUpConnection();
        $nic   = Database::$connection->real_escape_string($nic);
        $name  = Database::$connection->real_escape_string($name);
        $phone = Database::$connection->real_escape_string($phone);
        $email = Database::$connection->real_escape_string($email);

        return Database::iud("
            UPDATE guardians 
            SET name = '$name', phone = '$phone', email = '$email'
            WHERE nic = '$nic'
        ");
    }
}
