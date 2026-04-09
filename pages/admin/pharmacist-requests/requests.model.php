<?php
require_once __DIR__ . '/../pharmacists/pharmacists.model.php';

class PharmacistRequestsModel
{
    public static function all(string $status = ''): array
    {
        $where = '';
        if ($status !== '') {
            $safe = Database::escape($status);
            $where = "WHERE r.status = '$safe'";
        }

        $rows = [];
        $rs = Database::search("SELECT r.*, p.name AS pharmacy_name
                               FROM pharmacist_requests r
                               LEFT JOIN pharmacies p ON p.id = r.requested_pharmacy_id
                               $where
                               ORDER BY r.created_at DESC");
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc()) $rows[] = $r;
        }
        return $rows;
    }

    public static function approve(int $requestId, int $adminId): bool
    {
        $requestId = (int)$requestId;
        if ($requestId <= 0) return false;

        $rs = Database::search("SELECT * FROM pharmacist_requests WHERE id = $requestId AND status = 'pending' LIMIT 1");
        if (!($rs instanceof mysqli_result) || $rs->num_rows === 0) return false;
        $req = $rs->fetch_assoc();

        $license = (string)($req['license_no'] ?? '');
        $passwordHash = (string)($req['password_hash'] ?? '');
        $requestedPharmacyId = (int)($req['requested_pharmacy_id'] ?? 0);
        $passwordPlain = 'TempPass123!';

        if ($requestedPharmacyId <= 0) {
            return false;
        }

        // Create pharmacist account through existing model.
        $ok = PharmacistsModel::create([
            'name' => (string)($req['full_name'] ?? ''),
            'email' => (string)($req['email'] ?? ''),
            'phone' => (string)($req['phone'] ?? ''),
            'id' => $license,
            'password' => $passwordPlain,
            'pharmacy_id' => $requestedPharmacyId,
        ]);
        if (!$ok) {
            return false;
        }

        // Replace temp password with originally requested hash.
        $table = PharmacyContext::tableExists('pharmacists') ? 'pharmacists' : 'pharmacist';
        $safeEmail = Database::escape((string)($req['email'] ?? ''));
        $safeHash = Database::escape($passwordHash);
        if (PharmacyContext::columnExists($table, 'password')) {
            Database::iud("UPDATE `$table` SET password = '$safeHash' WHERE email = '$safeEmail'");
        }

        $adminId = (int)$adminId;
        Database::iud("UPDATE pharmacist_requests
                       SET status='approved', reviewed_by=$adminId, reviewed_at=NOW(), note='Approved by admin'
                       WHERE id = $requestId");

        return true;
    }

    public static function pendingCount(): int
    {
        $rs = Database::search("SELECT COUNT(*) AS cnt FROM pharmacist_requests WHERE status='pending'");
        if (!($rs instanceof mysqli_result)) {
            return 0;
        }
        $row = $rs->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public static function reject(int $requestId, int $adminId, string $note = ''): bool
    {
        $requestId = (int)$requestId;
        if ($requestId <= 0) return false;
        $safeNote = Database::escape($note === '' ? 'Rejected by admin' : $note);
        $adminId = (int)$adminId;
        return Database::iud("UPDATE pharmacist_requests
                             SET status='rejected', reviewed_by=$adminId, reviewed_at=NOW(), note='$safeNote'
                             WHERE id = $requestId AND status = 'pending'");
    }
}
