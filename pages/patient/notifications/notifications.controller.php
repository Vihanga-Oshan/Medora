<?php
/**
 * Notifications Controller
 * Ported from: NotificationServlet.java
 * Handles:
 *   GET              → list all
 *   POST ?action=delete&id=X  → delete one
 *   POST ?action=clearAll     → delete all
 */
require_once __DIR__ . '/notifications.model.php';

$nic = $user['nic'];

if (Request::isGet()) {
    $action = $_GET['action'] ?? '';
    if ($action === 'poll') {
        $initialize = (string)($_GET['initialize'] ?? '') === '1';
        $afterId = (int)($_GET['after_id'] ?? 0);

        Response::json([
            'ok' => true,
            'initialize' => $initialize,
            'latestId' => NotificationsModel::getLatestId($nic),
            'notifications' => $initialize ? [] : NotificationsModel::getAfterId($nic, $afterId),
            'timestamp' => time(),
        ]);
    }
}

if (Request::isPost()) {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        NotificationsModel::delete($id, $nic);
        Response::empty(200);
    }
    if ($action === 'clearAll') {
        NotificationsModel::clearAll($nic);
        Response::empty(200);
    }
    if ($action === 'markTaken') {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $ok = NotificationsModel::markTaken($id, $nic);
        Response::empty($ok ? 200 : 400);
    }
}

$notifications = NotificationsModel::getAll($nic);
$data = ['notifications' => $notifications];
