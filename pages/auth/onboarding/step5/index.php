<?php

/**
 * /auth/onboarding/step5 — entry point
 */
require_once __DIR__ . '/../../../../core/Auth.php';

$payload = Auth::requireRole('user');

require_once __DIR__ . '/step5.model.php';
$activePlan = Step5Model::getActivePlan($payload['id']);

require_once __DIR__ . '/step5.layout.php';
