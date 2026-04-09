<?php

$errorMessage = null;
$ownerId = (int) ($user['counselorId'] ?? $user['id'] ?? 0);
$clients = CounselorRecoveryCreateModel::getClients($ownerId);
if (Request::isPost()) {
    if (CounselorRecoveryCreateModel::create($ownerId, $_POST)) {
        Response::redirect('/pharmacist/medication-plans');
    }
    $errorMessage = 'Failed to create recovery plan.';
}
