<?php
/**
 * Prescription Validation Controller
 */
$prescriptions = ValidateModel::getPendingPrescriptions();

$data = [
    'prescriptions' => $prescriptions,
    'greeting' => (function (): string {
        $hour = (int)date('H');
        if ($hour < 12) return 'Good Morning';
        if ($hour < 18) return 'Good Afternoon';
        return 'Good Evening';
    })(),
    'currentDate' => date('d F Y'),
    'currentTime' => date('H:i:s'),
];
