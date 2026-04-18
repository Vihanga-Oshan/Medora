<?php
/**
 * Mark Medication Status
 * Ported from: MarkMedicationStatusServlet.java
 * POST only — marks a schedule entry as TAKEN or MISSED then redirects.
 */
require_once __DIR__ . '/../../common/patient.head.php';
require_once __DIR__ . '/../medications.model.php';

if (!Request::isPost()) {
    Response::redirect('/patient/dashboard');
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
    $updated = false;
    if ($reminderEventId > 0) {
        if ($status === 'TAKEN') {
            $updated = MedicationReminderService::markTakenFromEvent($reminderEventId, $user['nic']);
        } else {
            $updated = MedicationReminderService::markMissedFromEvent($reminderEventId, $user['nic']);
        }
    } elseif ($scheduleId > 0) {
        $updated = MedicationsModel::markStatus($scheduleId, $user['nic'], $status, $timeSlot);
    }

    if ($updated && $status === 'MISSED') {
        $patient = Database::fetchOne(
            "SELECT nic, name, guardian_nic FROM patient WHERE nic = ? LIMIT 1",
            's',
            [$user['nic']]
        );

        $guardianNic = trim((string)($patient['guardian_nic'] ?? ''));
        if ($guardianNic !== '') {
            $patientName = trim((string)($patient['name'] ?? 'Patient'));
            $message = $patientName . ' missed a scheduled dose.';

            if (PharmacyContext::tableHasPharmacyId('notifications') && PharmacyContext::selectedPharmacyId() > 0) {
                Database::execute(
                    "INSERT INTO notifications (patient_nic, message, type, is_read, created_at, pharmacy_id)
                     VALUES (?, ?, 'DOSE_MISSED', 0, NOW(), ?)",
                    'ssi',
                    [$user['nic'], $message, PharmacyContext::selectedPharmacyId()]
                );
            } else {
                Database::execute(
                    "INSERT INTO notifications (patient_nic, message, type, is_read, created_at)
                     VALUES (?, ?, 'DOSE_MISSED', 0, NOW())",
                    'ss',
                    [$user['nic'], $message]
                );
            }
        }
    }
}

Response::redirect($redirect);
