<?php
/**
 * Prescriptions Model — all DB queries for the prescriptions module.
 */
class PrescriptionsModel
{
    private static function patientExists(string $nic): bool
    {
        if ($nic === '') {
            return false;
        }

        $row = Database::fetchOne(
            "SELECT nic FROM patient WHERE nic = ? LIMIT 1",
            's',
            [$nic]
        );

        return $row !== null;
    }

    private static function ensurePatientRecord(string $nic, string $name = 'Patient'): bool
    {
        if (self::patientExists($nic)) {
            return true;
        }

        $displayName = trim($name) !== '' ? trim($name) : 'Patient';
        $emailBase = preg_replace('/[^a-zA-Z0-9]/', '', $nic) ?: 'patient';
        $email = strtolower($emailBase) . '@medora.local';

        if (Database::fetchOne("SELECT nic FROM patient WHERE email = ? LIMIT 1", 's', [$email])) {
            $email = strtolower($emailBase) . '+' . substr(md5($nic), 0, 8) . '@medora.local';
        }

        $created = Database::execute(
            "INSERT INTO patient (nic, name, gender, emergency_contact, email, password, allergies, chronic_issues, guardian_nic)
             VALUES (?, ?, 'Other', NULL, ?, ?, NULL, NULL, NULL)",
            'ssss',
            [
                $nic,
                $displayName,
                $email,
                password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
            ]
        );

        return $created || self::patientExists($nic);
    }

    private static function currentPharmacyId(): int
    {
        return PharmacyContext::selectedPharmacyId();
    }

    private static function pharmacyWhere(string $alias = 'prescriptions'): string
    {
        $pid = self::currentPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId('prescriptions')) {
            return '1=1';
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    private static function dateColumn(): string
    {
        return 'upload_date';
    }

    public static function getByPatient(string $nic): array
    {
        $dateCol = self::dateColumn();
        $rows = Database::fetchAll("
            SELECT id, file_name, file_path, status, $dateCol AS uploaded_at
            FROM prescriptions
            WHERE patient_nic = ?
              AND " . self::pharmacyWhere('prescriptions') . "
            ORDER BY $dateCol DESC
        ", 's', [$nic]);
        $out = [];
        foreach ($rows as $row) {
            $row['formatted_upload_date'] = date('M d, Y', strtotime($row['uploaded_at']));
            $out[] = $row;
        }
        return $out;
    }

    public static function getById(int $id, string $nic): ?array
    {
        $dateCol = self::dateColumn();
        return Database::fetchOne("
            SELECT id, file_name, file_path, status, $dateCol AS uploaded_at
            FROM prescriptions
            WHERE id = ? AND patient_nic = ?
              AND " . self::pharmacyWhere('prescriptions') . "
            LIMIT 1
        ", 'is', [$id, $nic]);
    }

    public static function updateFileName(int $id, string $newName, string $nic): void
    {
        Database::execute(
            "UPDATE prescriptions SET file_name = ? WHERE id = ? AND patient_nic = ? AND " . self::pharmacyWhere('prescriptions'),
            'sis',
            [$newName, $id, $nic]
        );
    }

    public static function delete(int $id, string $nic): ?string
    {
        $row = Database::fetchOne(
            "SELECT file_path FROM prescriptions WHERE id = ? AND patient_nic = ? AND " . self::pharmacyWhere('prescriptions') . " LIMIT 1",
            'is',
            [$id, $nic]
        );
        if ($row) {
            Database::execute(
                "DELETE FROM prescriptions WHERE id = ? AND patient_nic = ? AND " . self::pharmacyWhere('prescriptions'),
                'is',
                [$id, $nic]
            );
            return $row['file_path'];
        }
        return null;
    }

    public static function insert(string $nic, string $fileName, string $filePath, string $patientName = 'Patient'): bool
    {
        $pid = (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0)
            ? self::currentPharmacyId()
            : 0;

        if ($pid > 0) {
            $ok = Database::execute(
                "INSERT INTO prescriptions (patient_nic, file_name, file_path, status, upload_date, pharmacy_id) VALUES (?, ?, ?, 'PENDING', NOW(), ?)",
                'sssi',
                [$nic, $fileName, $filePath, $pid]
            );
        } else {
            $ok = Database::execute(
                "INSERT INTO prescriptions (patient_nic, file_name, file_path, status, upload_date) VALUES (?, ?, ?, 'PENDING', NOW())",
                'sss',
                [$nic, $fileName, $filePath]
            );
        }

        if ($ok) {
            return true;
        }

        if (!self::ensurePatientRecord($nic, $patientName)) {
            return false;
        }

        if ($pid > 0) {
            return Database::execute(
                "INSERT INTO prescriptions (patient_nic, file_name, file_path, status, upload_date, pharmacy_id) VALUES (?, ?, ?, 'PENDING', NOW(), ?)",
                'sssi',
                [$nic, $fileName, $filePath, $pid]
            );
        }

        return Database::execute(
            "INSERT INTO prescriptions (patient_nic, file_name, file_path, status, upload_date) VALUES (?, ?, ?, 'PENDING', NOW())",
            'sss',
            [$nic, $fileName, $filePath]
        );
    }
}
