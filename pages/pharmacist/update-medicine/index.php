<?php
/**
 * Java-compatibility route alias: /pharmacist/update-medicine
 * Handles POST from Java-style forms by forwarding into inventory edit handler.
 */
require_once __DIR__ . '/../common/pharmacist.head.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/pharmacist/inventory');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    Response::redirect('/pharmacist/inventory?status=error&msg=invalid_id');
}

$_GET['id'] = $id;
require_once ROOT . '/pages/pharmacist/inventory/edit/index.php';
