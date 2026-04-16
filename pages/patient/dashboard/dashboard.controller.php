<?php
/**
 * Patient Dashboard Controller
 * Reads GET params, calls model, calculates stats, sets $data for layout.
 */
require_once __DIR__ . '/dashboard.model.php';

$nic         = $user['nic'];
$today       = date('Y-m-d');
$selectedDate = $_GET['date'] ?? $today;
$selectedPharmacy = PharmacyContext::pharmacyById(PharmacyContext::selectedPharmacyId());

// All meds for selected date (timetable)
$medications = DashboardModel::getMedicationsByDate($nic, $selectedDate);

// Today's meds for schedule & stats
$todaysMeds  = ($selectedDate === $today)
    ? $medications
    : DashboardModel::getMedicationsByDate($nic, $today);

// Today's pending meds for the schedule list, limited to doses already due.
$nowTs = time();
$pendingMedications = array_filter($todaysMeds, static function ($m) use ($today, $nowTs) {
    if (strtoupper((string)($m['status'] ?? '')) !== 'PENDING') {
        return false;
    }

    $scheduleDate = (string)($m['schedule_date'] ?? $today);
    if ($scheduleDate !== $today) {
        return false;
    }

    $scheduledAt = strtotime((string)($m['scheduled_at'] ?? ''));
    if ($scheduledAt === false) {
        return true;
    }

    return $scheduledAt <= $nowTs;
});

// Stats (based on selected date)
$totalCount   = count($medications);
$takenCount   = count(array_filter($medications, fn($m) => strtoupper($m['status']) === 'TAKEN'));
$missedCount  = count(array_filter($medications, fn($m) => strtoupper($m['status']) === 'MISSED'));
$pendingCount = $totalCount - $takenCount - $missedCount;
$adherenceScore = $totalCount > 0 ? (int)(($takenCount / $totalCount) * 100) : 0;

// Notifications (3 most recent)
$notifications = DashboardModel::getRecentNotifications($nic);

// Guardian link request
$requestingGuardian = DashboardModel::getPendingGuardianRequest($nic);

$data = [
    'medications'        => $medications,
    'pendingMedications' => array_values($pendingMedications),
    'notifications'      => $notifications,
    'requestingGuardian' => $requestingGuardian,
    'selectedDate'       => $selectedDate,
    'totalCount'         => $totalCount,
    'takenCount'         => $takenCount,
    'missedCount'        => $missedCount,
    'pendingCount'       => $pendingCount,
    'adherenceScore'     => $adherenceScore,
    'selectedPharmacy'   => $selectedPharmacy,
];
