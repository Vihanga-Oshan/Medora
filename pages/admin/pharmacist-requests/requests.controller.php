<?php
require_once __DIR__ . '/requests.model.php';
require_once __DIR__ . '/../common/admin.activity.php';

$error = null;
$statusFilter = Request::get('status') ?? '';
if (!in_array($statusFilter, ['', 'pending', 'approved', 'rejected'], true)) {
    $statusFilter = '';
}

if (Request::isPost()) {
    $action = Request::post('action') ?? '';
    $requestId = (int)(Request::post('request_id') ?? 0);
    $adminId = (int)($user['id'] ?? 0);

    if ($action === 'approve') {
        if (!PharmacistRequestsModel::approve($requestId, $adminId)) {
            $error = 'Unable to approve request.';
        } else {
            $fullName = 'Pharmacist request';
            $row = Database::fetchOne("SELECT full_name FROM pharmacist_requests WHERE id = ? LIMIT 1", 'i', [$requestId]);
            if ($row) {
                $fullName = trim((string)($row['full_name'] ?? 'Pharmacist request'));
            }
            AdminActivityLog::log($user, "Approved pharmacist request for {$fullName}", 'green', $user['name'] ?? 'Admin', 'pharmacist_request', $requestId);
            Response::redirect('/admin/pharmacist-requests?status=pending');
        }
    }

    if ($action === 'reject') {
        $note = (string)(Request::post('note') ?? '');
        if (!PharmacistRequestsModel::reject($requestId, $adminId, $note)) {
            $error = 'Unable to reject request.';
        } else {
            $fullName = 'Pharmacist request';
            $row = Database::fetchOne("SELECT full_name FROM pharmacist_requests WHERE id = ? LIMIT 1", 'i', [$requestId]);
            if ($row) {
                $fullName = trim((string)($row['full_name'] ?? 'Pharmacist request'));
            }
            AdminActivityLog::log($user, "Rejected pharmacist request for {$fullName}", 'red', $user['name'] ?? 'Admin', 'pharmacist_request', $requestId);
            Response::redirect('/admin/pharmacist-requests?status=pending');
        }
    }
}

$requests = PharmacistRequestsModel::all($statusFilter);
