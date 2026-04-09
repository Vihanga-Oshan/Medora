<?php
class PharmacyAssignmentsModel
{
    private static function pharmacistTable(): string
    {
        if (PharmacyContext::tableExists('pharmacists')) return 'pharmacists';
        return 'pharmacist';
    }

    public static function pharmacists(): array
    {
        $t = self::pharmacistTable();
        $rows = [];
        $rs = Database::search("SELECT id, name, email FROM `$t` ORDER BY name ASC");
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    public static function pharmacies(): array
    {
        return PharmacyContext::getPharmacies();
    }

    public static function allAssignments(): array
    {
        $t = self::pharmacistTable();
        $rows = [];
        $rs = Database::search("SELECT pu.id, pu.pharmacy_id, pu.pharmacist_id, pu.role, pu.is_primary, pu.status,
                                      ph.name AS pharmacy_name, p.name AS pharmacist_name
                               FROM pharmacy_users pu
                               LEFT JOIN pharmacies ph ON ph.id = pu.pharmacy_id
                               LEFT JOIN `$t` p ON p.id = pu.pharmacist_id
                               ORDER BY pu.id DESC");
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    public static function assign(int $pharmacyId, int $pharmacistId, bool $primary): bool
    {
        if ($pharmacyId <= 0 || $pharmacistId <= 0) return false;

        if ($primary) {
            Database::iud("UPDATE pharmacy_users SET is_primary = 0 WHERE pharmacist_id = $pharmacistId");
        }

        $exists = Database::search("SELECT id FROM pharmacy_users WHERE pharmacy_id = $pharmacyId AND pharmacist_id = $pharmacistId LIMIT 1");
        if ($exists instanceof mysqli_result && $exists->num_rows > 0) {
            $row = $exists->fetch_assoc();
            $id = (int)($row['id'] ?? 0);
            if ($id > 0) {
                return Database::iud("UPDATE pharmacy_users SET is_primary = " . ($primary ? '1' : '0') . ", status='active' WHERE id = $id");
            }
        }

        return Database::iud("INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status, created_at)
                              VALUES ($pharmacyId, $pharmacistId, $pharmacistId, 'pharmacist', " . ($primary ? '1' : '0') . ", 'active', NOW())");
    }

    public static function deactivate(int $id): bool
    {
        $id = (int)$id;
        if ($id <= 0) return false;
        return Database::iud("UPDATE pharmacy_users SET status='inactive' WHERE id = $id");
    }
}