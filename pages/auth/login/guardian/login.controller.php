<?php

/**
 * Guardian Login Controller
 */
$error = null;

if (Request::isPost()) {
    $nic = trim(Request::post('nic') ?? '');
    $password = Request::post('password') ?? '';

    if ($nic === '' || $password === '') {
        $error = 'NIC and password are required.';
    } else {
        require_once __DIR__ . '/login.model.php';
        $guardian = LoginModel::findByNic($nic);

        $stored = $guardian['password_value'] ?? '';

        $valid = false;
        if ($guardian !== null) {
            if (str_starts_with($stored, '$2a$') || str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2b$')) {
                if (str_starts_with($stored, '$2a$')) {
                    $stored = '$2y$' . substr($stored, 4);
                }
                $valid = password_verify($password, $stored);
            } elseif (preg_match('/^[a-f0-9]{64}$/i', $stored)) {
                // Legacy SHA-256 hashes in older Medora datasets.
                $valid = hash_equals(strtolower($stored), hash('sha256', $password));
            } else {
                // Legacy plain-text fallback.
                $valid = hash_equals((string)$stored, (string)$password);
            }
        }

        if (!$valid) {
            $error = 'Invalid NIC or password.';
        } else {
            $displayName = trim((string)($guardian['guardian_name'] ?? ''));
            if ($displayName === '') {
                $displayName = 'Guardian';
            }

            $token = Auth::sign([
                'id' => $guardian['nic'],
                'name' => $displayName,
                'role' => 'guardian',
            ]);

            Auth::setTokenCookie($token, 86400, 'guardian');
            Response::redirect('/guardian/dashboard');
        }
    }
}
