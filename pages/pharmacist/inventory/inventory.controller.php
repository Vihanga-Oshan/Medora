<?php
/**
 * Medicine Inventory Controller
 */
require_once __DIR__ . '/inventory.model.php';

$search = $_GET['search'] ?? '';
$medicines = InventoryModel::getAll($search);

$data = [
    'medicines' => $medicines,
    'search'    => $search,
];
