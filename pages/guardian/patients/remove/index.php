<?php
/**
 * /guardian/patients/remove — unlink patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';
require_once ROOT . '/core/GuardianLinkRequestSupport.php';

if (!Request::isPost()) {
    header('Location: ' . (APP_BASE ?: '') . '/guardian/patients');
    exit;
}

if (!Csrf::verify($_POST['csrf_token'] ?? null, 'guardian_patient_unlink')) {
    header('Location: ' . (APP_BASE ?: '') . '/guardian/patients?error=csrf');
    exit;
}

$base = APP_BASE ?: '';
$patientNic = GuardianLinkRequestSupport::normalizeNic((string)($_POST['nic'] ?? ''));
$guardianNic = GuardianLinkRequestSupport::normalizeNic((string)($user['id'] ?? ''));
$patient = PatientsModel::getPatientProfile($patientNic);

if (!$patient || GuardianLinkRequestSupport::normalizeNic((string)($patient['guardian_nic'] ?? '')) !== $guardianNic) {
    header('Location: ' . $base . '/guardian/patients?error=unauthorized');
    exit;
}

if (!PatientsModel::unlinkPatient($patientNic)) {
    header('Location: ' . $base . '/guardian/patients?error=unlink_failed');
    exit;
}

header('Location: ' . $base . '/guardian/patients?msg=unlinked');
exit;
