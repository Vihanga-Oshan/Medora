<?php
/**
 * Prescription Review Controller
 */
require_once __DIR__ . '/review.model.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? $_POST['prescriptionId'] ?? 0);
if (!$id) {
    Response::redirect('/pharmacist/dashboard');
}

$prescription = ReviewModel::getPrescriptionById($id);
if (!$prescription) {
    Response::redirect('/pharmacist/dashboard');
}

$patient = ReviewModel::getPatientByNic($prescription['patient_nic']);
$order = ReviewModel::getOrderByPrescriptionId($id);

// Handle POST actions
if (Request::isPost()) {
    $action = strtoupper(trim((string)($_POST['action'] ?? '')));
    if (!in_array($action, ['APPROVED', 'REJECTED'], true)) {
        Response::redirect('/pharmacist/prescriptions/review?id=' . $id . '&error=action');
    }

    $statusUpdated = ReviewModel::updateStatus($id, $action);
    if (!$statusUpdated) {
        Response::redirect('/pharmacist/prescriptions/review?id=' . $id . '&error=update');
    }

    // Notification is best-effort; prescription status should still update even if this fails.
    $msg = ($action === 'APPROVED')
        ? "Good news! Your prescription ({$prescription['file_name']}) has been approved."
        : "Update: Your prescription ({$prescription['file_name']}) was rejected. Please contact the pharmacy.";
    $notified = ReviewModel::createNotification($prescription['patient_nic'], $msg);

    $redirect = '/pharmacist/validate?status=' . strtolower($action);
    if (!$notified) {
        $redirect .= '&notify=failed';
    }
    Response::redirect($redirect);
}

$data = [
    'prescription' => $prescription,
    'patient'      => $patient,
    'order'        => $order,
];
