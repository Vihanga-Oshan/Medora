<?php
require_once __DIR__ . '/../pharmacists/pharmacists.model.php';

class PharmacistRequestsModel
{
    public static function all(string $status = ''): array
    {
        $rows = [];
        if ($status !== '') {
            $rows = Database::fetchAll(
                "SELECT r.*, p.name AS pharmacy_name
                 FROM pharmacist_requests r
                 LEFT JOIN pharmacies p ON p.id = r.requested_pharmacy_id
                 WHERE r.status = ?
                 ORDER BY r.created_at DESC",
                's',
                [$status]
            );
            return $rows;
        }
        return Database::fetchAll("SELECT r.*, p.name AS pharmacy_name
                               FROM pharmacist_requests r
                               LEFT JOIN pharmacies p ON p.id = r.requested_pharmacy_id
                               ORDER BY r.created_at DESC");
    }

    public static function approve(int $requestId, int $adminId): bool
    {
        $requestId = (int) $requestId;
        if ($requestId <= 0)
            return false;

        $req = Database::fetchOne("SELECT * FROM pharmacist_requests WHERE id = ? AND status = 'pending' LIMIT 1", 'i', [$requestId]);
        if (!$req)
            return false;

        $license = (string) ($req['license_no'] ?? '');
        $passwordHash = (string) ($req['password_hash'] ?? '');
        $requestedPharmacyId = (int) ($req['requested_pharmacy_id'] ?? 0);
        $passwordPlain = 'TempPass123!';

        if ($requestedPharmacyId <= 0) {
            return false;
        }

        // Create pharmacist account through existing model.
        $ok = PharmacistsModel::create([
            'name' => (string) ($req['full_name'] ?? ''),
            'email' => (string) ($req['email'] ?? ''),
            'phone' => (string) ($req['phone'] ?? ''),
            'id' => $license,
            'password' => $passwordPlain,
            'pharmacy_id' => $requestedPharmacyId,
        ]);
        if (!$ok) {
            return false;
        }

        // Replace temp password with originally requested hash.
        Database::execute("UPDATE pharmacist SET password = ? WHERE email = ?", 'ss', [$passwordHash, (string) ($req['email'] ?? '')]);

        $adminId = (int) $adminId;
        Database::execute(
            "UPDATE pharmacist_requests SET status='approved', reviewed_by=?, reviewed_at=NOW(), note='Approved by admin' WHERE id = ?",
            'ii',
            [$adminId, $requestId]
        );

        return true;
    }

    public static function pendingCount(): int
    {
        $row = Database::fetchOne("SELECT COUNT(*) AS cnt FROM pharmacist_requests WHERE status = ?", 's', ['pending']);
        if (!$row) {
            return 0;
        }
        return (int) ($row['cnt'] ?? 0);
    }

    public static function reject(int $requestId, int $adminId, string $note = ''): bool
    {
        $requestId = (int) $requestId;
        if ($requestId <= 0)
            return false;
        $adminId = (int) $adminId;
        return Database::execute(
            "UPDATE pharmacist_requests SET status='rejected', reviewed_by=?, reviewed_at=NOW(), note=? WHERE id = ? AND status = 'pending'",
            'isi',
            [$adminId, $note === '' ? 'Rejected by admin' : $note, $requestId]
        );
    }
}
