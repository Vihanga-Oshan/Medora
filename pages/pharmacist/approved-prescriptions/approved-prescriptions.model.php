<?php
/**
 * Approved Prescriptions Model
 */
class ApprovedPrescriptionsModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int)($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) return $fromToken;
        return PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::escape($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function prescriptionDateExpr(): string
    {
        if (self::columnExists('prescriptions', 'upload_date')) return 'upload_date';
        if (self::columnExists('prescriptions', 'uploaded_at')) return 'uploaded_at';
        if (self::columnExists('prescriptions', 'created_at')) return 'created_at';
        return 'NULL';
    }

    public static function getApprovedPrescriptions(): array
    {
        $hasStatus = self::columnExists('prescriptions', 'status');
        $hasPatientNic = self::columnExists('prescriptions', 'patient_nic');
        $hasFileName = self::columnExists('prescriptions', 'file_name');
        $hasFilePath = self::columnExists('prescriptions', 'file_path');

        $dateExpr = self::prescriptionDateExpr();
        $orderBy = $dateExpr !== 'NULL' ? "$dateExpr DESC" : 'id DESC';
        $whereParts = [];
        if ($hasStatus) {
            $whereParts[] = "TRIM(UPPER(status)) = 'APPROVED'";
        }
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $whereParts[] = "pharmacy_id = " . self::currentPharmacyId();
        }
        $where = !empty($whereParts) ? ("WHERE " . implode(' AND ', $whereParts)) : '';

        $rs = Database::search("
            SELECT
                id,
                " . ($hasPatientNic ? "patient_nic" : "''") . " AS patient_nic,
                " . ($hasFileName ? "file_name" : "CONCAT('Prescription #', id)") . " AS file_name,
                " . ($hasFilePath ? "file_path" : "''") . " AS file_path,
                $dateExpr AS upload_date
            FROM prescriptions
            $where
            ORDER BY $orderBy
        ");

        $rows = [];
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
