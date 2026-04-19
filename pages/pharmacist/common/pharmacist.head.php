<?php

/**
 * Pharmacist head guard.
 * Verifies the pharmacist JWT and hydrates the active pharmacist profile.
 */
$authUser = Auth::requireRole('pharmacist');

$pharmacistId = (int) ($authUser['id'] ?? 0);

$table = 'pharmacist';
$pharmacist = Database::fetchOne(
    "SELECT id, name, email, '' AS phone, CAST(id AS CHAR) AS license_no, 'pharmacist' AS role, 'ACTIVE' AS status
     FROM pharmacist
     WHERE id = ?
     LIMIT 1",
    'i',
    [$pharmacistId]
);
if (!$pharmacist) {
    Auth::clearTokenCookie('pharmacist');
    Response::redirect('/pharmacist/login');
}

$displayName = $pharmacist['name']
    ?: ($authUser['name'] ?? 'Pharmacist');

$user = [
    'id' => (int) $pharmacist['id'],
    'name' => $displayName,
    'role' => $pharmacist['role'] ?? 'pharmacist',
    'email' => $pharmacist['email'] ?? '',
    'phone' => $pharmacist['phone'] ?? '',
    'licenseNo' => $pharmacist['license_no'] ?? '',
    'status' => $pharmacist['status'] ?? 'ACTIVE',
    'displayName' => $displayName,
    'profilePictureUrl' => (APP_BASE ?: '') . '/assets/img/avatar.png',
];

$currentPharmacyId = PharmacyContext::resolvePharmacistPharmacyId((int) $user['id']);
if ($currentPharmacyId <= 0) {
    $currentPharmacyId = (int) ($authUser['pharmacy_id'] ?? 0);
}
if ($currentPharmacyId <= 0) {
    Auth::clearTokenCookie('pharmacist');
    Response::redirect('/pharmacist/login');
}
$currentPharmacy = PharmacyContext::pharmacyById($currentPharmacyId);

$currentPharmacist = $user;
