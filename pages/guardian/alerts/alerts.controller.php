<?php
/**
 * Guardian Alerts Controller
 */
require_once __DIR__ . '/alerts.model.php';

$guardianNic = $user['id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'markRead') {
        $id = (int)$_POST['id'];
        AlertsModel::markAsRead($id);
    } elseif ($action === 'markAllRead') {
        AlertsModel::markAllRead($guardianNic);
    }
    header('Location: /guardian/alerts');
    exit;
}

$notifications = AlertsModel::getNotificationsByGuardian($guardianNic);

// Stats for layout
$total = count($notifications);
$unread = 0;
foreach ($notifications as $n) if (!$n['is_read']) $unread++;
$resolved = $total - $unread;

$data = [
    'notifications' => $notifications,
    'total'         => $total,
    'unread'        => $unread,
    'resolved'      => $resolved,
];
