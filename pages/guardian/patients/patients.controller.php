<?php
/**
 * Guardian Patient Monitoring Controller
 */
require_once __DIR__ . '/patients.model.php';
require_once ROOT . '/core/GuardianLinkRequestSupport.php';

$guardianNic = GuardianLinkRequestSupport::normalizeNic((string)($user['id'] ?? ''));
$patients = PatientsModel::getLinkedPatients($guardianNic);
$selectedDate = date('Y-m-d');

$selectedNic = GuardianLinkRequestSupport::normalizeNic((string)($_GET['nic'] ?? ($patients[0]['nic'] ?? '')));
$selectedPatient = null;
$medications = [];
$summary = [
    'linkedCount' => count($patients),
    'selectedTotal' => 0,
    'selectedTaken' => 0,
    'selectedPending' => 0,
    'selectedMissed' => 0,
];

$flashMap = [
    'linked' => ['type' => 'success', 'message' => 'Patient linked successfully.'],
    'request_sent' => ['type' => 'success', 'message' => 'Link request sent to the patient successfully.'],
    'already_linked' => ['type' => 'success', 'message' => 'This patient is already linked to your guardian account.'],
    'unlinked' => ['type' => 'success', 'message' => 'Patient removed from your linked list.'],
];

$errorMap = [
    'empty' => 'Enter a patient NIC to continue.',
    'not_found' => 'No patient was found for that NIC.',
    'already_linked' => 'This patient is already linked to another guardian.',
    'already_linked_other' => 'This patient is already linked to another guardian.',
    'unauthorized' => 'You can only manage patients linked to your account.',
    'link_failed' => 'We could not link that patient right now.',
    'unlink_failed' => 'We could not remove that patient right now.',
];

$flash = null;
if (isset($_GET['msg'], $flashMap[$_GET['msg']])) {
    $flash = $flashMap[$_GET['msg']];
} elseif (isset($_GET['error'], $errorMap[$_GET['error']])) {
    $flash = ['type' => 'error', 'message' => $errorMap[$_GET['error']]];
}

if ($selectedNic) {
    foreach ($patients as $p) {
        if ($p['nic'] === $selectedNic) {
            $selectedPatient = $p;
            break;
        }
    }

    if (!$selectedPatient && !empty($patients)) {
        $selectedPatient = $patients[0];
        $selectedNic = $selectedPatient['nic'];
    }

    if ($selectedPatient) {
        $medications = PatientsModel::getScheduleByDate($selectedNic, $selectedDate);
        $selectedSummary = PatientsModel::getTodayMedicationSummary($selectedNic, $selectedDate);
        $summary['selectedTotal'] = $selectedSummary['total'];
        $summary['selectedTaken'] = $selectedSummary['taken'];
        $summary['selectedPending'] = $selectedSummary['pending'];
        $summary['selectedMissed'] = $selectedSummary['missed'];
    }
}

$data = [
    'patients'        => $patients,
    'selectedPatient' => $selectedPatient,
    'medications'     => $medications,
    'summary'         => $summary,
    'selectedDate'    => $selectedDate,
    'flash'           => $flash,
];
