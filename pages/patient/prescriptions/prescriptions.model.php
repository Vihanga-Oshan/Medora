<?php
/**
 * Prescriptions Model — all DB queries for the prescriptions module.
 */
class PrescriptionsModel
{
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

    public static function insert(string $nic, string $fileName, string $filePath): void
    {
        $pid = (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0)
            ? self::currentPharmacyId()
            : 0;

        if ($pid > 0) {
            Database::execute(
                "INSERT INTO prescriptions (patient_nic, file_name, file_path, status, upload_date, pharmacy_id) VALUES (?, ?, ?, 'PENDING', NOW(), ?)",
                'sssi',
                [$nic, $fileName, $filePath, $pid]
            );
        } else {
            Database::execute(
                "INSERT INTO prescriptions (patient_nic, file_name, file_path, status, upload_date) VALUES (?, ?, ?, 'PENDING', NOW())",
                'sss',
                [$nic, $fileName, $filePath]
            );
        }
    }
}
