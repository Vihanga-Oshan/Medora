<?php

/**
 * Pharmacist head guard.
 * Verifies the pharmacist JWT and hydrates the active pharmacist profile.
 */
$authUser = Auth::requireRole('pharmacist');

Database::setUpConnection();
$pharmacistId = (int) ($authUser['id'] ?? 0);

// Resolve table name across schemas.
$table = 'pharmacists';
$plural = Database::search("SHOW TABLES LIKE 'pharmacists'");
if (!($plural instanceof mysqli_result) || $plural->num_rows === 0) {
    $singular = Database::search("SHOW TABLES LIKE 'pharmacist'");
    if ($singular instanceof mysqli_result && $singular->num_rows > 0) {
        $table = 'pharmacist';
    }
}

// Check optional columns.
$hasRole = false;
$hasStatus = false;
$hasPhone = false;
$hasLicenseNo = false;

$columnsRs = Database::search("SHOW COLUMNS FROM `$table`");
if ($columnsRs instanceof mysqli_result) {
    while ($col = $columnsRs->fetch_assoc()) {
        $field = strtolower((string)($col['Field'] ?? ''));
        if ($field === 'role') $hasRole = true;
        if ($field === 'status') $hasStatus = true;
        if ($field === 'phone') $hasPhone = true;
        if ($field === 'license_no') $hasLicenseNo = true;
    }
}

$selectPhone = $hasPhone ? 'phone' : "'' AS phone";
$selectLicense = $hasLicenseNo ? 'license_no' : "CAST(id AS CHAR) AS license_no";
$selectRole = $hasRole ? 'role' : "'pharmacist' AS role";
$selectStatus = $hasStatus ? 'status' : "'ACTIVE' AS status";

$where = ["id = $pharmacistId"];
if ($hasRole) {
    $where[] = "role = 'pharmacist'";
}
if ($hasStatus) {
    $where[] = "status = 'ACTIVE'";
}

$rs = Database::search(
    "SELECT id, name, email, $selectPhone, $selectLicense, $selectRole, $selectStatus
     FROM `$table`
     WHERE " . implode(' AND ', $where) . "
     LIMIT 1"
);

$pharmacist = $rs ? $rs->fetch_assoc() : null;
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

$currentPharmacyId = (int)($authUser['pharmacy_id'] ?? 0);
if ($currentPharmacyId <= 0) {
    $currentPharmacyId = PharmacyContext::resolvePharmacistPharmacyId((int)$user['id']);
}
if ($currentPharmacyId <= 0 && PharmacyContext::pharmaciesEnabled()) {
    Auth::clearTokenCookie('pharmacist');
    Response::redirect('/pharmacist/login');
}
$currentPharmacy = PharmacyContext::pharmacyById($currentPharmacyId);

$currentPharmacist = $user;
$currentCounselor = $user;
