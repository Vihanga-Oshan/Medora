<?php
/**
 * Guardian Patient Monitoring Controller
 */
require_once __DIR__ . '/patients.model.php';

$guardianNic = $user['id'];
$patients = PatientsModel::getLinkedPatients($guardianNic);

$selectedNic = $_GET['nic'] ?? ($patients[0]['nic'] ?? '');
$selectedPatient = null;
$medications = [];

if ($selectedNic) {
    // Basic verification: ensure the patient is actually linked to this guardian
    foreach ($patients as $p) {
        if ($p['nic'] === $selectedNic) {
            $selectedPatient = $p;
            break;
        }
    }
    
    if ($selectedPatient) {
        $medications = PatientsModel::getScheduleByDate($selectedNic, date('Y-m-d'));
    }
}

$data = [
    'patients'        => $patients,
    'selectedPatient' => $selectedPatient,
    'medications'     => $medications,
];
