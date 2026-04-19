<?php
/**
 * Medication Scheduling Layout
 * Based on: medication-scheduling.jsp
 */
$medicines = $data['medicines'] ?? [];
$inventoryMedicines = $data['inventoryMedicines'] ?? [];
$dosages = $data['dosages'] ?? [];
$frequencies = $data['frequencies'] ?? [];
$mealTimings = $data['mealTimings'] ?? [];
$p = $data['prescription'] ?? [];
$order = $data['order'] ?? [];
$orderItems = $data['orderItems'] ?? [];
$base = APP_BASE ?: '';

/**
 * Medication Scheduling Layout
 * Based on: medication-scheduling.jsp
 */
$medicines = $data['medicines'] ?? [];
$dosages = $data['dosages'] ?? [];
$frequencies = $data['frequencies'] ?? [];
$mealTimings = $data['mealTimings'] ?? [];
$p = $data['prescription'] ?? [];
$base = APP_BASE ?: '';

/**
 * Medication Scheduling Layout
 * Based on: medication-scheduling.jsp
 */
$medicines = $data['medicines'] ?? [];
$dosages = $data['dosages'] ?? [];
$frequencies = $data['frequencies'] ?? [];
$mealTimings = $data['mealTimings'] ?? [];
$p = $data['prescription'] ?? [];
$base = APP_BASE ?: '';

/**
 * Medication Scheduling Layout
 * Based on: medication-scheduling.jsp
 */
$medicines = $data['medicines'] ?? [];
$dosages = $data['dosages'] ?? [];
$frequencies = $data['frequencies'] ?? [];
$mealTimings = $data['mealTimings'] ?? [];
$p = $data['prescription'] ?? [];
$base = APP_BASE ?: '';

$fileName = (string)($p['file_name'] ?? '');
$filePath = trim((string)($p['file_path'] ?? ''));
$isPdf = str_ends_with(strtolower($fileName), '.pdf');
$errorCode = (string)($_GET['error'] ?? '');
$errorMessage = '';
if ($errorCode === 'csrf') {
    $errorMessage = '';
} elseif ($errorCode === 'empty') {
    $errorMessage = 'Please add at least one complete medication row before submitting.';
} elseif ($errorCode === 'save' || $errorCode === 'commit') {
    $errorMessage = 'Unable to save this schedule right now. Please try again.';
} elseif ($errorCode === 'empty_order') {
    $errorMessage = 'Please add at least one medicine to the order before submitting.';
} elseif ($errorCode === 'save_order') {
    $errorMessage = 'Unable to save the medicine order right now. Please try again.';
}
$wantsSchedule = !empty($p['wants_schedule']);
$wantsMedicineOrder = !empty($p['wants_medicine_order']);

$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isOrders = str_contains($currentPath, '/pharmacist/orders');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Medication Schedule | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/medication-scheduling.css">
</head>
<body>
<div class="container">
    <?php require_once __DIR__ . '/../common/pharmacist.sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <div class="user-info">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/avatar.png" alt="User Avatar" class="avatar">
                <span class="user-role"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
            </div>
            <div class="greeting">
                <span class="greeting-icon">&#9728;&#65039;</span>
                <div>
                    <span class="greeting-text">Medication Scheduling</span>
                    <span class="date-time">Prepare medicine plan for selected prescription</span>
                </div>
            </div>
        </header>

        <div style="padding: 28px 34px;">
            <h1 class="page-title" style="margin: 0 0 18px 0;">Medication Scheduling</h1>
            <?php if ($errorMessage !== ''): ?>
                <div style="margin:0 0 14px 0; padding:10px 12px; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; border-radius:8px;">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="scheduling-grid">
                <form action="<?= htmlspecialchars($base) ?>/pharmacist/scheduling" method="post" id="schedulingForm" class="schedule-form">
                    <input type="hidden" name="prescription_id" value="<?= (int)($p['id'] ?? 0) ?>">
                    <input type="hidden" name="patient_nic" value="<?= htmlspecialchars((string)($p['patient_nic'] ?? $p['patientNic'] ?? '')) ?>">

                    <?php if ($wantsSchedule): ?>
                        <h3>Medication Schedules</h3>

                        <div id="scheduleRows">
                            <div class="med-row" data-row>
                                <label>Medicine</label>
                                <select name="medicineId[]" required>
                                    <option value="">-- Select Medicine --</option>
                                    <?php foreach ($medicines as $m): ?>
                                        <option value="<?= (int)($m['id'] ?? 0) ?>"><?= htmlspecialchars((string)($m['name'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label>Dosage</label>
                                <select name="dosageId[]" required>
                                    <?php foreach ($dosages as $d): ?>
                                        <option value="<?= (int)($d['id'] ?? 0) ?>"><?= htmlspecialchars((string)($d['label'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label>Frequency</label>
                                <select name="frequencyId[]" required>
                                    <?php foreach ($frequencies as $f): ?>
                                        <option value="<?= (int)($f['id'] ?? 0) ?>"><?= htmlspecialchars((string)($f['label'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label>Meal Timing</label>
                                <select name="mealTimingId[]">
                                    <option value="">-- None --</option>
                                    <?php foreach ($mealTimings as $mt): ?>
                                        <option value="<?= (int)($mt['id'] ?? 0) ?>"><?= htmlspecialchars((string)($mt['label'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label>Start Date</label>
                                <input type="date" name="startDate[]" value="<?= date('Y-m-d') ?>" required>

                                <label>Duration (Days)</label>
                                <input type="number" name="durationDays[]" min="1" value="7" required>

                                <label>Instructions (Optional)</label>
                                <textarea name="instructions[]" rows="2" placeholder="E.g. Take with water after meals..."></textarea>

                                <button type="button" class="btn-reject" onclick="removeRow(this)">Remove</button>
                                <br><br>
                                <hr>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn-reject" id="addRowBtn">+ Add Another Medicine</button>
                        </div>
                    <?php endif; ?>

                    <?php if ($wantsMedicineOrder): ?>
                        <div class="workflow-section">
                            <h3>Medicine Order Items</h3>
                            <p class="workflow-copy">Select the inventory items the pharmacy will prepare for this patient.</p>
                            <div id="orderRows">
                                <div class="med-row order-row" data-order-row>
                                    <label>Inventory Item</label>
                                    <select name="orderMedicineId[]" required>
                                        <option value="">-- Select Inventory Item --</option>
                                        <?php foreach ($inventoryMedicines as $medicine): ?>
                                            <option value="<?= (int)($medicine['id'] ?? 0) ?>">
                                                <?= htmlspecialchars((string)($medicine['name'] ?? 'Medicine')) ?> | Stock <?= (int)($medicine['quantity_in_stock'] ?? 0) ?> | Rs. <?= number_format((float)($medicine['price'] ?? 0), 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <label>Quantity</label>
                                    <input type="number" name="orderQty[]" min="1" value="1" required>

                                    <button type="button" class="btn-reject" onclick="removeOrderRow(this)">Remove</button>
                                    <hr>
                                </div>
                            </div>

                            <?php if (!empty($orderItems)): ?>
                                <div class="existing-order-list">
                                    <strong>Current Selected Order Items</strong>
                                    <?php foreach ($orderItems as $item): ?>
                                        <div class="existing-order-item">
                                            <?= htmlspecialchars((string)($item['medicine_name'] ?? 'Medicine')) ?> x <?= (int)($item['quantity'] ?? 0) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="btn-group">
                                <button type="button" class="btn-reject" id="addOrderRowBtn">+ Add Order Item</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="btn-group">
                        <a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="btn-reject">Cancel</a>
                        <button type="submit" class="btn-submit">
                            <?php if ($wantsSchedule && $wantsMedicineOrder): ?>
                                Save Order + Schedule
                            <?php elseif ($wantsMedicineOrder): ?>
                                Save Medicine Order
                            <?php else: ?>
                                Submit Full Schedule
                            <?php endif; ?>
                        </button>
                    </div>
                </form>

                <div class="patient-details-box">
                    <h3>Prescription Reference</h3>
                    <div class="workflow-summary">
                        <?php if ($wantsMedicineOrder): ?><span class="workflow-pill">Medicine Order</span><?php endif; ?>
                        <?php if ($wantsSchedule): ?><span class="workflow-pill workflow-pill-secondary">Schedule</span><?php endif; ?>
                    </div>
                    <div class="preview-placeholder">
                        <?php if ($isPdf): ?>
                            <div class="pdf-box">
                                <span class="pdf-icon">&#128196;</span>
                                <a href="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)($p['id'] ?? 0) ?>" target="_blank" class="view-link">View PDF</a>
                            </div>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)($p['id'] ?? 0) ?>" alt="Prescription" class="preview-image">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const addBtn = document.getElementById('addRowBtn');
const container = document.getElementById('scheduleRows');
const addOrderBtn = document.getElementById('addOrderRowBtn');
const orderContainer = document.getElementById('orderRows');

if (addBtn && container) {
    addBtn.onclick = () => {
        const firstRow = container.querySelector('[data-row]');
        const clone = firstRow.cloneNode(true);

        clone.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.type === 'number') el.value = 7;
            else if (el.type !== 'date') el.value = '';
        });

        container.appendChild(clone);
    };
}

function removeRow(btn) {
    if (!container) return;
    const rows = container.querySelectorAll('[data-row]');
    if (rows.length > 1) {
        btn.closest('[data-row]').remove();
    } else {
        alert('At least one medication is required.');
    }
}

if (addOrderBtn && orderContainer) {
    addOrderBtn.onclick = () => {
        const firstRow = orderContainer.querySelector('[data-order-row]');
        const clone = firstRow.cloneNode(true);
        clone.querySelectorAll('input, select').forEach(el => {
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.type === 'number') el.value = 1;
        });
        orderContainer.appendChild(clone);
    };
}

function removeOrderRow(btn) {
    if (!orderContainer) return;
    const rows = orderContainer.querySelectorAll('[data-order-row]');
    if (rows.length > 1) {
        btn.closest('[data-order-row]').remove();
    } else {
        alert('At least one order item is required.');
    }
}
</script>
</body>
</html>


