<?php
/**
 * Profile Model
 * Ported from: patientDAO.java / PatientProfileServlet.java
 */
class ProfileModel
{
    private const TABLE = 'patient';

    public static function getByNic(string $nic): ?array
    {
        $nic = Database::escape($nic);
        $rs  = Database::search("
            SELECT nic, name, email, gender, emergency_contact, allergies, chronic_issues, guardian_nic
            FROM " . self::TABLE . "
            WHERE nic = '$nic'
            LIMIT 1
        ");
        if (!($rs instanceof mysqli_result)) {
            return null;
        }
        $row = $rs->fetch_assoc();
        if (!$row) {
            return null;
        }
        $row['phone'] = (string)($row['emergency_contact'] ?? '');
        $row['address'] = '';
        $row['link_status'] = '';
        return $row;
    }

    public static function update(string $nic, array $fields): void
    {
        $nic    = Database::escape($nic);
        $name   = Database::escape($fields['name'] ?? '');
        $phone  = Database::escape($fields['phone'] ?? '');
        $allerg = Database::escape($fields['allergies'] ?? '');
        $chronic= Database::escape($fields['chronic_issues'] ?? '');

        Database::iud("
            UPDATE " . self::TABLE . "
            SET name = '$name',
                emergency_contact = '$phone',
                allergies = '$allerg',
                chronic_issues = '$chronic'
            WHERE nic = '$nic'
        ");
    }
}
