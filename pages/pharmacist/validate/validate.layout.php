<?php
/**
 * Prescription Validation Layout (Java-style)
 */
$base = APP_BASE ?: '';
$cssVer = time();
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$prescriptions = $data['prescriptions'] ?? [];
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
    <title>Validate Prescriptions - Medora</title>
    <link rel="stylesheet"
        href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css?v=<?= $cssVer ?>">
    <link rel="stylesheet"
        href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/prescription-validation.css?v=<?= $cssVer ?>">
</head>

<body>
    <div class="container">
        <?php require_once __DIR__ . '/../common/pharmacist.sidebar.php'; ?>

        <div class="main-content prescription-validation-page">
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
                    <h2 class="page-title">Prescription Validation</h2>
                    <p class="subtitle">Review and validate pending patient prescriptions</p>
                </div>

                <?php if (empty($prescriptions)): ?>
                    <div class="no-data-card">
                        <span class="no-data-icon">&#128203;</span>
                        <p>No pending prescriptions to review right now.</p>
                        <a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="btn primary">Back to
                            Dashboard</a>
                    </div>
                <?php endif; ?>

                <div class="prescription-grid">
                    <?php foreach ($prescriptions as $p): ?>
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
                                    <span class="info-label">Uploaded On</span>
                                    <span class="info-value"><?= htmlspecialchars($uploaded) ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="file-name"
                                        title="<?= htmlspecialchars($fileName) ?>"><?= htmlspecialchars($fileName) ?></span>
                                </div>
                                <div class="info-group">
                                    <?php if (!empty($p['wants_medicine_order'])): ?><span class="status-pending">Medicine
                                            Order</span><?php endif; ?>
                                    <?php if (!empty($p['wants_schedule'])): ?><span
                                            class="status-approved">Schedule</span><?php endif; ?>
                                </div>
                            </div>

                            <div class="card-footer">
                                <a href="<?= htmlspecialchars($base) ?>/pharmacist/prescriptions/review?id=<?= (int) $p['id'] ?>"
                                    class="view-details-btn">
                                    <span>View &amp; Review</span>
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