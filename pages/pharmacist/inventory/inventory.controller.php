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
$supplierId = max(0, (int) ($_GET['supplier_id'] ?? 0));
$categoryId = max(0, (int) ($_GET['category_id'] ?? 0));
$sortBy = trim((string) ($_GET['sort_by'] ?? 'stock'));
$sortDir = trim((string) ($_GET['sort_dir'] ?? 'asc'));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = (int) ($_GET['per_page'] ?? 15);

$queryOptions = [
    'search' => $search,
    'status' => $status,
    'supplier_id' => $supplierId,
    'category_id' => $categoryId,
    'sort_by' => $sortBy,
    'sort_dir' => $sortDir,
    'page' => $page,
    'per_page' => $perPage,
];

$listResult = InventoryModel::getInventoryList($queryOptions);
$medicines = $listResult['rows'] ?? [];
$summary = InventoryModel::getSummary();
$suppliers = InventoryModel::getSupplierOverview();
$movements = InventoryModel::getRecentMovements();
$reorders = InventoryModel::getReorderRecommendations(8);
$supplierFilters = InventoryModel::getSuppliers();
$categoryFilters = InventoryModel::getCategories();

$data = [
    'medicines' => $medicines,
    'search' => $search,
    'status' => $status,
    'supplier_id' => $supplierId,
    'category_id' => $categoryId,
    'sort_by' => $sortBy,
    'sort_dir' => $sortDir,
    'page' => (int) ($listResult['page'] ?? 1),
    'per_page' => (int) ($listResult['per_page'] ?? 15),
    'total' => (int) ($listResult['total'] ?? 0),
    'total_pages' => (int) ($listResult['total_pages'] ?? 1),
    'from' => (int) ($listResult['from'] ?? 0),
    'to' => (int) ($listResult['to'] ?? 0),
    'summary' => $summary,
    'suppliers' => $suppliers,
    'supplier_filters' => $supplierFilters,
    'category_filters' => $categoryFilters,
    'reorders' => $reorders,
    'movements' => $movements,
];
