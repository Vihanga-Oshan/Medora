<?php
require_once __DIR__ . '/pharmacies.model.php';

$base = APP_BASE ?: '';
$error = null;

if (Request::isPost()) {
    $action = Request::post('action') ?? '';
    if ($action === 'create') {
        $ok = PharmaciesModel::create($_POST);
        if (!$ok) {
            $error = 'Unable to create pharmacy. Please check required fields.';
        } else {
            Response::redirect('/admin/pharmacies');
        }
    }

    if ($action === 'toggle') {
        $id = (int)(Request::post('id') ?? 0);
        PharmaciesModel::toggleStatus($id);
        Response::redirect('/admin/pharmacies');
    }
}

$pharmacies = PharmaciesModel::all();