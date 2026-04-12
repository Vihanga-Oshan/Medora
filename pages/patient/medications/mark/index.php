<?php
/**
 * Mark Medication Status
 * Ported from: MarkMedicationStatusServlet.java
 * POST only — marks a schedule entry as TAKEN or MISSED then redirects.
 */
require_once __DIR__ . '/../../common/patient.head.php';
require_once __DIR__ . '/../medications.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/patient/dashboard');
    exit;
}

if (!Csrf::verify($_POST['csrf_token'] ?? null, 'patient_medication_mark')) {
    header('Location: ' . (APP_BASE ?: '') . '/patient/dashboard?error=csrf');
    exit;
}

$scheduleId = (int)($_POST['schedule_id'] ?? 0);
$reminderEventId = (int)($_POST['reminder_event_id'] ?? 0);
$status     = strtoupper(trim($_POST['status'] ?? ''));
$timeSlot   = trim((string)($_POST['time_slot'] ?? $_POST['timeSlot'] ?? ''));
$base       = APP_BASE ?: '';
$redirect   = (string)($_POST['redirect'] ?? ($base . '/patient/dashboard'));

if ($redirect === '' || !str_starts_with($redirect, '/')) {
    $redirect = $base . '/patient/dashboard';
}

if (in_array($status, ['TAKEN', 'MISSED'], true)) {
    if ($reminderEventId > 0) {
        if ($status === 'TAKEN') {
            MedicationReminderService::markTakenFromEvent($reminderEventId, $user['nic']);
        } else {
            MedicationReminderService::markMissedFromEvent($reminderEventId, $user['nic']);
        }
    } elseif ($scheduleId > 0) {
        MedicationsModel::markStatus($scheduleId, $user['nic'], $status, $timeSlot);
    }
}

header('Location: ' . $redirect);
exit;
