<?php
$base = APP_BASE ?: '';
$patient = $data['patient'] ?? [];
$schedules = $data['schedules'] ?? [];
$selectedDate = $data['selectedDate'] ?? date('Y-m-d');
$editScheduleId = (int) ($data['editScheduleId'] ?? 0);
$medicines = $data['medicines'] ?? [];
$dosages = $data['dosages'] ?? [];
$frequencies = $data['frequencies'] ?? [];
$mealTimings = $data['mealTimings'] ?? [];
$success = (string) ($data['success'] ?? '');
$error = (string) ($data['error'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Schedule | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/view-schedule.css?v=<?= time() ?>">
</head>
<body>
<div class="container">
    <?php require_once __DIR__ . '/../common/pharmacist.sidebar.php'; ?>

    <main class="main-content schedule-viewer">
        <div class="table-header">
            <h2>Patient Schedule</h2>
            <div class="search-bar">
                <a href="<?= htmlspecialchars($base) ?>/pharmacist/patients" class="btn-edit">Back to Patients</a>
            </div>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success === 'schedule_updated' ? 'Schedule updated successfully.' : 'Schedule deleted successfully.') ?>
            </div>
        <?php elseif ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error === 'schedule_update_failed'
                    ? 'Unable to update this schedule right now.'
                    : ($error === 'schedule_delete_failed'
                        ? 'Unable to delete this schedule right now.'
                        : 'Invalid schedule request.')) ?>
            </div>
        <?php endif; ?>

        <section class="patient-info-card">
            <h2><?= htmlspecialchars((string) ($patient['name'] ?? 'Patient')) ?></h2>
            <p><strong>NIC:</strong> <?= htmlspecialchars((string) ($patient['nic'] ?? '')) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars((string) ($patient['email'] ?? '')) ?></p>
            <p><strong>Emergency Contact:</strong> <?= htmlspecialchars((string) ($patient['emergency_contact'] ?? '-')) ?></p>
        </section>

        <form method="get" class="date-filter">
            <input type="hidden" name="nic" value="<?= htmlspecialchars((string) ($patient['nic'] ?? '')) ?>">
            <label for="date"><strong>Date</strong></label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
            <button type="submit">View</button>
        </form>

        <section class="schedule-table">
            <h2>Schedule For <?= htmlspecialchars(date('d M Y', strtotime($selectedDate))) ?></h2>
            <?php if (empty($schedules)): ?>
                <p class="no-data-msg">No scheduled medications found for this patient on the selected date.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Meal Timing</th>
                        <th>Instructions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <?php $isEditing = $editScheduleId === (int) ($schedule['id'] ?? 0); ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($schedule['medicine_name'] ?? 'Medication')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['dosage'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['frequency'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['meal_timing'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['instructions'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['status'] ?? 'PENDING')) ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn-row btn-row-edit"
                                       href="<?= htmlspecialchars($base) ?>/pharmacist/view-schedule?nic=<?= urlencode((string) ($patient['nic'] ?? '')) ?>&date=<?= urlencode($selectedDate) ?>&edit=<?= (int) ($schedule['id'] ?? 0) ?>">
                                        Edit
                                    </a>
                                    <button type="button"
                                            class="btn-row btn-row-delete"
                                            data-schedule-id="<?= (int) ($schedule['id'] ?? 0) ?>"
                                            data-medicine-name="<?= htmlspecialchars((string) ($schedule['medicine_name'] ?? 'Medication')) ?>"
                                            onclick="openDeleteModal(this)">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php if ($isEditing): ?>
                            <tr class="edit-row">
                                <td colspan="7">
                                    <form method="post" class="edit-schedule-form">
                                        <input type="hidden" name="nic" value="<?= htmlspecialchars((string) ($patient['nic'] ?? '')) ?>">
                                        <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                                        <input type="hidden" name="schedule_id" value="<?= (int) ($schedule['id'] ?? 0) ?>">
                                        <input type="hidden" name="schedule_action" value="edit">

                                        <div class="edit-grid">
                                            <label>
                                                <span>Medicine</span>
                                                <select name="medicine_id" required>
                                                    <?php foreach ($medicines as $medicine): ?>
                                                        <option value="<?= (int) ($medicine['id'] ?? 0) ?>" <?= (int) ($schedule['medicine_id'] ?? 0) === (int) ($medicine['id'] ?? 0) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) ($medicine['name'] ?? 'Medicine')) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>

                                            <label>
                                                <span>Dosage</span>
                                                <select name="dosage_id">
                                                    <option value="">Select dosage</option>
                                                    <?php foreach ($dosages as $dosage): ?>
                                                        <option value="<?= (int) ($dosage['id'] ?? 0) ?>" <?= (int) ($schedule['dosage_id'] ?? 0) === (int) ($dosage['id'] ?? 0) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) ($dosage['label'] ?? '')) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>

                                            <label>
                                                <span>Frequency</span>
                                                <select name="frequency_id">
                                                    <option value="">Select frequency</option>
                                                    <?php foreach ($frequencies as $frequency): ?>
                                                        <option value="<?= (int) ($frequency['id'] ?? 0) ?>" <?= (int) ($schedule['frequency_id'] ?? 0) === (int) ($frequency['id'] ?? 0) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) ($frequency['label'] ?? '')) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>

                                            <label>
                                                <span>Meal timing</span>
                                                <select name="meal_timing_id">
                                                    <option value="">Select meal timing</option>
                                                    <?php foreach ($mealTimings as $mealTiming): ?>
                                                        <option value="<?= (int) ($mealTiming['id'] ?? 0) ?>" <?= (int) ($schedule['meal_timing_id'] ?? 0) === (int) ($mealTiming['id'] ?? 0) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) ($mealTiming['label'] ?? '')) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>

                                            <label>
                                                <span>Start date</span>
                                                <input type="date" name="start_date" required value="<?= htmlspecialchars((string) ($schedule['start_date'] ?? '')) ?>">
                                            </label>

                                            <label>
                                                <span>Duration (days)</span>
                                                <input type="number" name="duration_days" min="1" required value="<?= (int) ($schedule['duration_days'] ?? 1) ?>">
                                            </label>

                                            <label class="edit-grid-full">
                                                <span>Instructions</span>
                                                <textarea name="instructions" rows="3"><?= htmlspecialchars((string) ($schedule['instructions'] ?? '')) ?></textarea>
                                            </label>
                                        </div>

                                        <div class="edit-actions">
                                            <button type="submit" class="btn-row btn-row-save">Save Changes</button>
                                            <a class="btn-row btn-row-cancel"
                                               href="<?= htmlspecialchars($base) ?>/pharmacist/view-schedule?nic=<?= urlencode((string) ($patient['nic'] ?? '')) ?>&date=<?= urlencode($selectedDate) ?>">
                                                Cancel
                                            </a>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</div>

<div id="deleteScheduleModal" class="modal hidden" aria-hidden="true">
    <div class="modal-content">
        <h3>Delete Schedule</h3>
        <p id="deleteScheduleMessage">Are you sure you want to delete this schedule?</p>
        <form method="post">
            <input type="hidden" name="nic" value="<?= htmlspecialchars((string) ($patient['nic'] ?? '')) ?>">
            <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
            <input type="hidden" name="schedule_id" id="deleteScheduleId" value="">
            <input type="hidden" name="schedule_action" value="delete">
            <div class="modal-actions">
                <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="delete-btn">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDeleteModal(button) {
        const modal = document.getElementById('deleteScheduleModal');
        const scheduleIdInput = document.getElementById('deleteScheduleId');
        const message = document.getElementById('deleteScheduleMessage');
        const medicineName = button.getAttribute('data-medicine-name') || 'this schedule';
        const scheduleId = button.getAttribute('data-schedule-id') || '';

        scheduleIdInput.value = scheduleId;
        message.textContent = 'Are you sure you want to delete "' + medicineName ;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteScheduleModal');
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
    }

    window.addEventListener('click', function (event) {
        const modal = document.getElementById('deleteScheduleModal');
        if (event.target === modal) {
            closeDeleteModal();
        }
    });

    window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
</body>
</html>
