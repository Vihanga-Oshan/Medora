<?php

$patientNic = (string)($user['nic'] ?? '');
$flash = '';

$acceptHeader = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
$requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
$isAjax = $requestedWith === 'xmlhttprequest'
    || str_contains($acceptHeader, 'application/json')
    || (string)($_POST['ajax'] ?? '') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (string)($_GET['action'] ?? '') === 'fetch') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    $messages = PatientMessagesModel::getMessages($patientNic);
    PatientMessagesModel::markPharmacistMessagesRead($patientNic);

    echo json_encode([
        'ok' => true,
        'messages' => $messages,
        'count' => count($messages),
        'timestamp' => time(),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim((string)($_POST['message'] ?? ''));
    $sent = PatientMessagesModel::sendMessage($patientNic, $msg);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo json_encode([
            'ok' => (bool)$sent,
            'messages' => PatientMessagesModel::getMessages($patientNic),
            'timestamp' => time(),
        ]);
        exit;
    }

    Response::redirect('/patient/messages?msg=' . ($sent ? 'sent' : 'failed'));
}

$flash = trim((string)($_GET['msg'] ?? ''));
$messages = PatientMessagesModel::getMessages($patientNic);
PatientMessagesModel::markPharmacistMessagesRead($patientNic);
$activeMeds = PatientMessagesModel::getActiveMedsForToday($patientNic);

$data = [
    'messages' => $messages,
    'activeMeds' => $activeMeds,
    'flash' => $flash,
    'hasChatTable' => PatientMessagesModel::canUseMessages(),
];
