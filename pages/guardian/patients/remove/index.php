<?php
/**
 * /guardian/patients/remove — unlink patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';
require_once ROOT . '/core/GuardianLinkRequestSupport.php';

if (Request::isPost()) {
    $patientNic = GuardianLinkRequestSupport::normalizeNic((string)($_POST['nic'] ?? ''));
    $guardianNic = GuardianLinkRequestSupport::normalizeNic((string)($user['id'] ?? ''));
    // Verify association first (guardianNic from JWT)
    $patient = PatientsModel::getPatientProfile($patientNic);
    if ($patient && GuardianLinkRequestSupport::normalizeNic((string)($patient['guardian_nic'] ?? '')) === $guardianNic) {
        PatientsModel::unlinkPatient($patientNic);
        Response::redirect('/guardian/patients?msg=unlinked');
    } else {
        Response::redirect('/guardian/patients?error=unauthorized');
    }
} else {
    Response::redirect('/guardian/patients');
}
