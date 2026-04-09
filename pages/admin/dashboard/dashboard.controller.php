<?php
/**
 * Admin Dashboard Controller (Medora)
 */
require_once __DIR__ . '/dashboard.model.php';

$summary = DashboardModel::getSummary();
$recentLogs = DashboardModel::getRecentLogs();

$data = [
    'summary'    => $summary,
    'recentLogs' => $recentLogs,
    'adminName'  => $user['name'] ?? 'System Admin',
];
