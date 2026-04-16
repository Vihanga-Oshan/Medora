<?php

/**
 * Pharmacist Login Controller
 */
require_once ROOT . '/core/InputValidator.php';
require_once ROOT . '/core/AppLogger.php';

$error = null;

if (Request::isPost()) {
    $pharmacistId = trim(Request::post('id') ?? '');
    $password = Request::post('password') ?? '';
    $rememberMe = InputValidator::isTruthyRememberMe(Request::post('rememberMe'));
    $safeId = (int)preg_replace('/\D+/', '', $pharmacistId);

    AppLogger::write('pharmacist-login-debug.log', 'DEBUG', 'Pharmacist login request received.', [
        'id_input' => $pharmacistId,
        'id_numeric' => $safeId,
        'password_length' => strlen($password),
    ]);

    if ($pharmacistId === '' || $password === '') {
        $error = 'Pharmacist ID and password are required.';
        AppLogger::write('pharmacist-login-error.log', 'ERROR', 'Validation failed: missing ID or password.', [
            'id_empty' => $pharmacistId === '' ? 1 : 0,
            'password_empty' => $password === '' ? 1 : 0,
        ]);
    } elseif (!InputValidator::isValidLicenseDigits((string)$safeId)) {
        $error = 'Pharmacist ID must be exactly 4 digits.';
        AppLogger::write('pharmacist-login-error.log', 'ERROR', 'Validation failed: invalid pharmacist ID format.', [
            'id_input' => $pharmacistId,
            'id_numeric' => $safeId,
        ]);
    } else {
        require_once __DIR__ . '/login.model.php';
        $user = LoginModel::findById($pharmacistId);

        // Normalize Java BCrypt prefix ($2a$ -> $2y$)
        $hash = $user['password'] ?? '';
        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        $verified = $user !== null && password_verify($password, $hash);
        AppLogger::write('pharmacist-login-debug.log', 'DEBUG', 'Password verification result.', [
            'id_numeric' => $safeId,
            'user_found' => $user !== null ? 1 : 0,
            'hash_prefix' => substr((string)$hash, 0, 4),
            'verified' => $verified ? 1 : 0,
        ]);

        if (!$verified) {
            if (LoginModel::hasPendingRequest($pharmacistId)) {
                $rq = Database::fetchOne(
                    "SELECT id FROM pharmacist_requests WHERE license_no = ? AND status = 'pending' ORDER BY id DESC LIMIT 1",
                    's',
                    [(string)$safeId]
                );
                if ($rq) {
                    $_SESSION['pharmacist_request_id'] = (int)($rq['id'] ?? 0);
                }
                AppLogger::write('pharmacist-login-debug.log', 'DEBUG', 'Pending request detected, redirecting.', [
                    'id_numeric' => $safeId,
                    'request_id' => (int)($rq['id'] ?? 0),
                ]);
                Response::redirect('/pharmacist/pending-approval');
            }
            $error = 'Invalid pharmacist credentials.';
            AppLogger::write('pharmacist-login-error.log', 'ERROR', 'Pharmacist login failed.', [
                'id_numeric' => $safeId,
                'reason' => $user === null ? 'user_not_found' : 'password_mismatch',
            ]);
        } else {
            $displayName = $user['name'] ?: 'Pharmacist';
            $assignedPharmacyId = PharmacyContext::resolvePharmacistPharmacyId((int)$user['id']);

            $token = Auth::sign([
                'id'   => $user['id'],
                'name' => $displayName,
                'role' => $user['role'],
                'pharmacy_id' => $assignedPharmacyId,
            ]);

            Auth::setTokenCookie($token, $rememberMe ? 2592000 : 0, 'pharmacist');
            AppLogger::write('pharmacist-login-debug.log', 'DEBUG', 'Pharmacist login successful.', [
                'id' => (int)$user['id'],
                'name' => $displayName,
                'pharmacy_id' => $assignedPharmacyId,
            ]);
            Response::redirect('/pharmacist/dashboard');
        }
    }
}
