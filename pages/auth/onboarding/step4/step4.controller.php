<?php

/**
 * Step 4 Onboarding Controller
 */
$error = null;

if (Request::isPost()) {
    $selectedPlan = Request::post('selectedPlan') ?? '';

    if ($selectedPlan === '') {
        $error = 'Please select a recovery plan to continue.';
    } else {
        require_once __DIR__ . '/step4.model.php';
        $token = $_COOKIE['jwt'] ?? '';
        $payload = Auth::decode($token);

        if ($payload) {
            $userId = $payload['id'];

            if (Step4Model::createPlanAndComplete($userId, $selectedPlan)) {
                Response::redirect('/auth/onboarding/step5');
            } else {
                $error = 'Failed to generate plan. Please try again.';
            }
        } else {
            Response::redirect('/auth/login/user');
        }
    }
}
