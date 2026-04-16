<?php
/**
 * Edit Prescription Controller
 * Ported from: EditPatientPrescriptionServlet.java
 * GET → load prescription for editing
 * POST → save new file name
 */
$prescription = null;
$error        = null;
$id           = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if (Request::isPost()) {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'patient_prescription_edit')) {
        $error = 'Session expired. Please refresh and try again.';
    } else {
        $newName = trim($_POST['file_name'] ?? '');
        if ($newName === '') {
            $error = 'Prescription name cannot be empty.';
        } else {
            PrescriptionsModel::updateFileName($id, $newName, $user['nic']);
            Response::redirect('/patient/prescriptions');
        }
    }
}

$prescription = PrescriptionsModel::getById($id, $user['nic']);
if (!$prescription) {
    Response::redirect('/patient/prescriptions');
}
