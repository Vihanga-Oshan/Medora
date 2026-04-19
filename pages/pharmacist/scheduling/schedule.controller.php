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
$wantsSchedule = !empty($prescription['wants_schedule']);
$wantsMedicineOrder = !empty($prescription['wants_medicine_order']);

// Handle form submission
if (Request::isPost()) {
    $schedules = [];
    $orderItems = [];

    if ($wantsSchedule && isset($_POST['medicineId']) && is_array($_POST['medicineId'])) {
        $rows = count($_POST['medicineId']);

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
    }

    if ($wantsMedicineOrder && isset($_POST['orderMedicineId']) && is_array($_POST['orderMedicineId'])) {
        $inventoryIndex = [];
        foreach (ScheduleModel::getInventoryMedicines() as $inventoryMedicine) {
            $inventoryIndex[(int)($inventoryMedicine['id'] ?? 0)] = $inventoryMedicine;
        }

        $orderRows = count($_POST['orderMedicineId']);
        for ($i = 0; $i < $orderRows; $i++) {
            $medicineId = (int)($_POST['orderMedicineId'][$i] ?? 0);
            $quantity = max(0, (int)($_POST['orderQty'][$i] ?? 0));
            if ($medicineId <= 0 || $quantity <= 0 || !isset($inventoryIndex[$medicineId])) {
                continue;
            }
            $selected = $inventoryIndex[$medicineId];
            $orderItems[] = [
                'medicine_id' => $medicineId,
                'medicine_name' => (string)($selected['name'] ?? 'Medicine'),
                'quantity' => $quantity,
                'unit_price' => (float)($selected['price'] ?? 0),
            ];
        }
    }

    if ($wantsSchedule && empty($schedules) && !$wantsMedicineOrder) {
        Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=empty');
    }

    if ($wantsMedicineOrder && empty($orderItems) && !$wantsSchedule) {
        Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=empty_order');
    }

    if ($wantsSchedule && !empty($schedules)) {
        $ok = ScheduleModel::bulkInsert($schedules);
        if (!$ok) {
            Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=save');
        }
    }

    if ($wantsMedicineOrder && !empty($orderItems)) {
        $savedOrder = ScheduleModel::savePrescriptionOrderItems($prescriptionId, $orderItems);
        if (!$savedOrder) {
            Response::redirect('/pharmacist/scheduling?id=' . $prescriptionId . '&nic=' . urlencode((string)$patientNic) . '&error=save_order');
        }
    }

    if (!$wantsSchedule && $wantsMedicineOrder && !empty($orderItems)) {
        ScheduleModel::markPrescriptionProcessed($prescriptionId);
    }

    ScheduleModel::syncPatientPharmacySelection($patientNic);
    ScheduleModel::createNotification(
        $patientNic,
        $wantsSchedule && $wantsMedicineOrder
            ? "Your medication schedule and medicine order are being prepared for prescription: " . $prescription['file_name']
            : ($wantsSchedule
                ? "Your medication schedule has been created for prescription: " . $prescription['file_name']
                : "Your medicine order is being prepared for prescription: " . $prescription['file_name'])
    );
    Response::redirect('/pharmacist/dashboard?msg=workflow_saved');
}

$data = [
    'medicines'    => ScheduleModel::getMedicines(),
    'inventoryMedicines' => ScheduleModel::getInventoryMedicines(),
    'dosages'      => ScheduleModel::getDosages(),
    'frequencies'  => ScheduleModel::getFrequencies(),
    'mealTimings'  => ScheduleModel::getMealTimings(),
    'prescription' => $prescription,
    'order'        => ScheduleModel::getPrescriptionOrder($prescriptionId),
    'orderItems'   => ScheduleModel::getPrescriptionOrderItems($prescriptionId),
];
