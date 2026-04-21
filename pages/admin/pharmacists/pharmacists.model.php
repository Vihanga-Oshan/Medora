<?php
/**
 * Admin Pharmacist Model
 */
class PharmacistsModel
{
    private static function tableName(): string
    {
        return 'pharmacist';
    }

    private static function isIdAutoIncrement(string $table): bool
    {
        return false;
    }

    private static function normalizeLicenseId($raw): int
    {
        $text = trim((string) $raw);
        if ($text === '') {
            return 0;
        }

        // Allow formats like "12345" or "MEDORA-12345" by extracting digits.
        $digits = preg_replace('/\D+/', '', $text);
        if ($digits === null || $digits === '') {
            return 0;
        }

        return (int) $digits;
    }

    public static function getAll(int $pharmacyId = 0): array
    {
        $table = self::tableName();

        $filters = ["p.status = 'ACTIVE'"];
        $types = "";
        $params = [];

        if ($pharmacyId > 0) {
            $filters[] = "pu.pharmacy_id = ?";
            $types .= "i";
            $params[] = $pharmacyId;
        }
        $where = $filters ? "WHERE " . implode(' AND ', $filters) : "";

        return Database::fetchAll("
            SELECT
                p.id,
                p.name,
                p.email,
                '' AS phone,
                COALESCE(NULLIF(p.license_no, ''), CAST(p.id AS CHAR)) AS license_no,
                p.status,
                p.created_at,
                COALESCE(ph.name, 'Unassigned') AS pharmacy_name,
                COALESCE(pu.pharmacy_id, 0) AS pharmacy_id
            FROM `$table` p
            LEFT JOIN pharmacy_users pu
                ON pu.id = (
                    SELECT pu2.id
                    FROM pharmacy_users pu2
                    WHERE pu2.pharmacist_id = p.id
                      AND pu2.status = 'active'
                    ORDER BY pu2.is_primary DESC, pu2.id ASC
                    LIMIT 1
                )
            LEFT JOIN pharmacies ph ON ph.id = pu.pharmacy_id
            $where
            ORDER BY p.created_at DESC
        ", $types, $params);
    }

    public static function getPharmacyFilters(): array
    {
        return Database::fetchAll("
            SELECT id, name
            FROM pharmacies
            WHERE status = 'active'
            ORDER BY name ASC
        ");
    }

    public static function getById(int $id): ?array
    {
        $table = self::tableName();
        $row = Database::fetchOne("SELECT * FROM `$table` WHERE id = ? LIMIT 1", 'i', [$id]);
        if (!$row) {
            return null;
        }

        $row['phone'] = $row['phone'] ?? '';
        $row['license_no'] = $row['license_no'] ?? (string) ($row['id'] ?? '');
        $row['status'] = $row['status'] ?? 'ACTIVE';
        return $row;
    }

    public static function create(array $data): bool
    {
        $table = self::tableName();

        $nameRaw = trim((string) ($data['name'] ?? ''));
        $emailRaw = trim((string) ($data['email'] ?? ''));
        $phoneRaw = trim((string) ($data['phone'] ?? ''));
        $licenseIdRaw = $data['id'] ?? ($data['license_no'] ?? '');
        $pharmacyId = (int) ($data['pharmacy_id'] ?? 0);
        $passwordRaw = trim((string) ($data['password'] ?? ''));
        $confirmPasswordRaw = trim((string) ($data['confirm_password'] ?? ''));

        if ($nameRaw === '' || $emailRaw === '') {
            return false;
        }

        $licenseId = self::normalizeLicenseId($licenseIdRaw);
        if ($licenseId <= 0) {
            return false;
        }

        if ($passwordRaw === '' || $confirmPasswordRaw === '') {
            return false;
        }

        if ($passwordRaw !== $confirmPasswordRaw) {
            return false;
        }

        $passwordHash = $passwordRaw !== '' ? password_hash($passwordRaw, PASSWORD_BCRYPT) : '';

        $columns = [];
        $valuesSql = [];
        $types = '';
        $params = [];

        if (!self::isIdAutoIncrement($table)) {
            $columns[] = 'id';
            $valuesSql[] = '?';
            $types .= 'i';
            $params[] = $licenseId;
        }

        $columns[] = 'name';
        $valuesSql[] = '?';
        $types .= 's';
        $params[] = $nameRaw;
        $columns[] = 'email';
        $valuesSql[] = '?';
        $types .= 's';
        $params[] = $emailRaw;
        $columns[] = 'phone';
        $valuesSql[] = '?';
        $types .= 's';
        $params[] = $phoneRaw !== '' ? $phoneRaw : null;
        $columns[] = 'license_no';
        $valuesSql[] = '?';
        $types .= 's';
        $params[] = (string) $licenseId;
        $columns[] = 'password';
        $valuesSql[] = '?';
        $types .= 's';
        $params[] = $passwordHash;
        $columns[] = 'status';
        $valuesSql[] = "'ACTIVE'";
        $columns[] = 'created_at';
        $valuesSql[] = 'NOW()';

        if (empty($columns)) {
            return false;
        }

        $sql = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $valuesSql) . ")";
        $ok = Database::execute($sql, $types, $params);
        if (!$ok) {
            return false;
        }

        $pharmacistId = 0;
        if (!self::isIdAutoIncrement($table)) {
            $pharmacistId = $licenseId;
        } else {
            $pharmacistId = (int) (Database::$connection->insert_id ?? 0);
            if ($pharmacistId <= 0) {
                $row = Database::fetchOne("SELECT id FROM `$table` WHERE email = ? ORDER BY id DESC LIMIT 1", 's', [$emailRaw]);
                $pharmacistId = (int) ($row['id'] ?? 0);
            }
        }

        if ($pharmacyId <= 0) {
            return false;
        }

        $rsPharmacy = Database::fetchOne("SELECT id FROM pharmacies WHERE id = ? AND status = 'active' LIMIT 1", 'i', [$pharmacyId]);
        if (!$rsPharmacy) {
            return false;
        }

        if ($pharmacistId > 0 && $pharmacyId > 0) {
            $exists = Database::fetchOne("SELECT id FROM pharmacy_users WHERE pharmacy_id = ? AND pharmacist_id = ? LIMIT 1", 'ii', [$pharmacyId, $pharmacistId]);
            if (!$exists) {
                Database::execute("INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status, created_at) VALUES (?, ?, ?, 'pharmacist', 1, 'active', NOW())", 'iii', [$pharmacyId, $pharmacistId, $pharmacistId]);
            }
        }

        return true;
    }

    public static function update(int $id, array $data): bool
    {
        $table = self::tableName();

        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $licenseIdRaw = $data['id'] ?? ($data['license_no'] ?? '');
        $licenseId = self::normalizeLicenseId($licenseIdRaw);
        if ($licenseId <= 0) {
            return false;
        }

        $set = [];
        $types = '';
        $params = [];

        $set[] = "name = ?";
        $types .= 's';
        $params[] = $name;
        $set[] = "email = ?";
        $types .= 's';
        $params[] = $email;
        if (!self::isIdAutoIncrement($table)) {
            $set[] = "id = ?";
            $types .= 'i';
            $params[] = $licenseId;
        }
        if (!empty($data['password'])) {
            $hashed = password_hash((string) $data['password'], PASSWORD_BCRYPT);
            $set[] = "password = ?";
            $types .= 's';
            $params[] = $hashed;
        }

        if (empty($set)) {
            return false;
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE id = ?";
        $types .= 'i';
        $params[] = $id;
        return Database::execute($sql, $types, $params);
    }

    public static function softDelete(int $id): bool
    {
        $table = self::tableName();
        $ok = Database::execute("UPDATE `$table` SET status = 'INACTIVE' WHERE id = ?", 'i', [$id]);
        if (!$ok) {
            return false;
        }
        Database::execute("UPDATE pharmacy_users SET status = 'inactive' WHERE pharmacist_id = ?", 'i', [$id]);
        return true;
    }

    public static function getStats(): array
    {
        $table = self::tableName();

        $rs1 = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `$table`");
        $rs2 = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `$table` WHERE status = 'ACTIVE'");
        $rs3 = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `$table` WHERE status = 'INACTIVE'");
        return [
            'total' => (int) ($rs1['cnt'] ?? 0),
            'active' => (int) ($rs2['cnt'] ?? 0),
            'deleted' => (int) ($rs3['cnt'] ?? 0),
        ];
    }
}
