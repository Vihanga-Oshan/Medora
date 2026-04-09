<?php
/**
 * Pharmacist Dashboard Controller
 */
require_once __DIR__ . '/dashboard.model.php';

$metrics = DashboardModel::getMetrics();
$patientsNeedingCheck = DashboardModel::getPatientsNeedingCheck();
$patientsNeedingSchedule = DashboardModel::getPatientsNeedingSchedule();

// Greeting logic
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
];
