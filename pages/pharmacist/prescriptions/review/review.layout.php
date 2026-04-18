<?php
/**
 * Prescription Review Layout
 * Based on: java/src/main/webapp/WEB-INF/views/pharmacist/prescription-review.jsp
 */
$p = $data['prescription'] ?? [];
$pt = $data['patient'] ?? [];
$fileName = (string)($p['file_name'] ?? '');
$filePath = trim((string)($p['file_path'] ?? ''));
$isPdf = str_ends_with(strtolower($fileName), '.pdf');
$base = APP_BASE ?: '';

/**
 * Prescription Review Layout
 * Based on: java/src/main/webapp/WEB-INF/views/pharmacist/prescription-review.jsp
 */
$p = $data['prescription'] ?? [];
$pt = $data['patient'] ?? [];
$fileName = (string)($p['file_name'] ?? '');
$filePath = trim((string)($p['file_path'] ?? ''));
$isPdf = str_ends_with(strtolower($fileName), '.pdf');
$base = APP_BASE ?: '';

/**
 * Prescription Review Layout
 * Based on: java/src/main/webapp/WEB-INF/views/pharmacist/prescription-review.jsp
 */
$p = $data['prescription'] ?? [];
$pt = $data['patient'] ?? [];
$fileName = (string)($p['file_name'] ?? '');
$filePath = trim((string)($p['file_path'] ?? ''));
$isPdf = str_ends_with(strtolower($fileName), '.pdf');
$base = APP_BASE ?: '';

/**
 * Prescription Review Layout
 * Based on: java/src/main/webapp/WEB-INF/views/pharmacist/prescription-review.jsp
 */
$p = $data['prescription'] ?? [];
$pt = $data['patient'] ?? [];
$fileName = (string)($p['file_name'] ?? '');
$filePath = trim((string)($p['file_path'] ?? ''));
$isPdf = str_ends_with(strtolower($fileName), '.pdf');
$base = APP_BASE ?: '';
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
    <title>Medora - Prescription Review</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/prescription-review.css">
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

<main class="main-content review-page">
    <header class="header">
        <div class="user-info">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/avatar.png" alt="User Avatar" class="avatar">
            <span class="user-role"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
        </div>
        <div class="greeting">
            <span class="greeting-icon">&#9728;&#65039;</span>
            <div>
                <span class="greeting-text">Prescription Review</span>
                <span class="date-time">Validating document for <?= htmlspecialchars((string)($pt['name'] ?? 'Patient')) ?></span>
            </div>
        </div>
    </header>

    <div class="review-page-body">
        <h2 class="page-title">Prescription Review</h2>

        <div class="content-wrapper">
            <div class="prescription-image-container">
                <div class="image-header">
                    <span>Prescription Document</span>
                </div>
                <div class="image-box" id="imageContainer">
                    <?php if ($isPdf): ?>
                        <div class="pdf-placeholder">
                            <span class="pdf-icon">&#128196;</span>
                            <p>PDF Document</p>
                            <a href="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)($p['id'] ?? 0) ?>" target="_blank" class="btn secondary">Open PDF</a>
                        </div>
                    <?php elseif (!empty($p['id'])): ?>
                        <img src="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)$p['id'] ?>" alt="Prescription" id="zoomable-image">
                        <div class="zoom-controls">
                            <div class="zoom-info" id="zoomPercent">100%</div>
                            <button type="button" class="zoom-btn" onclick="adjustZoom(-0.2)" title="Zoom Out">-</button>
                            <button type="button" class="zoom-btn" onclick="adjustZoom(0.2)" title="Zoom In">+</button>
                            <button type="button" class="zoom-btn" onclick="resetZoom()" title="Reset">&#8634;</button>
                        </div>
                    <?php else: ?>
                        <p class="error-msg">No prescription document available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="patient-details-card">
                <div class="details-header">
                    <h3>Patient Details</h3>
                </div>
                <div class="details-body">
                    <div class="detail-row">
                        <span class="label">Full Name</span>
                        <span class="value"><?= htmlspecialchars((string)($pt['name'] ?? 'N/A')) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">NIC</span>
                        <span class="value"><?= htmlspecialchars((string)($pt['nic'] ?? ($p['patient_nic'] ?? 'N/A'))) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Emergency Contact</span>
                        <span class="value"><?= htmlspecialchars((string)($pt['emergency_contact'] ?? $pt['phone'] ?? 'Not provided')) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email</span>
                        <span class="value"><?= htmlspecialchars((string)($pt['email'] ?? 'Not provided')) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Allergies</span>
                        <span class="value highlight-danger"><?= htmlspecialchars((string)($pt['allergies'] ?? 'None')) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Chronic Conditions</span>
                        <span class="value"><?= htmlspecialchars((string)($pt['chronic_issues'] ?? 'None')) ?></span>
                    </div>
                    <?php if (!empty($pt['guardian_nic'])): ?>
                        <div class="detail-row">
                            <span class="label">Guardian NIC</span>
                            <span class="value"><?= htmlspecialchars((string)$pt['guardian_nic']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="review-actions">
                    <form method="post" class="action-form">
                        <input type="hidden" name="prescriptionId" value="<?= (int)($p['id'] ?? 0) ?>">
                        <input type="hidden" name="action" value="REJECTED">
                        <button type="submit" class="btn-reject">Reject Prescription</button>
                    </form>
                    <form method="post" class="action-form">
                        <input type="hidden" name="prescriptionId" value="<?= (int)($p['id'] ?? 0) ?>">
                        <input type="hidden" name="action" value="APPROVED">
                        <button type="submit" class="btn-approve">Approve Prescription</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
(function () {
    let scale = 1;
    let tx = 0;
    let ty = 0;
    let startX = 0;
    let startY = 0;
    let dragging = false;

    const target = document.getElementById('zoomable-image');
    const box = document.getElementById('imageContainer');
    const lbl = document.getElementById('zoomPercent');

    function redraw() {
        if (!target) return;
        target.style.transform = 'translate(' + tx + 'px,' + ty + 'px) scale(' + scale + ')';
        if (lbl) lbl.textContent = Math.round(scale * 100) + '%';
    }

    window.adjustZoom = function (delta) {
        const next = scale + delta;
        if (next >= 0.5 && next <= 5) {
            scale = next;
            redraw();
        }
    };

    window.resetZoom = function () {
        scale = 1;
        tx = 0;
        ty = 0;
        redraw();
    };

    if (!box || !target) return;

    box.addEventListener('mousedown', function (e) {
        if (scale <= 1) return;
        dragging = true;
        startX = e.clientX - tx;
        startY = e.clientY - ty;
        target.classList.add('dragging');
        e.preventDefault();
    });

    window.addEventListener('mousemove', function (e) {
        if (!dragging) return;
        tx = e.clientX - startX;
        ty = e.clientY - startY;
        redraw();
    });

    window.addEventListener('mouseup', function () {
        dragging = false;
        target.classList.remove('dragging');
    });

    box.addEventListener('wheel', function (e) {
        e.preventDefault();
        window.adjustZoom(e.deltaY > 0 ? -0.1 : 0.1);
    }, { passive: false });
})();
</script>
</div>
</body>
</html>

