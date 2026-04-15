<?php
class PharmaciesModel
{
    public static function all(): array
    {
        $rows = [];
        $rs = Database::search("SELECT * FROM pharmacies ORDER BY created_at DESC, id DESC");
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc())
                $rows[] = $r;
        }
        return $rows;
    }

    public static function create(array $in): bool
    {
        $name = trim((string) ($in['name'] ?? ''));
        $address = trim((string) ($in['address_line1'] ?? ''));
        $city = trim((string) ($in['city'] ?? ''));
        $district = trim((string) ($in['district'] ?? ''));
        $lat = (float) ($in['latitude'] ?? 0);
        $lng = (float) ($in['longitude'] ?? 0);
        $phone = trim((string) ($in['phone'] ?? ''));
        $email = trim((string) ($in['email'] ?? ''));

        if ($name === '' || $address === '' || $city === '' || $lat === 0.0 || $lng === 0.0) {
            return false;
        }

        return Database::execute(
            "INSERT INTO pharmacies (name, address_line1, city, district, latitude, longitude, phone, email, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())",
            'ssssddss',
            [$name, $address, $city, $district, $lat, $lng, $phone, $email]
        );
    }

    public static function toggleStatus(int $id): bool
    {
        $id = (int) $id;
        if ($id <= 0)
            return false;
        return Database::execute("UPDATE pharmacies SET status = IF(status='active','inactive','active'), updated_at = NOW() WHERE id = ?", 'i', [$id]);
    }
}