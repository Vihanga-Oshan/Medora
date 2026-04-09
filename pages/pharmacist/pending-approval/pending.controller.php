<?php
require_once __DIR__ . '/../register/register.model.php';

$base = APP_BASE ?: '';
$error = null;
$request = null;

$requestId = (int)($_SESSION['pharmacist_request_id'] ?? 0);
if ($requestId <= 0) {
    $requestId = (int)(Request::get('request_id') ?? 0);
}

if ($requestId > 0) {
    $request = PharmacistRegisterModel::getRequestById($requestId);
}

if (!$request) {
    $error = 'No pending registration request found.';
} else {
    $status = strtolower((string)($request['status'] ?? 'pending'));

    if ($status === 'approved') {
        // Auto-login pharmacist and redirect to dashboard.
        $email = Database::escape((string)($request['email'] ?? ''));
        $table = PharmacyContext::tableExists('pharmacists') ? 'pharmacists' : (PharmacyContext::tableExists('pharmacist') ? 'pharmacist' : '');

        if ($table !== '') {
            $where = "email = '$email'";
            if (PharmacyContext::columnExists($table, 'status')) {
                $where .= " AND status = 'ACTIVE'";
            }

            $rs = Database::search("SELECT * FROM `$table` WHERE $where LIMIT 1");
            if ($rs instanceof mysqli_result) {
                $ph = $rs->fetch_assoc();
                if ($ph) {
                    $pid = (int)($ph['id'] ?? 0);
                    $displayName = (string)($ph['name'] ?? 'Pharmacist');
                    $assignedPharmacyId = PharmacyContext::resolvePharmacistPharmacyId($pid);

                    $token = Auth::sign([
                        'id' => $pid,
                        'name' => $displayName,
                        'role' => 'pharmacist',
                        'pharmacy_id' => $assignedPharmacyId,
                    ]);
                    Auth::setTokenCookie($token, 86400, 'pharmacist');
                    unset($_SESSION['pharmacist_request_id']);
                    Response::redirect('/pharmacist/dashboard');
                }
            }
        }

        $error = 'Your request was approved, but account auto-login failed. Please log in manually.';
    }
}