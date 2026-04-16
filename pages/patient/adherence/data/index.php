<?php
require_once __DIR__ . '/../../common/patient.head.php';
require_once __DIR__ . '/../adherence.model.php';

$nic = $user['nic'] ?? '';
if ($nic === '') {
    Response::json(['ok' => false, 'error' => 'Unauthorized'], 401);
}

$payload = [
    'ok' => true,
    'overallAdherence' => AdherenceModel::getOverallAdherence($nic),
    'weeklyAdherence' => AdherenceModel::getWeeklyAdherence($nic),
    'medicationHistory' => AdherenceModel::getHistory($nic),
    'serverTime' => date('c'),
];

Response::json($payload);
