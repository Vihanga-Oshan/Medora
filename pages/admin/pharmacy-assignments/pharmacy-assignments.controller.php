<?php
require_once __DIR__ . '/pharmacy-assignments.model.php';
require_once __DIR__ . '/../common/admin.activity.php';

$error = null;
if (Request::isPost()) {
    $action = Request::post('action') ?? '';
    if ($action === 'assign') {
        $pharmacyId = (int) (Request::post('pharmacy_id') ?? 0);
        $pharmacistId = (int) (Request::post('pharmacist_id') ?? 0);
        $primary = (Request::post('is_primary') ?? '') === '1';
        if (!PharmacyAssignmentsModel::assign($pharmacyId, $pharmacistId, $primary)) {
            $error = 'Failed to save assignment.';
        } else {
            $pharmacyName = 'Pharmacy';
            $pharmacistName = 'Pharmacist';
            $row = Database::fetchOne("SELECT name FROM pharmacies WHERE id = ? LIMIT 1", 'i', [$pharmacyId]);
            if ($row) {
                $pharmacyName = trim((string) ($row['name'] ?? 'Pharmacy'));
            }

            $row = Database::fetchOne("SELECT name FROM pharmacist WHERE id = ? LIMIT 1", 'i', [$pharmacistId]);
            if ($row) {
                $pharmacistName = trim((string) ($row['name'] ?? 'Pharmacist'));
            }

            $roleText = $primary ? 'as primary' : 'as secondary';
            AdminActivityLog::log($user, "Assigned {$pharmacistName} to {$pharmacyName} {$roleText}", 'green', $user['name'] ?? 'Admin', 'assignment');
            Response::redirect('/admin/pharmacy-assignments');
        }
    }

    if ($action === 'deactivate') {
        $assignmentId = (int) (Request::post('id') ?? 0);
        $assignmentInfo = null;
        if ($assignmentId > 0) {
            $assignmentInfo = Database::fetchOne("
                    SELECT pu.id, COALESCE(ph.name, 'Pharmacy') AS pharmacy_name, COALESCE(p.name, 'Pharmacist') AS pharmacist_name
                    FROM pharmacy_users pu
                    LEFT JOIN pharmacies ph ON ph.id = pu.pharmacy_id
                    LEFT JOIN pharmacist p ON p.id = pu.pharmacist_id
                    WHERE pu.id = ?
                    LIMIT 1
                ", 'i', [$assignmentId]);
        }

        $ok = PharmacyAssignmentsModel::deactivate($assignmentId);
        if ($ok && $assignmentInfo) {
            $row = $assignmentInfo;
            $pharmacistName = trim((string) ($row['pharmacist_name'] ?? 'Pharmacist'));
            $pharmacyName = trim((string) ($row['pharmacy_name'] ?? 'Pharmacy'));
            AdminActivityLog::log($user, "Deactivated assignment of {$pharmacistName} at {$pharmacyName}", 'red', $user['name'] ?? 'Admin', 'assignment', $assignmentId);
        }
        Response::redirect('/admin/pharmacy-assignments');
    }
}

$pharmacies = PharmacyAssignmentsModel::pharmacies();
$pharmacists = PharmacyAssignmentsModel::pharmacists();
$assignments = PharmacyAssignmentsModel::allAssignments();
