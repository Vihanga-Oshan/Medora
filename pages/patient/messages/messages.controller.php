<?php

$patientNic = (string)($user['nic'] ?? '');
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim((string)($_POST['message'] ?? ''));
    $sent = PatientMessagesModel::sendMessage($patientNic, $msg);
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

