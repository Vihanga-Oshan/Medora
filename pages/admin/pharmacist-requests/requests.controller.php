<?php
require_once __DIR__ . '/requests.model.php';

$error = null;
$statusFilter = Request::get('status') ?? '';
if (!in_array($statusFilter, ['', 'pending', 'approved', 'rejected'], true)) {
    $statusFilter = '';
}

if (Request::isPost()) {
    if (!Csrf::verify(Request::post('csrf_token'), 'admin_pharmacist_requests_action')) {
        $error = 'Security validation failed. Please refresh and try again.';
    } else {
    $action = Request::post('action') ?? '';
    $requestId = (int)(Request::post('request_id') ?? 0);
    $adminId = (int)($user['id'] ?? 0);

    if ($action === 'approve') {
        if (!PharmacistRequestsModel::approve($requestId, $adminId)) {
            $error = 'Unable to approve request.';
        } else {
            Response::redirect('/admin/pharmacist-requests?status=pending');
        }
    }

    if ($action === 'reject') {
        $note = (string)(Request::post('note') ?? '');
        if (!PharmacistRequestsModel::reject($requestId, $adminId, $note)) {
            $error = 'Unable to reject request.';
        } else {
            Response::redirect('/admin/pharmacist-requests?status=pending');
        }
    }
    }
}

$requests = PharmacistRequestsModel::all($statusFilter);
