<?php
require_once __DIR__ . '/register.model.php';

$error = null;
$success = null;
$pharmacies = PharmacyContext::getPharmacies();

if (!function_exists('pharmacistRegisterWriteLog')) {
    function pharmacistRegisterWriteLog(string $file, string $level, string $message, array $context = []): void
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 3);
        $logDir = $rootDir . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($context, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        @file_put_contents($logDir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }
}

if (Request::isPost()) {
    $name = trim((string)(Request::post('name') ?? ''));
    $email = trim((string)(Request::post('email') ?? ''));
    $phone = trim((string)(Request::post('phone') ?? ''));
    $licenseRaw = trim((string)(Request::post('license_no') ?? ''));
    $license = PharmacistRegisterModel::normalizeLicenseDigits($licenseRaw);
    $password = (string)(Request::post('password') ?? '');
    $confirmPassword = (string)(Request::post('confirm_password') ?? '');
    $requestedPharmacyId = (int)(Request::post('requested_pharmacy_id') ?? 0);

    $validPharmacyIds = array_map(static fn($p) => (int)($p['id'] ?? 0), $pharmacies);
    pharmacistRegisterWriteLog('pharmacist-register-debug.log', 'DEBUG', 'Pharmacist register request received.', [
        'email' => $email,
        'license' => $license,
        'requested_pharmacy_id' => $requestedPharmacyId,
    ]);

    if ($name === '' || $email === '' || $licenseRaw === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill all required fields.';
    } elseif ($license === '') {
        $error = 'License number must be exactly 4 digits.';
    } elseif ($requestedPharmacyId <= 0 || !in_array($requestedPharmacyId, $validPharmacyIds, true)) {
        $error = 'Please select a valid pharmacy location.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (PharmacistRegisterModel::existsInSystem($email, $license)) {
        $error = 'An account or request already exists for this email/license. If approved, log in using your Pharmacist ID (license digits, e.g. 7777).';
        pharmacistRegisterWriteLog('pharmacist-register-error.log', 'ERROR', 'Duplicate pharmacist register attempt blocked.', [
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
            pharmacistRegisterWriteLog('pharmacist-register-debug.log', 'DEBUG', 'Pharmacist request created.', [
                'request_id' => $requestId,
                'email' => $email,
                'license' => $license,
            ]);
            Response::redirect('/pharmacist/pending-approval');
        } else {
            $error = 'Unable to submit request. Please try again.';
            pharmacistRegisterWriteLog('pharmacist-register-error.log', 'ERROR', 'Pharmacist request creation failed.', [
                'email' => $email,
                'license' => $license,
            ]);
        }
    }
}
