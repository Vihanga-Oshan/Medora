<?php
require_once __DIR__ . '/register.model.php';
require_once ROOT . '/core/InputValidator.php';
require_once ROOT . '/core/AppLogger.php';

$error = null;
$success = null;
$pharmacies = PharmacyContext::getPharmacies();

if (Request::isPost()) {
    $name = trim((string)(Request::post('name') ?? ''));
    $email = InputValidator::normalizeEmail((string)(Request::post('email') ?? ''));
    $phone = trim((string)(Request::post('phone') ?? ''));
    $licenseRaw = trim((string)(Request::post('license_no') ?? ''));
    $license = PharmacistRegisterModel::normalizeLicenseDigits($licenseRaw);
    $password = (string)(Request::post('password') ?? '');
    $confirmPassword = (string)(Request::post('confirm_password') ?? '');
    $requestedPharmacyId = (int)(Request::post('requested_pharmacy_id') ?? 0);

    $validPharmacyIds = array_map(static fn($p) => (int)($p['id'] ?? 0), $pharmacies);
    AppLogger::write('pharmacist-register-debug.log', 'DEBUG', 'Pharmacist register request received.', [
        'email' => $email,
        'license' => $license,
        'requested_pharmacy_id' => $requestedPharmacyId,
    ]);

    if ($name === '' || $email === '' || $licenseRaw === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill all required fields.';
    } elseif (!InputValidator::isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($phone !== '' && !InputValidator::isValidPhone($phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (!InputValidator::isValidLicenseDigits($license)) {
        $error = 'License number must be exactly 4 digits.';
    } elseif ($requestedPharmacyId <= 0 || !in_array($requestedPharmacyId, $validPharmacyIds, true)) {
        $error = 'Please select a valid pharmacy location.';
    } elseif (($passwordError = InputValidator::passwordError($password)) !== null) {
        $error = $passwordError;
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (PharmacistRegisterModel::existsInSystem($email, $license)) {
        $error = 'An account or request already exists for this email/license. If approved, log in using your Pharmacist ID (license digits, e.g. 7777).';
        AppLogger::write('pharmacist-register-error.log', 'ERROR', 'Duplicate pharmacist register attempt blocked.', [
            'email' => $email,
            'license' => $license,
        ]);
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
            AppLogger::write('pharmacist-register-debug.log', 'DEBUG', 'Pharmacist request created.', [
                'request_id' => $requestId,
                'email' => $email,
                'license' => $license,
            ]);
            Response::redirect('/pharmacist/pending-approval');
        } else {
            $error = 'Unable to submit request. Please try again.';
            AppLogger::write('pharmacist-register-error.log', 'ERROR', 'Pharmacist request creation failed.', [
                'email' => $email,
                'license' => $license,
            ]);
        }
    }
}
