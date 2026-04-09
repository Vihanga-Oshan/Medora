<?php
/**
 * Java-compatibility route alias: /pharmacist/delete-medicine
 */
require_once __DIR__ . '/../common/pharmacist.head.php';
require_once __DIR__ . '/../inventory/inventory.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/pharmacist/inventory');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    Response::redirect('/pharmacist/inventory?status=error&msg=invalid_id');
}

$ok = InventoryModel::delete($id);
Response::redirect($ok ? '/pharmacist/inventory?status=deleted' : '/pharmacist/inventory?status=error&msg=delete_failed');
