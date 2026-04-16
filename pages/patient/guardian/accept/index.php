<?php
/**
 * /patient/guardian/accept
 * Accept a pending guardian link request for the logged-in patient.
 */
require_once __DIR__ . '/../../common/patient.head.php';
require_once ROOT . '/core/GuardianLinkRequestSupport.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/patient/dashboard');
}

$patientNic = GuardianLinkRequestSupport::normalizeNic((string)($user['nic'] ?? ''));
$guardianNic = GuardianLinkRequestSupport::normalizeNic((string)($_POST['guardian_nic'] ?? ''));

if ($patientNic === '' || $guardianNic === '') {
    Response::redirect('/patient/dashboard?error=invalid_request');
}

$pending = GuardianLinkRequestSupport::latestPendingForPair($patientNic, $guardianNic);

if (!$pending) {
    Response::redirect('/patient/dashboard?error=request_not_found');
}

$requestId = (int)($pending['id'] ?? 0);
GuardianLinkRequestSupport::updateStatus($requestId, 'ACCEPTED');

Database::execute(
    "UPDATE patient SET guardian_nic = ? WHERE nic = ?",
    'ss',
    [$guardianNic, $patientNic]
);

$patient = Database::fetchOne("SELECT name FROM patient WHERE nic = ? LIMIT 1", 's', [$patientNic]);
$patientName = trim((string)($patient['name'] ?? 'Patient'));
$message = $patientName . " accepted your guardian link request.";

if (PharmacyContext::tableHasPharmacyId('notifications') && PharmacyContext::selectedPharmacyId() > 0) {
    Database::execute(
        "INSERT INTO notifications (patient_nic, message, type, is_read, created_at, pharmacy_id)
         VALUES (?, ?, 'GUARDIAN_LINK', 0, NOW(), ?)",
        'ssi',
        [$patientNic, $message, PharmacyContext::selectedPharmacyId()]
    );
} else {
    Database::execute(
        "INSERT INTO notifications (patient_nic, message, type, is_read, created_at)
         VALUES (?, ?, 'GUARDIAN_LINK', 0, NOW())",
        'ss',
        [$patientNic, $message]
    );
}

Response::redirect('/patient/dashboard?msg=guardian_accepted');
