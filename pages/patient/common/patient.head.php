<?php

/**
 * Patient head guard — include at the top of every patient page index.php.
 *
 * 1. Verifies the JWT cookie exists and is valid
 * 2. Confirms the role is 'patient'
 * 3. Sets $user = ['nic', 'name', 'role', 'iat', 'exp']
 * 4. Redirects to /auth/login on any failure
 */
$user = Auth::requireRole('patient');

if (PharmacyContext::pharmaciesEnabled()) {
    $path = Request::path();
    $isSelectionPath = str_starts_with($path, '/patient/pharmacy-select');

    $patientNic = (string)($user['nic'] ?? '');
    $hasSelection = PharmacyContext::patientHasSelection($patientNic);

    if (!$isSelectionPath && !$hasSelection) {
        Response::redirect('/patient/pharmacy-select');
    }
}
