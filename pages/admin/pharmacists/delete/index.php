<?php
/**
 * /admin/pharmacists/delete — Suspend pharmacist handler
 */
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../../common/admin.activity.php';
require_once __DIR__ . '/../pharmacists.model.php';

if (Request::isPost()) {
    $id = (int)$_POST['id'];
    if ($id) {
        $name = 'Pharmacist';
        $row = Database::fetchOne("SELECT name FROM pharmacist WHERE id = ? LIMIT 1", 'i', [$id]);
        if ($row) {
            $name = trim((string)($row['name'] ?? 'Pharmacist'));
        }

        if (PharmacistsModel::softDelete($id)) {
            AdminActivityLog::log($user, "Suspended pharmacist account for {$name}", 'red', $user['name'] ?? 'Admin', 'pharmacist', $id);
        }
        Response::redirect('/admin/pharmacists?msg=suspended');
    }
} else {
    Response::redirect('/admin/pharmacists');
}
