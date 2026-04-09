<?php
/**
 * Guardian Profile Controller
 */
require_once __DIR__ . '/profile.model.php';

$nic = $user['id'];

// Handle Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';

    if (ProfileModel::updateProfile($nic, $name, $phone, $email)) {
        header('Location: /guardian/profile?msg=updated');
        exit;
    }
}

$guardian = ProfileModel::getProfile($nic);

$data = [
    'guardian' => $guardian,
];
