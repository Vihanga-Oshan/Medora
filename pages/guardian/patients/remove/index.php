<?php
/**
 * /guardian/patients/remove — unlink patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';

if (Request::isPost()) {
    $patientNic = $_POST['nic'] ?? '';
    // Verify association first (guardianNic from JWT)
    $patient = PatientsModel::getPatientProfile($patientNic);
    if ($patient && $patient['guardian_nic'] === $user['id']) {
        PatientsModel::unlinkPatient($patientNic);
        Response::redirect('/guardian/patients?msg=unlinked');
    } else {
        Response::redirect('/guardian/patients?error=unauthorized');
    }
} else {
    Response::redirect('/guardian/patients');
}
