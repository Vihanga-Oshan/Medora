<?php

require_once ROOT . '/pages/pharmacist/scheduling/schedule.model.php';
require_once __DIR__ . '/view-schedule.model.php';

$patientNic = trim((string) ($_REQUEST['nic'] ?? ''));
$selectedDate = trim((string) ($_REQUEST['date'] ?? date('Y-m-d')));

if ($patientNic === '') {
    Response::redirect('/pharmacist/patients');
}

if (Request::isPost()) {
    $action = trim((string) ($_POST['schedule_action'] ?? ''));
    $scheduleId = (int) ($_POST['schedule_id'] ?? 0);

    $redirectBase = '/pharmacist/view-schedule?nic=' . urlencode($patientNic) . '&date=' . urlencode($selectedDate);

    if ($scheduleId <= 0) {
        Response::redirect($redirectBase . '&error=invalid_schedule');
    }

    if ($action === 'delete') {
        $ok = PharmacistViewScheduleModel::softDeleteSchedule($scheduleId, $patientNic);
        Response::redirect($redirectBase . ($ok ? '&success=schedule_deleted' : '&error=schedule_delete_failed'));
    }

    if ($action === 'edit') {
        $payload = [
            'medicine_id' => (int) ($_POST['medicine_id'] ?? 0),
            'dosage_id' => (int) ($_POST['dosage_id'] ?? 0),
            'frequency_id' => (int) ($_POST['frequency_id'] ?? 0),
            'meal_timing_id' => (int) ($_POST['meal_timing_id'] ?? 0),
            'start_date' => trim((string) ($_POST['start_date'] ?? '')),
            'duration_days' => (int) ($_POST['duration_days'] ?? 0),
            'instructions' => trim((string) ($_POST['instructions'] ?? '')),
        ];

        $ok = $payload['medicine_id'] > 0
            && $payload['start_date'] !== ''
            && $payload['duration_days'] > 0
            && PharmacistViewScheduleModel::updateSchedule($scheduleId, $patientNic, $payload);

        Response::redirect($redirectBase . ($ok ? '&success=schedule_updated' : '&error=schedule_update_failed'));
    }
}

$patient = PharmacistViewScheduleModel::getPatient($patientNic);
if (!$patient) {
    Response::redirect('/pharmacist/patients?error=patient_not_found');
}

$schedules = PharmacistViewScheduleModel::getSchedulesByDate($patientNic, $selectedDate);
$editScheduleId = (int) ($_GET['edit'] ?? 0);

$data = [
    'patient' => $patient,
    'schedules' => $schedules,
    'selectedDate' => $selectedDate,
    'editScheduleId' => $editScheduleId,
    'success' => trim((string) ($_GET['success'] ?? '')),
    'error' => trim((string) ($_GET['error'] ?? '')),
    'medicines' => ScheduleModel::getMedicines(),
    'dosages' => ScheduleModel::getDosages(),
    'frequencies' => ScheduleModel::getFrequencies(),
    'mealTimings' => ScheduleModel::getMealTimings(),
];
