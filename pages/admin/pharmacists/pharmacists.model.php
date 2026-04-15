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

    private static function hasColumn(string $table, string $column): bool
    {
        return in_array($column, ['id', 'name', 'email', 'password', 'created_at'], true);
    }

    private static function isIdAutoIncrement(string $table): bool
    {
        return false;
    }

    private static function nextId(string $table): int
    {
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $row = Database::fetchOne("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM `$safeTable`");
        if (!$row) {
            return 1;
        }
        return (int) ($row['next_id'] ?? 1);
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

    public static function getAll(string $search = ''): array
    {
        Database::setUpConnection();
        $table = self::tableName();
        $search = Database::escape($search);

        $filters = [];
        if ($search !== '') {
            $filters[] = "name LIKE '%$search%'";
            $filters[] = "email LIKE '%$search%'";
            if (self::hasColumn($table, 'license_no')) {
                $filters[] = "license_no LIKE '%$search%'";
            } else {
                $filters[] = "CAST(id AS CHAR) LIKE '%$search%'";
            }
        }
        $where = $filters ? "WHERE " . implode(' OR ', $filters) : "";

        $selectPhone = self::hasColumn($table, 'phone') ? 'phone' : "'' AS phone";
        $selectLicense = self::hasColumn($table, 'license_no') ? 'license_no' : "CAST(id AS CHAR) AS license_no";
        $selectStatus = self::hasColumn($table, 'status') ? 'status' : "'ACTIVE' AS status";
        $selectCreatedAt = self::hasColumn($table, 'created_at') ? 'created_at' : 'NOW() AS created_at';

        $rs = Database::search("
            SELECT id, name, email, $selectPhone, $selectLicense, $selectStatus, $selectCreatedAt
            FROM `$table`
            $where
            ORDER BY created_at DESC
        ");

        $rows = [];
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function getById(int $id): ?array
    {
        Database::setUpConnection();
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
        Database::setUpConnection();
        $table = self::tableName();

        $nameRaw = trim((string) ($data['name'] ?? ''));
        $emailRaw = trim((string) ($data['email'] ?? ''));
        $phoneRaw = trim((string) ($data['phone'] ?? ''));
        $licenseIdRaw = $data['id'] ?? ($data['license_no'] ?? '');
        $pharmacyId = (int) ($data['pharmacy_id'] ?? 0);
        $passwordRaw = (string) ($data['password'] ?? '');

        if ($nameRaw === '' || $emailRaw === '') {
            return false;
        }

        $name = Database::escape($nameRaw);
        $email = Database::escape($emailRaw);
        $phone = Database::escape($phoneRaw);
        $licenseId = self::normalizeLicenseId($licenseIdRaw);
        if ($licenseId <= 0) {
            return false;
        }

        $license = Database::escape((string) $licenseId);
        $passwordHash = $passwordRaw !== '' ? password_hash($passwordRaw, PASSWORD_BCRYPT) : '';
        $password = Database::escape($passwordHash);

        $columns = [];
        $values = [];

        if (self::hasColumn($table, 'id') && !self::isIdAutoIncrement($table)) {
            $columns[] = 'id';
            $values[] = (string) $licenseId;
        }

        if (self::hasColumn($table, 'name')) {
            $columns[] = 'name';
            $values[] = "'$name'";
        }

        if (self::hasColumn($table, 'email')) {
            $columns[] = 'email';
            $values[] = "'$email'";
        }

        if (self::hasColumn($table, 'phone')) {
            $columns[] = 'phone';
            $values[] = "'$phone'";
        }

        if (self::hasColumn($table, 'license_no')) {
            $columns[] = 'license_no';
            $values[] = "'$license'";
        }

        if (self::hasColumn($table, 'password')) {
            $columns[] = 'password';
            $values[] = "'$password'";
        }

        if (self::hasColumn($table, 'role')) {
            $columns[] = 'role';
            $values[] = "'pharmacist'";
        }

        if (self::hasColumn($table, 'status')) {
            $columns[] = 'status';
            $values[] = "'ACTIVE'";
        }

        if (self::hasColumn($table, 'created_at')) {
            $columns[] = 'created_at';
            $values[] = 'NOW()';
        }

        if (empty($columns)) {
            return false;
        }

        $sql = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
        $ok = Database::iud($sql);
        if (!$ok) {
            return false;
        }

        $pharmacistId = 0;
        if (self::hasColumn($table, 'id') && !self::isIdAutoIncrement($table)) {
            $pharmacistId = $licenseId;
        } else {
            $pharmacistId = (int) (Database::$connection->insert_id ?? 0);
            if ($pharmacistId <= 0) {
                $row = Database::fetchOne("SELECT id FROM `$table` WHERE email = ? ORDER BY id DESC LIMIT 1", 's', [$email]);
                $pharmacistId = (int) ($row['id'] ?? 0);
            }
        }

        if ($pharmacyId <= 0 || !PharmacyContext::tableExists('pharmacies')) {
            return false;
        }

        $rsPharmacy = Database::fetchOne("SELECT id FROM pharmacies WHERE id = ? AND status = 'active' LIMIT 1", 'i', [$pharmacyId]);
        if (!$rsPharmacy) {
            return false;
        }

        if ($pharmacistId > 0 && $pharmacyId > 0 && PharmacyContext::tableExists('pharmacy_users')) {
            $exists = Database::fetchOne("SELECT id FROM pharmacy_users WHERE pharmacy_id = ? AND pharmacist_id = ? LIMIT 1", 'ii', [$pharmacyId, $pharmacistId]);
            if (!$exists) {
                Database::execute("INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status, created_at) VALUES (?, ?, ?, 'pharmacist', 1, 'active', NOW())", 'iii', [$pharmacyId, $pharmacistId, $pharmacistId]);
            }
        }

        return true;
    }

    public static function update(int $id, array $data): bool
    {
        Database::setUpConnection();
        $table = self::tableName();

        $name = (string) ($data['name'] ?? '');
        $email = (string) ($data['email'] ?? '');
        $phone = (string) ($data['phone'] ?? '');
        $licenseIdRaw = $data['id'] ?? ($data['license_no'] ?? '');
        $licenseId = self::normalizeLicenseId($licenseIdRaw);
        if ($licenseId <= 0) {
            return false;
        }
        $license = Database::escape((string) $licenseId);
        $status = Database::escape((string) ($data['status'] ?? 'ACTIVE'));

        $set = [];
        if (self::hasColumn($table, 'name')) {
            $set[] = "name = '$name'";
        }
        if (self::hasColumn($table, 'email')) {
            $set[] = "email = '$email'";
        }
        if (self::hasColumn($table, 'phone')) {
            $set[] = "phone = '$phone'";
        }
        if (self::hasColumn($table, 'id') && !self::isIdAutoIncrement($table)) {
            $set[] = "id = $licenseId";
        }
        if (self::hasColumn($table, 'license_no')) {
            $set[] = "license_no = '$license'";
        }
        if (self::hasColumn($table, 'status')) {
            $set[] = "status = '$status'";
        }
        if (!empty($data['password']) && self::hasColumn($table, 'password')) {
            $hashed = Database::escape(password_hash((string) $data['password'], PASSWORD_BCRYPT));
            $set[] = "password = '$hashed'";
        }

        if (empty($set)) {
            return false;
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE id = ?";
        $types = '';
        $params = [];
        foreach ($set as $assignment) {
            if (str_contains($assignment, " = NOW()")) {
                continue;
            }
        }
        return Database::execute($sql, 'i', [$id]);
    }

    public static function softDelete(int $id): bool
    {
        Database::setUpConnection();
        $table = self::tableName();

        if (self::hasColumn($table, 'status')) {
            return Database::execute("UPDATE `$table` SET status = 'DELETED' WHERE id = ?", 'i', [$id]);
        }

        // Fallback for minimal schemas without status support.
        return Database::execute("DELETE FROM `$table` WHERE id = ?", 'i', [$id]);
    }

    public static function getStats(): array
    {
        Database::setUpConnection();
        $table = self::tableName();

        $rs1 = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `$table`");
        if (self::hasColumn($table, 'status')) {
            $rs2 = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `$table` WHERE status = ?", 's', ['ACTIVE']);
            $rs3 = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `$table` WHERE status = ?", 's', ['DELETED']);
            $active = (int) ($rs2['cnt'] ?? 0);
            $deleted = (int) ($rs3['cnt'] ?? 0);
        } else {
            $active = (int) ($rs1['cnt'] ?? 0);
            $deleted = 0;
        }

        return [
            'total' => (int) ($rs1['cnt'] ?? 0),
            'active' => $active,
            'deleted' => $deleted,
        ];
    }
}
