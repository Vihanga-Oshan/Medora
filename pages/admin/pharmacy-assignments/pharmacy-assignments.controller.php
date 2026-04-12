<?php
require_once __DIR__ . '/pharmacy-assignments.model.php';
require_once __DIR__ . '/../common/admin.activity.php';

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
            $pharmacyName = 'Pharmacy';
            $pharmacistName = 'Pharmacist';
            $rsPharmacy = Database::search("SELECT name FROM pharmacies WHERE id = $pharmacyId LIMIT 1");
            if ($rsPharmacy instanceof mysqli_result && $rsPharmacy->num_rows > 0) {
                $row = $rsPharmacy->fetch_assoc();
                $pharmacyName = trim((string)($row['name'] ?? 'Pharmacy'));
            }

            $table = PharmacyContext::tableExists('pharmacists') ? 'pharmacists' : 'pharmacist';
            $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            $rsPharmacist = Database::search("SELECT name FROM `$safeTable` WHERE id = $pharmacistId LIMIT 1");
            if ($rsPharmacist instanceof mysqli_result && $rsPharmacist->num_rows > 0) {
                $row = $rsPharmacist->fetch_assoc();
                $pharmacistName = trim((string)($row['name'] ?? 'Pharmacist'));
            }

            $roleText = $primary ? 'as primary' : 'as secondary';
            AdminActivityLog::log($user, "Assigned {$pharmacistName} to {$pharmacyName} {$roleText}", 'green', $user['name'] ?? 'Admin', 'assignment');
            Response::redirect('/admin/pharmacy-assignments');
        }
    }

    if ($action === 'deactivate') {
        $assignmentId = (int)(Request::post('id') ?? 0);
        $assignmentInfo = null;
        if ($assignmentId > 0) {
            $table = PharmacyContext::tableExists('pharmacists') ? 'pharmacists' : 'pharmacist';
            $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            $assignmentInfo = Database::search("
                SELECT pu.id, COALESCE(ph.name, 'Pharmacy') AS pharmacy_name, COALESCE(p.name, 'Pharmacist') AS pharmacist_name
                FROM pharmacy_users pu
                LEFT JOIN pharmacies ph ON ph.id = pu.pharmacy_id
                LEFT JOIN `$safeTable` p ON p.id = pu.pharmacist_id
                WHERE pu.id = $assignmentId
                LIMIT 1
            ");
        }

        $ok = PharmacyAssignmentsModel::deactivate($assignmentId);
        if ($ok && $assignmentInfo instanceof mysqli_result && $assignmentInfo->num_rows > 0) {
            $row = $assignmentInfo->fetch_assoc();
            $pharmacistName = trim((string)($row['pharmacist_name'] ?? 'Pharmacist'));
            $pharmacyName = trim((string)($row['pharmacy_name'] ?? 'Pharmacy'));
            AdminActivityLog::log($user, "Deactivated assignment of {$pharmacistName} at {$pharmacyName}", 'red', $user['name'] ?? 'Admin', 'assignment', $assignmentId);
        }
        Response::redirect('/admin/pharmacy-assignments');
    }
}

$pharmacies = PharmacyAssignmentsModel::pharmacies();
$pharmacists = PharmacyAssignmentsModel::pharmacists();
$assignments = PharmacyAssignmentsModel::allAssignments();
