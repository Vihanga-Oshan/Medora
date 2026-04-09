<?php

/**
 * Patient Login Controller
 * GET: show form, POST: authenticate NIC + password.
 */
$error = null;

if (Request::isPost()) {
    $nic = trim(Request::post('nic') ?? '');
    $password = Request::post('password') ?? '';

    if ($nic === '' || $password === '') {
        $error = 'NIC and password are required.';
    } else {
        require_once __DIR__ . '/login.model.php';
        $user = LoginModel::findByNic($nic);

        $stored = $user['password_value'] ?? '';

        $isValid = false;
        if ($user !== null) {
            if (str_starts_with($stored, '$2a$') || str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2b$')) {
                if (str_starts_with($stored, '$2a$')) {
                    $stored = '$2y$' . substr($stored, 4);
                }
                $isValid = password_verify($password, $stored);
            } elseif (preg_match('/^[a-f0-9]{64}$/i', $stored)) {
                // Legacy SHA-256 hashes in older Medora datasets.
                $isValid = hash_equals(strtolower($stored), hash('sha256', $password));
            } else {
                // Legacy plain-text fallback.
                $isValid = hash_equals((string)$stored, (string)$password);
            }
        }

        if (!$isValid) {
            $error = 'Invalid NIC or password.';
        } else {
            $displayName = trim((string)($user['patient_name'] ?? ''));
            if ($displayName === '') {
                $displayName = 'Patient';
            }

            $token = Auth::sign([
                'nic'  => $user['nic'],
                'name' => $displayName,
                'role' => 'patient',
            ]);

            Auth::setTokenCookie($token, 86400, 'patient');
            PharmacyContext::clearSelectedPharmacy();
            PharmacyContext::patientHasSelection((string)$user['nic']);
            if (PharmacyContext::selectedPharmacyId() > 0) {
                Response::redirect('/patient/dashboard');
            }
            Response::redirect('/patient/pharmacy-select');
        }
    }
}
