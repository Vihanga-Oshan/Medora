<?php

/**
 * Pharmacist Login Controller
 */
$error = null;

if (Request::isPost()) {
    $pharmacistId = trim(Request::post('id') ?? '');
    $password = Request::post('password') ?? '';

    if ($pharmacistId === '' || $password === '') {
        $error = 'Pharmacist ID and password are required.';
    } else {
        require_once __DIR__ . '/login.model.php';
        $user = LoginModel::findById($pharmacistId);

        // Normalize Java BCrypt prefix ($2a$ -> $2y$)
        $hash = $user['password'] ?? '';
        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        if ($user === null || !password_verify($password, $hash)) {
            if (LoginModel::hasPendingRequest($pharmacistId)) {
                $safeId = (int)preg_replace('/\D+/', '', $pharmacistId);
                $rq = Database::fetchOne(
                    "SELECT id FROM pharmacist_requests WHERE license_no = ? AND status = 'pending' ORDER BY id DESC LIMIT 1",
                    's',
                    [(string)$safeId]
                );
                if ($rq) {
                    $_SESSION['pharmacist_request_id'] = (int)($rq['id'] ?? 0);
                }
                Response::redirect('/pharmacist/pending-approval');
            }
            $error = 'Invalid pharmacist credentials.';
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
            Response::redirect('/pharmacist/dashboard');
        }
    }
}
