<?php
class PharmacyAssignmentsModel
{
    private static function pharmacistTable(): string
    {
        return 'pharmacist';
    }

    public static function pharmacists(): array
    {
        $t = self::pharmacistTable();
        $rows = [];
        $rs = Database::search("SELECT id, name, email FROM `$t` ORDER BY name ASC");
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc())
                $rows[] = $r;
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
            while ($r = $rs->fetch_assoc())
                $rows[] = $r;
        }
        return $rows;
    }

    public static function assign(int $pharmacyId, int $pharmacistId, bool $primary): bool
    {
        if ($pharmacyId <= 0 || $pharmacistId <= 0)
            return false;

        if ($primary) {
            Database::execute("UPDATE pharmacy_users SET is_primary = 0 WHERE pharmacist_id = ?", 'i', [$pharmacistId]);
        }

        $row = Database::fetchOne("SELECT id FROM pharmacy_users WHERE pharmacy_id = ? AND pharmacist_id = ? LIMIT 1", 'ii', [$pharmacyId, $pharmacistId]);
        if ($row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                return Database::execute(
                    "UPDATE pharmacy_users SET is_primary = ?, status='active' WHERE id = ?",
                    'ii',
                    [$primary ? 1 : 0, $id]
                );
            }
        }

        return Database::execute(
            "INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status, created_at)
             VALUES (?, ?, ?, 'pharmacist', ?, 'active', NOW())",
            'iiii',
            [$pharmacyId, $pharmacistId, $pharmacistId, $primary ? 1 : 0]
        );
    }

    public static function deactivate(int $id): bool
    {
        $id = (int) $id;
        if ($id <= 0)
            return false;
        return Database::execute("UPDATE pharmacy_users SET status='inactive' WHERE id = ?", 'i', [$id]);
    }
}
