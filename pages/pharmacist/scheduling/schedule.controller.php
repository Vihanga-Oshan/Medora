<?php
/**
 * Medication Scheduling Controller
 */
require_once __DIR__ . '/schedule.model.php';

$prescriptionId = (int)($_GET['id'] ?? $_POST['prescription_id'] ?? 0);
$patientNic = $_GET['nic'] ?? $_POST['patient_nic'] ?? '';

if (!$prescriptionId || !$patientNic) {
    Response::redirect('/pharmacist/dashboard');
}

$prescription = ScheduleModel::getPrescription($prescriptionId);
$prescriptionNic = (string)($prescription['patient_nic'] ?? $prescription['patientNic'] ?? '');
if (!$prescription || $prescriptionNic === '' || $prescriptionNic !== $patientNic) {
    Response::redirect('/pharmacist/dashboard');
}

// Handle form submission
if (Request::isPost() && isset($_POST['medicineId']) && is_array($_POST['medicineId'])) {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'pharmacist_schedule_submit')) {
        Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=csrf');
    }

    $rows = count($_POST['medicineId']);
    $schedules = [];
    
    // Frequencies and Dosages are labels in the DB schedules table for display parity
    // We fetch labels from lookup tables first to avoid multiple queries in loop
    $dosagesMap = array_column(ScheduleModel::getDosages(), 'label', 'id');
    $freqsMap = array_column(ScheduleModel::getFrequencies(), 'label', 'id');
    $freqTimesMap = array_column(ScheduleModel::getFrequencies(), 'times_of_day', 'id');
    $mealsMap = array_column(ScheduleModel::getMealTimings(), 'label', 'id');

    for ($i = 0; $i < $rows; $i++) {
        if (empty($_POST['medicineId'][$i]) || empty($_POST['startDate'][$i]) || empty($_POST['durationDays'][$i])) {
            continue;
        }
        $schedules[] = [
            'patient_nic'     => $patientNic,
            'prescription_id' => $prescriptionId,
            'medicine_id'     => $_POST['medicineId'][$i],
            'dosage_id'       => $_POST['dosageId'][$i] ?? null,
            'frequency_id'    => $_POST['frequencyId'][$i] ?? null,
            'meal_timing_id'  => $_POST['mealTimingId'][$i] ?? null,
            'dosage'          => $dosagesMap[$_POST['dosageId'][$i]] ?? '',
            'frequency'       => $freqsMap[$_POST['frequencyId'][$i]] ?? '',
            'times_of_day'    => $freqTimesMap[$_POST['frequencyId'][$i]] ?? '',
            'meal_timing'     => $mealsMap[$_POST['mealTimingId'][$i]] ?? '',
            'start_date'      => $_POST['startDate'][$i],
            'duration_days'   => $_POST['durationDays'][$i],
            'instructions'    => $_POST['instructions'][$i] ?? '',
        ];
    }

    if (empty($schedules)) {
        Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=empty');
    }

    $txStarted = Database::beginTransaction();
    $ok = $txStarted && ScheduleModel::bulkInsert($schedules);
    if (!$ok) {
        if ($txStarted) {
            Database::rollback();
        }
        Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=save');
    }

    if (!Database::commit()) {
        Database::rollback();
        Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=commit');
    }

    ScheduleModel::syncPatientPharmacySelection($patientNic);
    // Best-effort notification; scheduling should not fail because of a notification insert issue.
    ScheduleModel::createNotification($patientNic, "Your medication schedule has been created for prescription: " . $prescription['file_name']);
    Response::redirect('/pharmacist/dashboard?msg=schedule_created');
}

$data = [
    'medicines'    => ScheduleModel::getMedicines(),
    'dosages'      => ScheduleModel::getDosages(),
    'frequencies'  => ScheduleModel::getFrequencies(),
    'mealTimings'  => ScheduleModel::getMealTimings(),
    'prescription' => $prescription,
];
