<?php
/**
 * /guardian/patients/add — link patient handler
 */
require_once __DIR__ . '/../../common/guardian.head.php';
require_once __DIR__ . '/../patients.model.php';

if (!Request::isPost()) {
    Response::redirect('/guardian/patients');
}

$patientNic = GuardianLinkRequestSupport::normalizeNic((string)($_POST['nic'] ?? ''));
$guardianNic = GuardianLinkRequestSupport::normalizeNic((string)($user['id'] ?? ''));

if ($patientNic === '') {
    Response::redirect('/guardian/patients?error=empty&modal=add');
}

$patient = PatientsModel::getPatientProfile($patientNic);
if (!$patient) {
    Response::redirect('/guardian/patients?error=not_found&modal=add');
}

$linkedGuardianNic = GuardianLinkRequestSupport::normalizeNic((string)($patient['guardian_nic'] ?? ''));
if ($linkedGuardianNic !== '' && $linkedGuardianNic !== $guardianNic) {
    Response::redirect('/guardian/patients?error=already_linked&modal=add');
}

if ($linkedGuardianNic === $guardianNic) {
    Response::redirect('/guardian/patients?nic=' . urlencode($patientNic) . '&msg=already_linked');
}

if (!PatientsModel::sendLinkRequest($patientNic, $guardianNic)) {
    Response::redirect('/guardian/patients?error=link_failed&modal=add');
}

Response::redirect('/guardian/patients?nic=' . urlencode($patientNic) . '&msg=request_sent');
