<?php
require_once __DIR__ . '/pharmacy-assignments.model.php';

$error = null;
if (Request::isPost()) {
    $action = Request::post('action') ?? '';
    if ($action === 'assign') {
        $pharmacyId = (int)(Request::post('pharmacy_id') ?? 0);
        $pharmacistId = (int)(Request::post('pharmacist_id') ?? 0);
        $primary = (Request::post('is_primary') ?? '') === '1';
        if (!PharmacyAssignmentsModel::assign($pharmacyId, $pharmacistId, $primary)) {
            $error = 'Failed to save assignment.';
        } else {
            Response::redirect('/admin/pharmacy-assignments');
        }
    }

    if ($action === 'deactivate') {
        PharmacyAssignmentsModel::deactivate((int)(Request::post('id') ?? 0));
        Response::redirect('/admin/pharmacy-assignments');
    }
}

$pharmacies = PharmacyAssignmentsModel::pharmacies();
$pharmacists = PharmacyAssignmentsModel::pharmacists();
$assignments = PharmacyAssignmentsModel::allAssignments();