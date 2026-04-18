<?php
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
}

$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
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
    <aside class="sidebar">
        <div class="logo-section">
            <div class="logo-icon">&#10010;</div>
            <h1 class="logo-text">Medora</h1>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="nav-item <?= $isDashboard ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate" class="nav-item <?= $isValidate ? 'active' : '' ?>">Prescription Review</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions" class="nav-item <?= $isApproved ? 'active' : '' ?>">Approved Prescriptions</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients" class="nav-item <?= $isPatients ? 'active' : '' ?>">Patients</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/messages" class="nav-item <?= $isMessages ? 'active' : '' ?>">Messages <span class="nav-badge">2</span></a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory" class="nav-item <?= $isMedicine ? 'active' : '' ?>">Medicine</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/settings" class="nav-item <?= $isSettings ? 'active' : '' ?>">Settings</a></li>
            </ul>
        </nav>

        <div class="footer-section">
            <form method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/logout" style="margin-top:10px;">
                <button type="submit" class="nav-item logout-link" style="display:block; width:100%; text-align:left; border:none; background:none; cursor:pointer;">Logout</button>
            </form>
            <div class="copyright">Medora &copy; 2022</div>
            <div class="version">v 1.1.2</div>
        </div>
    </aside>

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

                    <div class="btn-group">
                        <a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="btn-reject">Cancel</a>
                        <button type="submit" class="btn-submit">Submit Full Schedule</button>
                    </div>
                </form>

                <div class="patient-details-box">
                    <h3>Prescription Reference</h3>
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

function removeRow(btn) {
    const rows = container.querySelectorAll('[data-row]');
    if (rows.length > 1) {
        btn.closest('[data-row]').remove();
    } else {
        alert('At least one medication is required.');
    }
}
</script>
</body>
</html>

