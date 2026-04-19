<?php

require_once __DIR__ . '/dashboard.model.php';
require_once __DIR__ . '/../inventory/inventory.model.php';

$metrics = DashboardModel::getMetrics();
$patientsNeedingCheck = DashboardModel::getPatientsNeedingCheck();
$patientsNeedingSchedule = DashboardModel::getPatientsNeedingSchedule();
$comments = DashboardModel::getDashboardComments();
$inventorySummary = InventoryModel::getSummary();
$inventoryReorders = InventoryModel::getReorderRecommendations(4);


$hour = (int)date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

$data = [
    'metrics'                 => $metrics,
    'patientsNeedingCheck'    => $patientsNeedingCheck,
    'patientsNeedingSchedule' => $patientsNeedingSchedule,
    'greeting'                => $greeting,
    'currentDate'             => date('d F Y'),
    'currentTime'             => date('H:i:s'),
    'comments'                => $comments,
    'inventorySummary'        => $inventorySummary,
    'inventoryReorders'       => $inventoryReorders,
];
