<?php

/**
 * /auth/logout - clears JWT cookie and redirects to role login.
 * POST only.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/auth/login');
}

$user = Auth::getUser();
$role = strtolower((string)($user['role'] ?? ''));

Auth::clearTokenCookie($role !== '' ? $role : null);
PharmacyContext::clearSelectedPharmacy();

$loginByRole = [
    'pharmacist' => '/auth/login/pharmacist',
    'patient' => '/auth/login',
    'guardian' => '/auth/login/guardian',
    'admin' => '/auth/login/admin',
];

Response::redirect($loginByRole[$role] ?? '/auth/login');
