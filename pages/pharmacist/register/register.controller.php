<?php
require_once __DIR__ . '/register.model.php';

$error = null;
$success = null;
$pharmacies = PharmacyContext::getPharmacies();

if (Request::isPost()) {
    $name = trim((string)(Request::post('name') ?? ''));
    $email = trim((string)(Request::post('email') ?? ''));
    $phone = trim((string)(Request::post('phone') ?? ''));
    $license = trim((string)(Request::post('license_no') ?? ''));
    $password = (string)(Request::post('password') ?? '');
    $confirmPassword = (string)(Request::post('confirm_password') ?? '');
    $requestedPharmacyId = (int)(Request::post('requested_pharmacy_id') ?? 0);

    $validPharmacyIds = array_map(static fn($p) => (int)($p['id'] ?? 0), $pharmacies);

    if ($name === '' || $email === '' || $license === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill all required fields.';
    } elseif ($requestedPharmacyId <= 0 || !in_array($requestedPharmacyId, $validPharmacyIds, true)) {
        $error = 'Please select a valid pharmacy location.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (PharmacistRegisterModel::existsInSystem($email, $license)) {
        $error = 'A pharmacist account/request already exists with this email or license number.';
    } else {
        $requestId = PharmacistRegisterModel::createRequest([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'license_no' => $license,
            'password' => $password,
            'requested_pharmacy_id' => $requestedPharmacyId,
        ]);
        if ($requestId > 0) {
            $_SESSION['pharmacist_request_id'] = $requestId;
            Response::redirect('/pharmacist/pending-approval');
        } else {
            $error = 'Unable to submit request. Please try again.';
        }
    }
}
