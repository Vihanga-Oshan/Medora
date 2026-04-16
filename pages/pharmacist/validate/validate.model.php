<?php
/**
 * Prescription Validation Model
 */
class ValidateModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int)($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) return $fromToken;
        return PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
    }

    private static function prescriptionDateExpr(): string
    {
        return 'upload_date';
    }

    public static function getPendingPrescriptions(): array
    {
        $dateExpr = self::prescriptionDateExpr();
        $whereParts = ["TRIM(UPPER(status)) = 'PENDING'"];
        $types = '';
        $params = [];
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $whereParts[] = "pharmacy_id = ?";
            $types .= 'i';
            $params[] = self::currentPharmacyId();
        }
        $where = !empty($whereParts) ? ("WHERE " . implode(' AND ', $whereParts)) : '';

        return Database::fetchAll("
            SELECT
                id,
                patient_nic,
                file_name,
                file_path,
                $dateExpr AS upload_date,
                status
            FROM prescriptions
            $where
            ORDER BY $dateExpr DESC
        ", $types, $params);
    }
}
