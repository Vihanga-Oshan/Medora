<?php
/**
 * Adherence Controller
 * Ported from: AdherenceHistoryServlet.java
 */
require_once __DIR__ . '/adherence.model.php';

$nic = $user['nic'];

$data = [
    'overallAdherence' => AdherenceModel::getOverallAdherence($nic),
    'weeklyAdherence'  => AdherenceModel::getWeeklyAdherence($nic),
    'medicationHistory'=> AdherenceModel::getHistory($nic),
];
