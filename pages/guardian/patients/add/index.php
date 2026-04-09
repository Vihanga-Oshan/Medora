<?php
/**
 * /guardian/patients/add — link patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientNic = $_POST['nic'] ?? '';
    $guardianNic = $user['id'];

    if ($patientNic) {
        // Validation: Verify patient exists first
        $patient = PatientsModel::getPatientProfile($patientNic);
        if ($patient) {
            PatientsModel::linkPatient($patientNic, $guardianNic);
            header("Location: /guardian/patients?nic=$patientNic&msg=linked");
        } else {
            header("Location: /guardian/patients?error=not_found");
        }
    } else {
        header("Location: /guardian/patients?error=empty");
    }
} else {
    header("Location: /guardian/patients");
}
exit;
