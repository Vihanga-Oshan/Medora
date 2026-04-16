<?php
/**
 * Profile Controller
 * Ported from: PatientProfileServlet.java
 * GET → load profile
 * POST → save changes
 */
require_once __DIR__ . '/profile.model.php';

$nic        = $user['nic'];
$success    = false;
$error      = null;

if (Request::isPost()) {
    $name   = trim($_POST['name']           ?? '');
    $phone  = trim($_POST['phone']          ?? '');
    $address= trim($_POST['address']        ?? '');
    $allerg = trim($_POST['allergies']      ?? '');
    $chronic= trim($_POST['chronic_issues'] ?? '');

    if ($name === '') {
        $error = 'Name cannot be empty.';
    } else {
        ProfileModel::update($nic, [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'allergies' => $allerg,
            'chronic_issues' => $chronic,
        ]);
        // Reload to show updated data
        Response::redirect('/patient/profile?saved=1');
    }
}

$profile = ProfileModel::getByNic($nic);
$success = isset($_GET['saved']);

$data = [
    'profile' => $profile,
    'success' => $success,
    'error'   => $error,
];
