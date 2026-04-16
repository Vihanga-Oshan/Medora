<?php
/**
 * Admin Settings Controller
 */
require_once __DIR__ . '/settings.model.php';
require_once __DIR__ . '/../common/admin.activity.php';

$adminId = (int)($user['id'] ?? 0);
$error = null;
$success = null;
$showPasswordCard = false;

if ($adminId <= 0) {
    $error = 'Unable to resolve current admin account.';
}

if (Request::isPost() && $error === null) {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_settings_update')) {
        $error = 'Security validation failed. Please refresh and try again.';
    } else {
        $currentAdmin = SettingsModel::getCurrentAdmin($adminId);
        if ($currentAdmin === null) {
            $error = 'Current admin profile could not be loaded.';
        } else {
            $action = (string)($_POST['action'] ?? '');

            if ($action === 'update_email') {
                $newEmail = trim((string)($_POST['admin_email'] ?? ''));
                if ($newEmail === '' || strcasecmp($newEmail, (string)$currentAdmin['email']) === 0) {
                    $error = 'Please enter a new email address.';
                } else {
                    $emailError = null;
                    if (!SettingsModel::updateEmail($adminId, $newEmail, $emailError)) {
                        $error = $emailError ?? 'Unable to update email address.';
                    } else {
                        $success = 'Email updated successfully.';
                        AdminActivityLog::log(
                            $user,
                            "Updated admin email to {$newEmail}",
                            'blue',
                            $user['name'] ?? 'Admin',
                            'settings'
                        );
                    }
                }
            } elseif ($action === 'update_password') {
                $currentPassword = (string)($_POST['current_password'] ?? '');
                $newPassword = (string)($_POST['new_password'] ?? '');
                $confirmPassword = (string)($_POST['confirm_password'] ?? '');

                $passError = null;
                if (!SettingsModel::updatePassword($adminId, $currentPassword, $newPassword, $confirmPassword, $passError)) {
                    $error = $passError ?? 'Unable to update password.';
                    // Keep card visible only when current password has already been verified.
                    $showPasswordCard = !str_contains(strtolower((string)$error), 'current password is incorrect');
                } else {
                    $success = 'Password updated successfully.';
                    $showPasswordCard = false;
                    AdminActivityLog::log(
                        $user,
                        'Changed admin password',
                        'purple',
                        $user['name'] ?? 'Admin',
                        'settings'
                    );
                }
            } else {
                $error = 'Invalid settings action.';
            }
        }
    }
}

$adminProfile = SettingsModel::getCurrentAdmin($adminId);

$data = [
    'admin' => $adminProfile,
    'error' => $error,
    'success' => $success,
    'showPasswordCard' => $showPasswordCard,
];
