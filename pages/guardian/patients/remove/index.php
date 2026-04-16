<?php
/**
 * /guardian/patients/remove — unlink patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';
require_once ROOT . '/core/GuardianLinkRequestSupport.php';

if (!Request::isPost()) {
    Response::redirect('/guardian/patients');
}

if (!Csrf::verify($_POST['csrf_token'] ?? null, 'guardian_patient_unlink')) {
    Response::redirect('/guardian/patients?error=csrf');
}

$patientNic = GuardianLinkRequestSupport::normalizeNic((string)($_POST['nic'] ?? ''));
$guardianNic = GuardianLinkRequestSupport::normalizeNic((string)($user['id'] ?? ''));
$patient = PatientsModel::getPatientProfile($patientNic);

if (!$patient || GuardianLinkRequestSupport::normalizeNic((string)($patient['guardian_nic'] ?? '')) !== $guardianNic) {
    Response::redirect('/guardian/patients?error=unauthorized');
}

if (!PatientsModel::unlinkPatient($patientNic)) {
    Response::redirect('/guardian/patients?error=unlink_failed');
}

Response::redirect('/guardian/patients?msg=unlinked');
