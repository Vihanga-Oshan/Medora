<?php
/**
 * Admin Pharmacist List Controller
 */
require_once __DIR__ . '/pharmacists.model.php';

$search = $_GET['search'] ?? '';
$pharmacists = PharmacistsModel::getAll($search);
$stats = PharmacistsModel::getStats();

$data = [
    'pharmacists' => $pharmacists,
    'stats'       => $stats,
    'search'      => $search,
];
