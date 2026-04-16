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
        $row = Database::fetchOne("
            SELECT nic, name, email, gender, emergency_contact, allergies, chronic_issues, guardian_nic
            FROM " . self::TABLE . "
            WHERE nic = ?
            LIMIT 1
        ", 's', [$nic]);
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
        Database::execute("
            UPDATE " . self::TABLE . "
            SET name = ?,
                emergency_contact = ?,
                allergies = ?,
                chronic_issues = ?
            WHERE nic = ?
        ", 'sssss', [
            (string) ($fields['name'] ?? ''),
            (string) ($fields['phone'] ?? ''),
            (string) ($fields['allergies'] ?? ''),
            (string) ($fields['chronic_issues'] ?? ''),
            $nic,
        ]);
    }
}
