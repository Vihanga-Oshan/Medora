<?php
/**
 * Guardian Profile Controller
 */
require_once __DIR__ . '/profile.model.php';

$nic = $user['id'];
$base = APP_BASE ?: '';
$error = null;
$success = null;
$showPasswordCard = false;

// Handle Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update_name') {
        $newName = trim((string)($_POST['name'] ?? ''));
        $fieldError = null;
        if (!ProfileModel::updateName($nic, $newName, $fieldError)) {
            $error = $fieldError ?? 'Unable to update full name.';
        } else {
            $success = 'Full name updated successfully.';
        }
    } elseif ($action === 'update_email') {
        $newEmail = trim((string)($_POST['email'] ?? ''));
        $fieldError = null;
        if (!ProfileModel::updateEmail($nic, $newEmail, $fieldError)) {
            $error = $fieldError ?? 'Unable to update email address.';
        } else {
            $success = 'Email updated successfully.';
        }
    } elseif ($action === 'update_phone') {
        $newPhone = trim((string)($_POST['phone'] ?? ''));
        $fieldError = null;
        if (!ProfileModel::updatePhone($nic, $newPhone, $fieldError)) {
            $error = $fieldError ?? 'Unable to update phone number.';
        } else {
            $success = 'Phone number updated successfully.';
        }
    } elseif ($action === 'update_password') {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $passError = null;
        if (ProfileModel::updatePassword($nic, $currentPassword, $newPassword, $confirmPassword, $passError)) {
            $success = 'Password updated successfully.';
            $showPasswordCard = false;
        } else {
            $error = $passError ?? 'Password update failed. Please try again.';
            $showPasswordCard = !str_contains(strtolower((string)$error), 'current password is incorrect');
        }
    } else {
        $error = 'Invalid settings action.';
    }
}

$guardian = ProfileModel::getProfile($nic);

$data = [
    'guardian' => $guardian,
    'success' => $success,
    'error' => $error,
    'showPasswordCard' => $showPasswordCard,
];
