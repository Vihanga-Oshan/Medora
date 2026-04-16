<?php

/**
 * Admin Login Controller
 */
require_once ROOT . '/core/InputValidator.php';

$error = null;

if (Request::isPost()) {
    $email    = InputValidator::normalizeEmail((string) (Request::post('email') ?? ''));
    $password = Request::post('password') ?? '';
    $rememberMe = InputValidator::isTruthyRememberMe(Request::post('rememberMe'));

    if ($email === '' || $password === '') {
        $error = 'Admin email and password are required.';
    } elseif (!InputValidator::isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
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
                'email' => (string)($user['email'] ?? ''),
                'role' => $user['role'],
            ]);

            Auth::setTokenCookie($token, $rememberMe ? 2592000 : 0, 'admin');
            Response::redirect('/admin/dashboard');
        }
    }
}
