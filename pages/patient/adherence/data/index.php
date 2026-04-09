<?php
require_once __DIR__ . '/../../common/patient.head.php';
require_once __DIR__ . '/../adherence.model.php';

header('Content-Type: application/json; charset=UTF-8');

$nic = $user['nic'] ?? '';
if ($nic === '') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$payload = [
    'ok' => true,
    'overallAdherence' => AdherenceModel::getOverallAdherence($nic),
    'weeklyAdherence' => AdherenceModel::getWeeklyAdherence($nic),
    'medicationHistory' => AdherenceModel::getHistory($nic),
    'serverTime' => date('c'),
];

echo json_encode($payload);

