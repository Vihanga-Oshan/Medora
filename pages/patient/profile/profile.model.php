<?php
/**
 * Profile Model
 * Ported from: patientDAO.java / PatientProfileServlet.java
 */
class ProfileModel
{
    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::escape($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM $safeTable LIKE '$safeCol'");
        return $rs && $rs->num_rows > 0;
    }

    private static function resolvePatientTable(): ?string
    {
        if (self::tableExists('patients')) return 'patients';
        if (self::tableExists('patient')) return 'patient';
        return null;
    }

    public static function getByNic(string $nic): ?array
    {
        $table = self::resolvePatientTable();
        if ($table === null) {
            return null;
        }

        $nic = Database::escape($nic);
        $selectCols = [];
        $candidateCols = ['nic', 'name', 'email', 'gender', 'phone', 'address', 'allergies', 'chronic_issues', 'guardian_nic', 'link_status'];
        foreach ($candidateCols as $col) {
            if (self::columnExists($table, $col)) {
                $selectCols[] = $col;
            }
        }

        if (empty($selectCols)) {
            return null;
        }

        $rs  = Database::search("
            SELECT " . implode(', ', $selectCols) . "
            FROM $table
            WHERE nic = '$nic'
            LIMIT 1
        ");
        return $rs ? $rs->fetch_assoc() : null;
    }

    public static function update(string $nic, array $fields): void
    {
        $table  = self::resolvePatientTable();
        if ($table === null) {
            return;
        }

        $nic    = Database::escape($nic);
        $name   = Database::escape($fields['name'] ?? '');
        $phone  = Database::escape($fields['phone'] ?? '');
        $addr   = Database::escape($fields['address'] ?? '');
        $allerg = Database::escape($fields['allergies'] ?? '');
        $chronic= Database::escape($fields['chronic_issues'] ?? '');

        $updates = [];
        if (self::columnExists($table, 'name')) $updates[] = "name = '$name'";
        if (self::columnExists($table, 'phone')) $updates[] = "phone = '$phone'";
        if (self::columnExists($table, 'address')) $updates[] = "address = '$addr'";
        if (self::columnExists($table, 'allergies')) $updates[] = "allergies = '$allerg'";
        if (self::columnExists($table, 'chronic_issues')) $updates[] = "chronic_issues = '$chronic'";

        if (empty($updates)) {
            return;
        }

        Database::iud("
            UPDATE $table
            SET " . implode(', ', $updates) . "
            WHERE nic = '$nic'
        ");
    }
}
