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

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'pharmacist_prescription_review_action')) {
        Response::redirect('/pharmacist/prescriptions/review?id=' . $id . '&error=csrf');
    }

    $action = $_POST['action'] ?? '';
    Database::beginTransaction();
    $ok = ReviewModel::updateStatus($id, $action);
    if ($ok) {
        $msg = ($action === 'APPROVED') 
            ? "Good news! Your prescription ({$prescription['file_name']}) has been approved."
            : "Update: Your prescription ({$prescription['file_name']}) was rejected. Please contact the pharmacy.";
        
        $ok = ReviewModel::createNotification($prescription['patient_nic'], $msg);
    }

    if ($ok) {
        if (Database::commit()) {
            Response::redirect('/pharmacist/validate');
        }
    }
    Database::rollback();
}

$data = [
    'prescription' => $prescription,
    'patient'      => $patient,
];
