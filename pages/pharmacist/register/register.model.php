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

    public static function existsInSystem(string $email, string $license): bool
    {
        $normalizedLicense = self::normalizeLicenseDigits($license);
        if ($normalizedLicense === '') {
            return false;
        }

        $row = Database::fetchOne("SELECT 1 FROM pharmacist_requests WHERE (email = ? OR license_no = ?) AND status IN ('pending','approved') LIMIT 1", 'ss', [$email, $normalizedLicense]);
        if ($row)
            return true;

        $row2 = Database::fetchOne("SELECT 1 FROM `pharmacist` WHERE (email = ? OR license_no = ?) LIMIT 1", 'ss', [$email, $normalizedLicense]);
        return $row2 !== null;
    }

    public static function createRequest(array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $normalizedLicense = self::normalizeLicenseDigits((string) ($input['license_no'] ?? ''));
        $pass = (string) ($input['password'] ?? '');
        $pharmacyId = (int) ($input['requested_pharmacy_id'] ?? 0);

        if ($name === '' || $email === '' || $normalizedLicense === '' || $pass === '' || $pharmacyId <= 0) {
            return 0;
        }

        $rsPharmacy = Database::fetchOne("SELECT id FROM pharmacies WHERE id = ? AND status = 'active' LIMIT 1", 'i', [$pharmacyId]);
        if (!$rsPharmacy) {
            return 0;
        }

        $ok = Database::execute(
            "INSERT INTO pharmacist_requests
            (full_name, email, phone, license_no, password_hash, requested_pharmacy_id, status, created_at)
            VALUES
            (?, ?, ?, ?, ?, ?, 'pending', NOW())",
            'sssssi',
            [$name, $email, $phone, $normalizedLicense, password_hash($pass, PASSWORD_BCRYPT), $pharmacyId]
        );
        if (!$ok) {
            return 0;
        }

        return (int) (Database::$connection->insert_id ?? 0);
    }

    public static function getRequestById(int $id): ?array
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        return Database::fetchOne("SELECT * FROM pharmacist_requests WHERE id = ? LIMIT 1", 'i', [$id]);
    }
}
