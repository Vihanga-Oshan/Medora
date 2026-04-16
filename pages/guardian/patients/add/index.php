<?php
/**
 * /guardian/patients/add — link patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';

if (!Request::isPost()) {
    header('Location: ' . (APP_BASE ?: '') . '/guardian/patients');
    exit;
}

if (!Csrf::verify($_POST['csrf_token'] ?? null, 'guardian_patient_link')) {
    header('Location: ' . (APP_BASE ?: '') . '/guardian/patients?error=csrf&modal=add');
    exit;
}

$base = APP_BASE ?: '';
$patientNic = GuardianLinkRequestSupport::normalizeNic((string)($_POST['nic'] ?? ''));
$guardianNic = GuardianLinkRequestSupport::normalizeNic((string)($user['id'] ?? ''));

if ($patientNic === '') {
    header('Location: ' . $base . '/guardian/patients?error=empty&modal=add');
    exit;
}

$patient = PatientsModel::getPatientProfile($patientNic);
if (!$patient) {
    header('Location: ' . $base . '/guardian/patients?error=not_found&modal=add');
    exit;
}

$linkedGuardianNic = GuardianLinkRequestSupport::normalizeNic((string)($patient['guardian_nic'] ?? ''));
if ($linkedGuardianNic !== '' && $linkedGuardianNic !== $guardianNic) {
    header('Location: ' . $base . '/guardian/patients?error=already_linked&modal=add');
    exit;
}

if ($linkedGuardianNic === $guardianNic) {
    header('Location: ' . $base . '/guardian/patients?nic=' . urlencode($patientNic) . '&msg=already_linked');
    exit;
}

if (!PatientsModel::sendLinkRequest($patientNic, $guardianNic)) {
    header('Location: ' . $base . '/guardian/patients?error=link_failed&modal=add');
    exit;
}

header('Location: ' . $base . '/guardian/patients?nic=' . urlencode($patientNic) . '&msg=request_sent');
exit;
