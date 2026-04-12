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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        NotificationsModel::delete($id, $nic);
        http_response_code(200);
        exit;
    }
    if ($action === 'clearAll') {
        NotificationsModel::clearAll($nic);
        http_response_code(200);
        exit;
    }
    if ($action === 'markTaken') {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $ok = NotificationsModel::markTaken($id, $nic);
        http_response_code($ok ? 200 : 400);
        exit;
    }
}

$notifications = NotificationsModel::getAll($nic);
$data = ['notifications' => $notifications];
