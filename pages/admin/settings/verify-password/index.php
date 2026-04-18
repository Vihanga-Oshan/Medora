<?php
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../settings.model.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!Request::isPost()) {
    Response::json(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$adminId = (int)($user['id'] ?? 0);
$currentPassword = (string)($_POST['current_password'] ?? '');
$error = null;

if ($adminId <= 0) {
    Response::json(['ok' => false, 'code' => 'unauthorized', 'message' => 'Unauthorized'], 401);
}

$sessionKey = 'admin_pwd_verify_' . $adminId;
$state = $_SESSION[$sessionKey] ?? [
    'failed_count' => 0,
    'locked_until' => 0,
];

$now = time();
$lockedUntil = (int)($state['locked_until'] ?? 0);
if ($lockedUntil > $now) {
    $remaining = $lockedUntil - $now;
    $mins = (int)ceil($remaining / 60);
    Response::json([
        'ok' => false,
        'code' => 'locked',
        'message' => "Too many incorrect attempts. Try again in {$mins} minute(s)."
    ]);
}

$ok = SettingsModel::verifyCurrentPassword($adminId, $currentPassword, $error);
if ($ok) {
    $_SESSION[$sessionKey] = [
        'failed_count' => 0,
        'locked_until' => 0,
    ];
    Response::json([
        'ok' => true,
        'code' => 'verified',
        'message' => 'verified'
    ]);
}

$failedCount = (int)($state['failed_count'] ?? 0) + 1;
if ($failedCount >= 5) {
    $lockForSeconds = 5 * 60;
    $_SESSION[$sessionKey] = [
        'failed_count' => 0,
        'locked_until' => $now + $lockForSeconds,
    ];
    Response::json([
        'ok' => false,
        'code' => 'locked',
        'message' => 'Too many incorrect attempts. Try again in 5 minutes.'
    ]);
}

$_SESSION[$sessionKey] = [
    'failed_count' => $failedCount,
    'locked_until' => 0,
];

Response::json([
    'ok' => false,
    'code' => 'incorrect_password',
    'message' => $error ?? 'Current password is incorrect.',
    'remaining_attempts' => max(0, 5 - $failedCount)
]);
