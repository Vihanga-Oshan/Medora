<?php
/**
 * Medications Timetable Layout
 * Ported from: patient-dashboard.jsp timetable section
 */
$medications  = $data['medications'];
$selectedDate = $data['selectedDate'];
$base         = APP_BASE ?: '';
$cssVer       = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your full medication timetable on Medora">
    <title>Medication Timetable | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/medications.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>

<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">Medication Timetable</h1>
    <p class="section-subtitle">Your full medication schedule by date</p>

    <div class="card">
        <form method="get" class="date-filter-form">
            <label for="date">Select Date</label>
            <div class="input-group">
                <input type="date" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                <button type="submit" class="btn btn-primary">View</button>
            </div>
        </form>

        <p class="results-header">Schedule for <strong><?= htmlspecialchars($selectedDate) ?></strong></p>

        <?php if (empty($medications)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#128197;</div>
                <p>No medication schedule found for this date.</p>
            </div>
        <?php else: ?>
            <div class="medications-table-wrapper">
                <table class="medications-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Dosage</th>
                            <th>Frequency</th>
                            <th>Meal Timing</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medications as $m):
                            $s = strtolower($m['status']);
                            $pillClass = $s === 'taken' ? 'status-taken' : ($s === 'missed' ? 'status-missed' : 'status-pending');
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($m['medicine_name']) ?></strong></td>
                                <td><?= htmlspecialchars($m['dosage']) ?></td>
                                <td><?= htmlspecialchars($m['frequency']) ?></td>
                                <td><?= htmlspecialchars($m['meal_timing'] ?? '—') ?></td>
                                <td><span class="status-pill <?= $pillClass ?>"><?= strtoupper($m['status']) ?></span></td>
                                <td>
                                    <?php if (strtoupper($m['status']) === 'PENDING'): ?>
                                        <div class="table-actions">
                                            <form action="<?= htmlspecialchars($base) ?>/patient/medications/mark" method="post" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('patient_medication_mark')) ?>">
                                                <input type="hidden" name="schedule_id" value="<?= (int)$m['id'] ?>">
                                                <input type="hidden" name="reminder_event_id" value="<?= (int)($m['reminder_event_id'] ?? 0) ?>">
                                                <input type="hidden" name="patient_nic" value="<?= htmlspecialchars($user['nic']) ?>">
                                                <input type="hidden" name="status"      value="TAKEN">
                                                <input type="hidden" name="time_slot"   value="<?= htmlspecialchars((string)($m['frequency_slot'] ?? $m['frequency'])) ?>">
                                                <input type="hidden" name="redirect"    value="<?= htmlspecialchars($base) ?>/patient/medications?date=<?= htmlspecialchars($selectedDate) ?>">
                                                <button type="submit" class="action-btn btn-check" title="Mark Taken">&#10003;</button>
                                            </form>
                                            <form action="<?= htmlspecialchars($base) ?>/patient/medications/mark" method="post" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('patient_medication_mark')) ?>">
                                                <input type="hidden" name="schedule_id" value="<?= (int)$m['id'] ?>">
                                                <input type="hidden" name="reminder_event_id" value="<?= (int)($m['reminder_event_id'] ?? 0) ?>">
                                                <input type="hidden" name="patient_nic" value="<?= htmlspecialchars($user['nic']) ?>">
                                                <input type="hidden" name="status"      value="MISSED">
                                                <input type="hidden" name="time_slot"   value="<?= htmlspecialchars((string)($m['frequency_slot'] ?? $m['frequency'])) ?>">
                                                <input type="hidden" name="redirect"    value="<?= htmlspecialchars($base) ?>/patient/medications?date=<?= htmlspecialchars($selectedDate) ?>">
                                                <button type="submit" class="action-btn btn-cross" title="Mark Missed">&#10007;</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../common/patient.footer.php'; ?>

</body>
</html>
