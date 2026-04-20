<?php
/**
 * Admin Pharmacist List Controller
 */
require_once __DIR__ . '/pharmacists.model.php';

$pharmacyFilter = (int) ($_GET['pharmacy_id'] ?? 0);
$pharmacists = PharmacistsModel::getAll($pharmacyFilter);
$pharmacies = PharmacistsModel::getPharmacyFilters();
$stats = PharmacistsModel::getStats();

$data = [
    'pharmacists'    => $pharmacists,
    'pharmacies'     => $pharmacies,
    'pharmacyFilter' => $pharmacyFilter,
    'stats'          => $stats,
];
