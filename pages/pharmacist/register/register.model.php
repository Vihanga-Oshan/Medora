<?php
class PharmacistRegisterModel
{
    public static function normalizeLicenseDigits(string $license): string
    {
        $digits = preg_replace('/\D+/', '', $license) ?? '';
        if (strlen($digits) !== 4) {
            return '';
        }
        return $digits;
    }

    private static function pharmacistTable(): ?string
    {
        if (PharmacyContext::tableExists('pharmacists')) return 'pharmacists';
        if (PharmacyContext::tableExists('pharmacist')) return 'pharmacist';
        return null;
    }

    public static function existsInSystem(string $email, string $license): bool
    {
        $normalizedLicense = self::normalizeLicenseDigits($license);
        if ($normalizedLicense === '') {
            return false;
        }

        $safeEmail = Database::escape($email);
        $safeLicense = Database::escape($normalizedLicense);

        if (PharmacyContext::tableExists('pharmacist_requests')) {
            $rs = Database::search("SELECT 1 FROM pharmacist_requests WHERE (email='$safeEmail' OR license_no='$safeLicense') AND status IN ('pending','approved') LIMIT 1");
            if ($rs instanceof mysqli_result && $rs->num_rows > 0) return true;
        }

        $table = self::pharmacistTable();
        if ($table === null) return false;

        $hasLicense = PharmacyContext::columnExists($table, 'license_no');
        $where = $hasLicense
            ? "(email='$safeEmail' OR license_no='$safeLicense')"
            : "(email='$safeEmail' OR CAST(id AS CHAR)='$safeLicense')";

        $rs2 = Database::search("SELECT 1 FROM `$table` WHERE $where LIMIT 1");
        return $rs2 instanceof mysqli_result && $rs2->num_rows > 0;
    }

    public static function createRequest(array $input): int
    {
        $name = Database::escape(trim((string)($input['name'] ?? '')));
        $email = Database::escape(trim((string)($input['email'] ?? '')));
        $phone = Database::escape(trim((string)($input['phone'] ?? '')));
        $normalizedLicense = self::normalizeLicenseDigits((string)($input['license_no'] ?? ''));
        $license = Database::escape($normalizedLicense);
        $pass = (string)($input['password'] ?? '');
        $pharmacyId = (int)($input['requested_pharmacy_id'] ?? 0);

        if ($name === '' || $email === '' || $license === '' || $pass === '' || $pharmacyId <= 0) {
            return 0;
        }

        if (!PharmacyContext::tableExists('pharmacies')) {
            return 0;
        }

        $rsPharmacy = Database::search("SELECT id FROM pharmacies WHERE id = $pharmacyId AND status = 'active' LIMIT 1");
        if (!($rsPharmacy instanceof mysqli_result) || $rsPharmacy->num_rows === 0) {
            return 0;
        }

        $hash = Database::escape(password_hash($pass, PASSWORD_BCRYPT));
        $pharmacySql = (string)$pharmacyId;

        $ok = Database::iud("INSERT INTO pharmacist_requests
            (full_name, email, phone, license_no, password_hash, requested_pharmacy_id, status, created_at)
            VALUES
            ('$name', '$email', '$phone', '$license', '$hash', $pharmacySql, 'pending', NOW())");
        if (!$ok) {
            return 0;
        }

        return (int)(Database::$connection->insert_id ?? 0);
    }

    public static function getRequestById(int $id): ?array
    {
        $id = (int)$id;
        if ($id <= 0 || !PharmacyContext::tableExists('pharmacist_requests')) {
            return null;
        }

        $rs = Database::search("SELECT * FROM pharmacist_requests WHERE id = $id LIMIT 1");
        if (!($rs instanceof mysqli_result)) {
            return null;
        }
        $row = $rs->fetch_assoc();
        return $row ?: null;
    }
}
