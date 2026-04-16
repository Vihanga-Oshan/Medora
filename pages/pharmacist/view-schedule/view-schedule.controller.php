<?php

require_once __DIR__ . '/view-schedule.model.php';

$patientNic = trim((string) ($_GET['nic'] ?? ''));
$selectedDate = trim((string) ($_GET['date'] ?? date('Y-m-d')));
if ($patientNic === '') {
    Response::redirect('/pharmacist/patients');
}

$patient = PharmacistViewScheduleModel::getPatient($patientNic);
if (!$patient) {
    Response::redirect('/pharmacist/patients?error=patient_not_found');
}

$schedules = PharmacistViewScheduleModel::getSchedulesByDate($patientNic, $selectedDate);

$data = [
    'patient' => $patient,
    'schedules' => $schedules,
    'selectedDate' => $selectedDate,
];
