<?php
/**
 * Guardian Dashboard Controller
 */
require_once __DIR__ . '/dashboard.model.php';

$guardianNic = $user['id']; // Sub from JWT
$patients = DashboardModel::getPatientsByGuardian($guardianNic);
$recentAlerts = DashboardModel::getRecentAlertsByGuardian($guardianNic);
$unreadCount = DashboardModel::getUnreadAlertsCount($guardianNic);
$avgAdherence = DashboardModel::getAverageAdherence($guardianNic);

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
    'patients'        => $patients,
    'recentAlerts'   => $recentAlerts,
    'unreadCount'    => $unreadCount,
    'avgAdherence'   => $avgAdherence,
    'greeting'       => $greeting,
    'guardianName'   => $user['name'] ?? 'Guardian',
];
