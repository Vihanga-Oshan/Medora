<?php
/**
 * /guardian/patients/add — link patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientNic = strtoupper(trim((string)($_POST['nic'] ?? '')));
    $patientNic = preg_replace('/[\s\-]+/', '', $patientNic) ?? $patientNic;
    $guardianNic = strtoupper(trim((string)$user['id']));
    $guardianNic = preg_replace('/[\s\-]+/', '', $guardianNic) ?? $guardianNic;

    if ($patientNic) {
        // Validation: Verify patient exists first
        $patient = PatientsModel::getPatientProfile($patientNic);
        if ($patient) {
            $existingGuardian = strtoupper(trim((string)($patient['guardian_nic'] ?? '')));
            $existingGuardian = preg_replace('/[\s\-]+/', '', $existingGuardian) ?? $existingGuardian;

            if ($existingGuardian !== '' && $existingGuardian !== $guardianNic) {
                header("Location: /guardian/patients?error=already_linked");
                exit;
            }

            if ($existingGuardian === $guardianNic) {
                header("Location: /guardian/patients?nic=$patientNic&msg=already_linked");
                exit;
            }

            PatientsModel::sendLinkRequest($patientNic, $guardianNic);
            header("Location: /guardian/patients?nic=$patientNic&msg=request_sent");
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
