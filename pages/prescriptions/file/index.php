<?php
/**
 * Protected prescription file serving endpoint.
 * URL: /prescriptions/file?id={prescription_id}[&download=1]
 */

$auth = Auth::getUser();
if (!$auth) {
    Response::redirect('/patient/login');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    Response::abort(400, 'Invalid request');
}

$role = (string)($auth['role'] ?? '');
$where = ["id = ?"];
$types = 'i';
$params = [$id];

if ($role === 'patient') {
    $nic = (string)($auth['nic'] ?? '');
    if ($nic === '') {
        Response::abort(403, 'Forbidden');
    }
    $where[] = "patient_nic = ?";
    $types .= 's';
    $params[] = $nic;

    $selectedPharmacyId = PharmacyContext::selectedPharmacyId();
    if ($selectedPharmacyId > 0 && PharmacyContext::tableHasPharmacyId('prescriptions')) {
        $where[] = "pharmacy_id = ?";
        $types .= 'i';
        $params[] = (int) $selectedPharmacyId;
    }
} elseif ($role === 'pharmacist') {
    $pharmacyId = (int)($auth['pharmacy_id'] ?? 0);
    if ($pharmacyId <= 0) {
        $pharmacyId = PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
    }
    if ($pharmacyId > 0 && PharmacyContext::tableHasPharmacyId('prescriptions')) {
        $where[] = "pharmacy_id = ?";
        $types .= 'i';
        $params[] = (int) $pharmacyId;
    }
} elseif ($role !== 'admin') {
    Response::abort(403, 'Forbidden');
}

$row = Database::fetchOne("SELECT id, file_name, file_path FROM prescriptions WHERE " . implode(' AND ', $where) . " LIMIT 1", $types, $params);
if (!$row) {
    Response::abort(404, 'File not found');
}
$stored = basename((string)($row['file_path'] ?? ''));
if ($stored === '' || $stored === '.' || $stored === '..') {
    Response::abort(404, 'File not found');
}

$privatePath = ROOT . '/storage/prescriptions/' . $stored;
$legacyPath = ROOT . '/public/uploads/prescriptions/' . $stored;
$fullPath = is_file($privatePath) ? $privatePath : $legacyPath;

if (!is_file($fullPath)) {
    Response::abort(404, 'File not found');
}

$displayName = trim((string)($row['file_name'] ?? 'prescription'));
if ($displayName === '') {
    $displayName = 'prescription';
}
$displayName = preg_replace('/[^a-zA-Z0-9\-\._ ]/', '_', $displayName) ?: 'prescription';

$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
$mime = $finfo ? (string)finfo_file($finfo, $fullPath) : '';
if ($finfo) {
    finfo_close($finfo);
}
if ($mime === '') {
    $mime = 'application/octet-stream';
}

$download = (int)($_GET['download'] ?? 0) === 1;
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($fullPath));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $displayName . '"');

readfile($fullPath);
exit;
