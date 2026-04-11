<?php

/**
 * Admin Login Controller
 */
$error = null;

if (Request::isPost()) {
    $email    = trim(Request::post('email') ?? '');
    $password = Request::post('password') ?? '';

    if ($email === '' || $password === '') {
        $error = 'Admin email and password are required.';
    } else {
        require_once __DIR__ . '/login.model.php';
        $user = LoginModel::findByEmail($email);

       
        $hash = $user['password_hash'] ?? '';
        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$' . substr($hash, 4);
        }

        $isValid = false;
        if ($user !== null) {
            if (preg_match('/^\$2[aby]\$/', $hash)) {
                $isValid = password_verify($password, $hash);
            } elseif (preg_match('/^[a-f0-9]{64}$/i', $hash)) {
                // Legacy Java-style SHA-256 hex hash support.
                $isValid = hash_equals(strtolower($hash), hash('sha256', $password));
            } else {
                $isValid = hash_equals((string) $hash, (string) $password);
            }
        }

        if ($user === null || !$isValid) {
            $error = 'Invalid admin credentials or insufficient permissions.';
        } else {
            $displayName = $user['display_name'] ?: ($user['first_name'] ?: 'Admin');

            $token = Auth::sign([
                'id'   => $user['user_id'],
                'name' => $displayName,
                'role' => $user['role'],
            ]);

            Auth::setTokenCookie($token, 86400, 'admin');
            Response::redirect('/admin/dashboard');
        }
    }
}
