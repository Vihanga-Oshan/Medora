<?php
/**
 * /patient/guardian/reject
 * Reject a pending guardian link request for the logged-in patient.
 */
require_once __DIR__ . '/../../common/patient.head.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/patient/dashboard');
}

$patientNic = strtoupper(trim((string)($user['nic'] ?? '')));
$patientNic = preg_replace('/[\s\-]+/', '', $patientNic) ?? $patientNic;
$guardianNic = strtoupper(trim((string)($_POST['guardian_nic'] ?? '')));
$guardianNic = preg_replace('/[\s\-]+/', '', $guardianNic) ?? $guardianNic;

if ($patientNic === '' || $guardianNic === '') {
    Response::redirect('/patient/dashboard?error=invalid_request');
}

Database::iud("
    CREATE TABLE IF NOT EXISTS guardian_link_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        guardian_nic VARCHAR(45) NOT NULL,
        patient_nic VARCHAR(20) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
        guardian_seen TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        responded_at TIMESTAMP NULL DEFAULT NULL,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_guardian_status (guardian_nic, status, responded_at),
        INDEX idx_patient_status (patient_nic, status, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pending = Database::fetchOne(
    "SELECT id
     FROM guardian_link_requests
     WHERE REPLACE(REPLACE(UPPER(patient_nic), ' ', ''), '-', '') = ?
       AND REPLACE(REPLACE(UPPER(guardian_nic), ' ', ''), '-', '') = ?
       AND UPPER(status) = 'PENDING'
     ORDER BY id DESC
     LIMIT 1",
    'ss',
    [$patientNic, $guardianNic]
);

if (!$pending) {
    Response::redirect('/patient/dashboard?error=request_not_found');
}

$requestId = (int)($pending['id'] ?? 0);
Database::execute(
    "UPDATE guardian_link_requests
     SET status = 'DECLINED', guardian_seen = 0, responded_at = NOW(), updated_at = NOW()
     WHERE id = ?",
    'i',
    [$requestId]
);

$patient = Database::fetchOne("SELECT name FROM patient WHERE nic = ? LIMIT 1", 's', [$patientNic]);
$patientName = trim((string)($patient['name'] ?? 'Patient'));
$message = $patientName . " declined your guardian link request.";

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

Response::redirect('/patient/dashboard?msg=guardian_rejected');
