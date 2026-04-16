<?php
/**
 * Medicine Inventory Controller
 */
require_once __DIR__ . '/inventory.model.php';

if (Request::isPost() && (string) ($_POST['action'] ?? '') === 'adjust_stock') {
    $medicineId = (int) ($_POST['medicine_id'] ?? 0);
    $mode = trim((string) ($_POST['adjustment_mode'] ?? ''));
    $quantity = max(0, (int) ($_POST['adjustment_quantity'] ?? 0));
    $note = trim((string) ($_POST['adjustment_note'] ?? ''));
    $referenceNo = trim((string) ($_POST['reference_no'] ?? ''));

    if ($medicineId <= 0 || $quantity <= 0 || !in_array($mode, ['add', 'remove', 'set'], true)) {
        Response::redirect('/pharmacist/inventory?status=error&msg=invalid_stock_adjustment');
    }

    $ok = InventoryModel::adjustStock($medicineId, $mode, $quantity, $note, $referenceNo);
    Response::redirect($ok ? '/pharmacist/inventory?status=stock_updated' : '/pharmacist/inventory?status=error&msg=stock_adjustment_failed');
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status_filter'] ?? 'all'));
$medicines = InventoryModel::getAll($search, $status);
$summary = InventoryModel::getSummary();
$suppliers = InventoryModel::getSupplierOverview();
$movements = InventoryModel::getRecentMovements();

$data = [
    'medicines' => $medicines,
    'search'    => $search,
    'status'    => $status,
    'summary'   => $summary,
    'suppliers' => $suppliers,
    'movements' => $movements,
];
