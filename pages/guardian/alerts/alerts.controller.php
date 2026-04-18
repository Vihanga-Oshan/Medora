<?php
/**
 * Guardian Alerts Controller
 */
require_once __DIR__ . '/alerts.model.php';

$guardianNic = $user['id'];
$base = APP_BASE ?: '';

if (Request::isPost()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'markRead') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            AlertsModel::markAsReadForGuardian($id, $guardianNic);
        }
    } elseif ($action === 'markAllRead') {
        AlertsModel::markAllRead($guardianNic);
    }
    Response::redirect('/guardian/alerts?msg=updated');
}

$notifications = AlertsModel::getNotificationsByGuardian($guardianNic);
$filter = strtolower(trim((string)($_GET['filter'] ?? 'all')));
if (!in_array($filter, ['all', 'unread', 'critical'], true)) {
    $filter = 'all';
}

$total = count($notifications);
$unread = 0;
$critical = 0;

foreach ($notifications as $n) {
    if (!(int)$n['is_read']) {
        $unread++;
    }
    if (strtoupper((string)($n['type'] ?? '')) === 'CRITICAL') {
        $critical++;
    }
}

$resolved = $total - $unread;
$filteredNotifications = array_values(array_filter($notifications, static function (array $notification) use ($filter): bool {
    if ($filter === 'unread') {
        return !(int)($notification['is_read'] ?? 0);
    }
    if ($filter === 'critical') {
        return strtoupper((string)($notification['type'] ?? '')) === 'CRITICAL';
    }
    return true;
}));

$flash = null;
if (($_GET['msg'] ?? '') === 'updated') {
    $flash = ['type' => 'success', 'message' => 'Alert status updated successfully.'];
}

$data = [
    'notifications'         => $filteredNotifications,
    'allNotifications'      => $notifications,
    'total'                 => $total,
    'unread'                => $unread,
    'resolved'              => $resolved,
    'critical'              => $critical,
    'filter'                => $filter,
    'flash'                 => $flash,
];
