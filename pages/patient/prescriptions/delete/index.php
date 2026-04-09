<?php
/**
 * Delete Prescription
 * Ported from: DeletePrescriptionServlet.java
 * POST only — deletes prescription record and file, redirects back to list.
 */
require_once __DIR__ . '/../../common/patient.head.php';
require_once __DIR__ . '/../../prescriptions/prescriptions.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . (APP_BASE ?: '') . '/patient/prescriptions');
    exit;
}

if (!Csrf::verify($_POST['csrf_token'] ?? null, 'patient_prescription_delete')) {
    header('Location: ' . (APP_BASE ?: '') . '/patient/prescriptions?error=csrf');
    exit;
}

$id      = (int)($_POST['id'] ?? 0);
$filePath = PrescriptionsModel::delete($id, $user['nic']);

if ($filePath) {
    $safeFile = basename((string)$filePath);
    $privatePath = ROOT . '/storage/prescriptions/' . $safeFile;
    $legacyPath = ROOT . '/public/uploads/prescriptions/' . $safeFile;

    if (is_file($privatePath)) {
        unlink($privatePath);
    } elseif (is_file($legacyPath)) {
        unlink($legacyPath);
    }
}

header('Location: ' . (APP_BASE ?: '') . '/patient/prescriptions');
exit;
