<?php
/**
 * /admin/pharmacists/delete — Suspend pharmacist handler
 */
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../pharmacists.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_pharmacists_delete')) {
        $base = APP_BASE ?: '';
        header('Location: ' . $base . '/admin/pharmacists?error=csrf');
        exit;
    }

    $id = (int)$_POST['id'];
    if ($id) {
        PharmacistsModel::softDelete($id);
        $base = APP_BASE ?: '';
        header('Location: ' . $base . '/admin/pharmacists?msg=suspended');
    }
} else {
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/admin/pharmacists');
}
exit;
