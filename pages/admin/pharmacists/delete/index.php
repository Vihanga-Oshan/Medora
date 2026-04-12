<?php
/**
 * /admin/pharmacists/delete — Suspend pharmacist handler
 */
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../../common/admin.activity.php';
require_once __DIR__ . '/../pharmacists.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_pharmacists_delete')) {
        $base = APP_BASE ?: '';
        header('Location: ' . $base . '/admin/pharmacists?error=csrf');
        exit;
    }

    $id = (int)$_POST['id'];
    if ($id) {
        $table = PharmacyContext::tableExists('pharmacists') ? 'pharmacists' : 'pharmacist';
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $name = 'Pharmacist';
        $rs = Database::search("SELECT name FROM `$safeTable` WHERE id = $id LIMIT 1");
        if ($rs instanceof mysqli_result && $rs->num_rows > 0) {
            $row = $rs->fetch_assoc();
            $name = trim((string)($row['name'] ?? 'Pharmacist'));
        }

        if (PharmacistsModel::softDelete($id)) {
            AdminActivityLog::log($user, "Suspended pharmacist account for {$name}", 'red', $user['name'] ?? 'Admin', 'pharmacist', $id);
        }
        $base = APP_BASE ?: '';
        header('Location: ' . $base . '/admin/pharmacists?msg=suspended');
    }
} else {
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/admin/pharmacists');
}
exit;
