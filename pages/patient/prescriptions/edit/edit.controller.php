<?php
/**
 * Edit Prescription Controller
 * Ported from: EditPatientPrescriptionServlet.java
 * GET → load prescription for editing
 * POST → save new file name
 */
$redirected   = false;
$prescription = null;
$error        = null;
$id           = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'patient_prescription_edit')) {
        $error = 'Session expired. Please refresh and try again.';
    } else {
    $newName = trim($_POST['file_name'] ?? '');
    if ($newName === '') {
        $error = 'Prescription name cannot be empty.';
    } else {
        PrescriptionsModel::updateFileName($id, $newName, $user['nic']);
        header('Location: ' . (APP_BASE ?: '') . '/patient/prescriptions');
        $redirected = true;
        exit;
    }
    }
}

if (!$redirected) {
    $prescription = PrescriptionsModel::getById($id, $user['nic']);
    if (!$prescription) {
        header('Location: ' . (APP_BASE ?: '') . '/patient/prescriptions');
        exit;
    }
}
