<?php
/**
 * /pharmacist/inventory/delete
 * POST-only route to delete a medicine row.
 */
require_once __DIR__ . '/../../common/pharmacist.head.php';
require_once __DIR__ . '/../inventory.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/pharmacist/inventory');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    Response::redirect('/pharmacist/inventory?status=error&msg=invalid_id');
}

$ok = InventoryModel::delete($id);
if ($ok) {
    Response::redirect('/pharmacist/inventory?status=deleted');
}

Response::redirect('/pharmacist/inventory?status=error&msg=delete_failed');

