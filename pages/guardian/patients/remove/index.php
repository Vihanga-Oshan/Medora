<?php
/**
 * /guardian/patients/remove — unlink patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientNic = $_POST['nic'] ?? '';
    // Verify association first (guardianNic from JWT)
    $patient = PatientsModel::getPatientProfile($patientNic);
    if ($patient && $patient['guardian_nic'] === $user['id']) {
        PatientsModel::unlinkPatient($patientNic);
        header("Location: /guardian/patients?msg=unlinked");
    } else {
        header("Location: /guardian/patients?error=unauthorized");
    }
} else {
    header("Location: /guardian/patients");
}
exit;
