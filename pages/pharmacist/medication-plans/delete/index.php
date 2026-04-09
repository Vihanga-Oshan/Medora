<?php

require_once __DIR__ . '/../../common/pharmacist.head.php';
require_once __DIR__ . '/../../common/counselor.data.php';

$planId = (int) (Request::get('planId') ?? 0);
$ownerId = (int) ($user['counselorId'] ?? $user['id'] ?? 0);
if ($planId > 0) {
    CounselorData::deletePlan($ownerId, $planId);
}

Response::redirect('/pharmacist/medication-plans');
