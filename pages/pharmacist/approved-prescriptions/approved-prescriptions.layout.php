<?php
/**
 * Approved Prescriptions Layout (Java-style)
 */
$base = APP_BASE ?: '';

/**
 * Approved Prescriptions Layout (Java-style)
 */
$base = APP_BASE ?: '';

/**
 * Approved Prescriptions Layout (Java-style)
 */
$base = APP_BASE ?: '';

/**
 * Approved Prescriptions Layout (Java-style)
 */
$base = APP_BASE ?: '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$approvedPrescriptions = $data['approvedPrescriptions'] ?? [];
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
    <title>Approved Prescriptions - Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/prescription-validation.css">
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
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard"
                            class="nav-item <?= $isDashboard ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate"
                            class="nav-item <?= $isValidate ? 'active' : '' ?>">Prescription Review</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions"
                            class="nav-item <?= $isApproved ? 'active' : '' ?>">Approved Prescriptions</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients"
                            class="nav-item <?= $isPatients ? 'active' : '' ?>">Patients</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/messages"
                            class="nav-item <?= $isMessages ? 'active' : '' ?>">Messages <span
                                class="nav-badge">2</span></a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory"
                            class="nav-item <?= $isMedicine ? 'active' : '' ?>">Medicine</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/settings"
                            class="nav-item <?= $isSettings ? 'active' : '' ?>">Settings</a></li>
                </ul>
            </nav>

            <div class="footer-section">
                <form method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/logout" style="margin-top:10px;">
                    <button type="submit" class="nav-item logout-link"
                        style="display:block; width:100%; text-align:left; border:none; background:none; cursor:pointer;">Logout</button>
                </form>
                <div class="copyright">Medora &copy; 2022</div>
                <div class="version">v 1.1.2</div>
            </div>
        </aside>

        <div class="main-content">
            <header class="header">
                <div class="user-info">
                    <img src="<?= htmlspecialchars($base) ?>/assets/img/avatar.png" alt="User Avatar" class="avatar">
                    <span class="user-role"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
                </div>
                <div class="greeting">
                    <span class="greeting-icon">&#9728;&#65039;</span>
                    <div>
                        <span class="greeting-text"><?= htmlspecialchars($data['greeting'] ?? 'Good Day') ?></span>
                        <span class="date-time"><?= htmlspecialchars($data['currentDate'] ?? '') ?> &bull;
                            <?= htmlspecialchars($data['currentTime'] ?? '') ?></span>
                    </div>
                </div>
            </header>

            <div class="validation-page-body">
                <div class="pv-header">
                    <h2 class="page-title">Approved Prescriptions</h2>
                    <p class="subtitle">Prepare schedules for validated prescriptions</p>
                </div>

                <?php if (empty($approvedPrescriptions)): ?>
                    <div class="no-data-card">
                        <span class="no-data-icon">&#9989;</span>
                        <p>No approved prescriptions pending scheduling.</p>
                        <a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="btn primary">Back to
                            Dashboard</a>
                    </div>
                <?php endif; ?>

                <div class="prescription-grid">
                    <?php foreach ($approvedPrescriptions as $p): ?>
                        <?php
                        $fileName = (string) ($p['file_name'] ?? '');
                        $filePath = trim((string) ($p['file_path'] ?? ''));
                        $isPdf = str_ends_with(strtolower($fileName), '.pdf');
                        $uploaded = !empty($p['upload_date']) ? date('d M Y, h:i A', strtotime($p['upload_date'])) : 'N/A';
                        ?>
                        <div class="prescription-card">
                            <div class="preview-container">
                                <?php if ($isPdf): ?>
                                    <div class="pdf-thumb">
                                        <span class="pdf-icon">&#128196;</span>
                                        <span class="pdf-text">PDF</span>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int) $p['id'] ?>"
                                        alt="Prescription" class="preview-image">
                                <?php endif; ?>
                            </div>

                            <div class="card-info">
                                <div class="info-group">
                                    <span class="info-label">Patient NIC</span>
                                    <span class="info-value"><?= htmlspecialchars($p['patient_nic'] ?? '') ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Approved On</span>
                                    <span class="info-value"><?= htmlspecialchars($uploaded) ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="file-name"
                                        title="<?= htmlspecialchars($fileName) ?>"><?= htmlspecialchars($fileName) ?></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <a href="<?= htmlspecialchars($base) ?>/pharmacist/scheduling?id=<?= (int) $p['id'] ?>&nic=<?= urlencode((string) $p['patient_nic']) ?>"
                                    class="view-details-btn">
                                    <span>Schedule Medicine</span>
                                    <span class="arrow">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>