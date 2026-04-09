<?php

$planId = (int) (Request::get('planId') ?? Request::post('planId') ?? 0);
$ownerId = (int) ($user['counselorId'] ?? $user['id'] ?? 0);
if ($planId <= 0) {
    Response::redirect('/pharmacist/medication-plans');
}

$errorMessage = null;
if (Request::isPost()) {
    if (CounselorRecoveryViewModel::update($ownerId, $planId, $_POST)) {
        Response::redirect('/pharmacist/medication-plans');
    }
    $errorMessage = 'Failed to update recovery plan.';
}

$plan = CounselorRecoveryViewModel::getPlan($ownerId, $planId);
if (!$plan) {
    Response::redirect('/pharmacist/medication-plans');
}
$tasks = CounselorRecoveryViewModel::getTasks($planId);
$goals = CounselorRecoveryViewModel::getGoals($planId);
$clients = CounselorRecoveryViewModel::getClients($ownerId);
