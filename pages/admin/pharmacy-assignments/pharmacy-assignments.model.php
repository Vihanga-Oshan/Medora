<?php
class PharmacyAssignmentsModel
{
    public static function pharmacists(): array
    {
        return Database::fetchAll("SELECT id, name, email FROM `pharmacist` WHERE status = 'ACTIVE' ORDER BY name ASC");
    }

    public static function pharmacies(): array
    {
        return PharmacyContext::getPharmacies();
    }

    public static function allAssignments(): array
    {
        return Database::fetchAll("SELECT pu.id, pu.pharmacy_id, pu.pharmacist_id, pu.role,
                                      ph.name AS pharmacy_name, p.name AS pharmacist_name
                               FROM pharmacy_users pu
                               LEFT JOIN pharmacies ph ON ph.id = pu.pharmacy_id
                               LEFT JOIN `pharmacist` p ON p.id = pu.pharmacist_id
                               ORDER BY pu.id DESC");
    }

    public static function assign(int $pharmacyId, int $pharmacistId): bool
    {
        if ($pharmacyId <= 0 || $pharmacistId <= 0)
            return false;

        $row = Database::fetchOne("SELECT id FROM pharmacy_users WHERE pharmacy_id = ? AND pharmacist_id = ? LIMIT 1", 'ii', [$pharmacyId, $pharmacistId]);
        if ($row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                return Database::execute(
                    "UPDATE pharmacy_users SET status='active' WHERE id = ?",
                    'i',
                    [$id]
                );
            }
        }

        return Database::execute(
            "INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status, created_at)
             VALUES (?, ?, ?, 'pharmacist', 1, 'active', NOW())",
            'iii',
            [$pharmacyId, $pharmacistId, $pharmacistId]
        );
    }

    public static function deactivate(int $id): bool
    {
        $id = (int) $id;
        if ($id <= 0)
            return false;
        return Database::execute("DELETE FROM pharmacy_users WHERE id = ?", 'i', [$id]);
    }
}
