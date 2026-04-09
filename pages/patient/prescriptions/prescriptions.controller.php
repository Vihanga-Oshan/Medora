<?php
/**
 * Prescriptions Controller — lists all prescriptions for the patient.
 */
require_once __DIR__ . '/prescriptions.model.php';

$nic          = $user['nic'];
$prescriptions = PrescriptionsModel::getByPatient($nic);

$data = [
    'prescriptions' => $prescriptions,
];
