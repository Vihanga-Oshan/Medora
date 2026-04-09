<?php
/**
 * Medications Controller — timetable view by date.
 * Ported from: PatientMedicationTimetableServlet.java
 */
require_once __DIR__ . '/medications.model.php';

$nic          = $user['nic'];
$today        = date('Y-m-d');
$selectedDate = $_GET['date'] ?? $today;

$medications = MedicationsModel::getByDate($nic, $selectedDate);

$data = [
    'medications'  => $medications,
    'selectedDate' => $selectedDate,
    'today'        => $today,
];
