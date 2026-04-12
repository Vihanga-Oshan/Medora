<?php
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../settings.model.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!Request::isPost()) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_settings_verify_password')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Security validation failed']);
    exit;
}

$adminId = (int)($user['id'] ?? 0);
$currentPassword = (string)($_POST['current_password'] ?? '');
$error = null;

if ($adminId <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'code' => 'unauthorized', 'message' => 'Unauthorized']);
    exit;
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
    echo json_encode([
        'ok' => false,
        'code' => 'locked',
        'message' => "Too many incorrect attempts. Try again in {$mins} minute(s)."
    ]);
    exit;
}

$ok = SettingsModel::verifyCurrentPassword($adminId, $currentPassword, $error);
if ($ok) {
    $_SESSION[$sessionKey] = [
        'failed_count' => 0,
        'locked_until' => 0,
    ];
    echo json_encode([
        'ok' => true,
        'code' => 'verified',
        'message' => 'verified'
    ]);
    exit;
}

$failedCount = (int)($state['failed_count'] ?? 0) + 1;
if ($failedCount >= 5) {
    $lockForSeconds = 5 * 60;
    $_SESSION[$sessionKey] = [
        'failed_count' => 0,
        'locked_until' => $now + $lockForSeconds,
    ];
    echo json_encode([
        'ok' => false,
        'code' => 'locked',
        'message' => 'Too many incorrect attempts. Try again in 5 minutes.'
    ]);
    exit;
}

$_SESSION[$sessionKey] = [
    'failed_count' => $failedCount,
    'locked_until' => 0,
];

echo json_encode([
    'ok' => false,
    'code' => 'incorrect_password',
    'message' => $error ?? 'Current password is incorrect.',
    'remaining_attempts' => max(0, 5 - $failedCount)
]);
exit;
