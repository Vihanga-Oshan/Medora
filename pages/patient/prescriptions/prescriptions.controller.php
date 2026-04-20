<?php

require_once __DIR__ . '/prescriptions.model.php';

$error = null;

if (Request::isPost()) {
    require __DIR__ . '/upload/upload.controller.php';
}

$nic = $user['nic'];
$prescriptions = PrescriptionsModel::getByPatient($nic);

$data = [
    'prescriptions' => $prescriptions,
];
