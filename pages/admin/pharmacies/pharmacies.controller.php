<?php
require_once __DIR__ . '/pharmacies.model.php';
require_once __DIR__ . '/../common/admin.activity.php';

$base = APP_BASE ?: '';
$error = null;

if (Request::isPost()) {
    $action = Request::post('action') ?? '';
    if ($action === 'create') {
        $ok = PharmaciesModel::create($_POST);
        if (!$ok) {
            $error = 'Unable to create pharmacy. Please check required fields.';
        } else {
            $pharmacyName = trim((string)($_POST['name'] ?? 'Pharmacy'));
            if ($pharmacyName !== '') {
                AdminActivityLog::log($user, "Created pharmacy {$pharmacyName}", 'green', $user['name'] ?? 'Admin', 'pharmacy');
            }
            Response::redirect('/admin/pharmacies');
        }
    }

    if ($action === 'toggle') {
        $id = (int)(Request::post('id') ?? 0);
        $ok = PharmaciesModel::toggleStatus($id);
        if ($ok && $id > 0) {
            $row = Database::fetchOne("SELECT name, status FROM pharmacies WHERE id = ? LIMIT 1", 'i', [$id]);
            if ($row) {
                $name = trim((string)($row['name'] ?? 'Pharmacy'));
                $status = strtoupper((string)($row['status'] ?? 'active'));
                AdminActivityLog::log($user, "Changed {$name} status to {$status}", 'blue', $user['name'] ?? 'Admin', 'pharmacy', $id);
            }
        }
        Response::redirect('/admin/pharmacies');
    }
}

$pharmacies = PharmaciesModel::all();
