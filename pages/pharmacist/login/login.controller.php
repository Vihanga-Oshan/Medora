<?php

/**
 * Pharmacist Login Controller
 */
$error = null;

if (!function_exists('pharmacistLoginWriteLog')) {
    function pharmacistLoginWriteLog(string $file, string $level, string $message, array $context = []): void
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
    $pharmacistId = trim(Request::post('id') ?? '');
    $password = Request::post('password') ?? '';
    $safeId = (int)preg_replace('/\D+/', '', $pharmacistId);

    pharmacistLoginWriteLog('pharmacist-login-debug.log', 'DEBUG', 'Pharmacist login request received.', [
        'id_input' => $pharmacistId,
        'id_numeric' => $safeId,
        'password_length' => strlen($password),
    ]);

    if ($pharmacistId === '' || $password === '') {
        $error = 'Pharmacist ID and password are required.';
        pharmacistLoginWriteLog('pharmacist-login-error.log', 'ERROR', 'Validation failed: missing ID or password.', [
            'id_empty' => $pharmacistId === '' ? 1 : 0,
            'password_empty' => $password === '' ? 1 : 0,
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
        pharmacistLoginWriteLog('pharmacist-login-debug.log', 'DEBUG', 'Password verification result.', [
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
                pharmacistLoginWriteLog('pharmacist-login-debug.log', 'DEBUG', 'Pending request detected, redirecting.', [
                    'id_numeric' => $safeId,
                    'request_id' => (int)($rq['id'] ?? 0),
                ]);
                Response::redirect('/pharmacist/pending-approval');
            }
            $error = 'Invalid pharmacist credentials.';
            pharmacistLoginWriteLog('pharmacist-login-error.log', 'ERROR', 'Pharmacist login failed.', [
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

            Auth::setTokenCookie($token, 86400, 'pharmacist');
            pharmacistLoginWriteLog('pharmacist-login-debug.log', 'DEBUG', 'Pharmacist login successful.', [
                'id' => (int)$user['id'],
                'name' => $displayName,
                'pharmacy_id' => $assignedPharmacyId,
            ]);
            Response::redirect('/pharmacist/dashboard');
        }
    }
}
