<?php

$patientNic = (string)($user['nic'] ?? '');
$flash = '';

$isAjax = Request::expectsJson();

if (Request::isGet() && (string)($_GET['action'] ?? '') === 'fetch') {
    $messages = PatientMessagesModel::getMessages($patientNic);
    PatientMessagesModel::markPharmacistMessagesRead($patientNic);

    Response::json([
        'ok' => true,
        'messages' => $messages,
        'count' => count($messages),
        'timestamp' => time(),
    ]);
}

if (Request::isPost()) {
    $msg = trim((string)($_POST['message'] ?? ''));
    $sent = PatientMessagesModel::sendMessage($patientNic, $msg);

    if ($isAjax) {
        Response::json([
            'ok' => (bool)$sent,
            'messages' => PatientMessagesModel::getMessages($patientNic),
            'timestamp' => time(),
        ]);
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
    'hasChatTable' => true,
];
