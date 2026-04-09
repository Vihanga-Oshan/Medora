<?php
class PharmaciesModel
{
    public static function all(): array
    {
        $rows = [];
        $rs = Database::search("SELECT * FROM pharmacies ORDER BY created_at DESC, id DESC");
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    public static function create(array $in): bool
    {
        $name = Database::escape(trim((string)($in['name'] ?? '')));
        $address = Database::escape(trim((string)($in['address_line1'] ?? '')));
        $city = Database::escape(trim((string)($in['city'] ?? '')));
        $district = Database::escape(trim((string)($in['district'] ?? '')));
        $lat = (float)($in['latitude'] ?? 0);
        $lng = (float)($in['longitude'] ?? 0);
        $phone = Database::escape(trim((string)($in['phone'] ?? '')));
        $email = Database::escape(trim((string)($in['email'] ?? '')));

        if ($name === '' || $address === '' || $city === '' || $lat === 0.0 || $lng === 0.0) {
            return false;
        }

        return Database::iud("INSERT INTO pharmacies (name, address_line1, city, district, latitude, longitude, phone, email, status, created_at, updated_at)
                              VALUES ('$name', '$address', '$city', '$district', $lat, $lng, '$phone', '$email', 'active', NOW(), NOW())");
    }

    public static function toggleStatus(int $id): bool
    {
        $id = (int)$id;
        if ($id <= 0) return false;
        return Database::iud("UPDATE pharmacies SET status = IF(status='active','inactive','active'), updated_at = NOW() WHERE id = $id");
    }
}