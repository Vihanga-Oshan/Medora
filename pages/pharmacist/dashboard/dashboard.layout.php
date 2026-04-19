<?php
/**
 * Pharmacist Dashboard Layout
 * Mirrors the original Java/JSP structure and class names.
 */
$metrics = $data['metrics'];
$patientsNeedingCheck = $data['patientsNeedingCheck'];
$patientsNeedingSchedule = $data['patientsNeedingSchedule'];
$inventorySummary = $data['inventorySummary'] ?? [];
$inventoryReorders = $data['inventoryReorders'] ?? [];
$base = APP_BASE ?: '';
$cssVer = time();
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
$pendingCount = (int)($metrics['pendingCount'] ?? 0);
$approvedCount = (int)($metrics['approvedCount'] ?? 0);
$newPatientCount = (int)($metrics['newPatientCount'] ?? 0);
$checkCount = count($patientsNeedingCheck);
$scheduleCount = count($patientsNeedingSchedule);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medora - Dashboard</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/medicine-inventory.css?v=<?= $cssVer ?>">
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
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard/" class="nav-item <?= $isDashboard ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/" class="nav-item <?= $isValidate ? 'active' : '' ?>">Prescription Review</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions/" class="nav-item <?= $isApproved ? 'active' : '' ?>">Approved Prescriptions</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients/" class="nav-item <?= $isPatients ? 'active' : '' ?>">Patients</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/messages/" class="nav-item <?= $isMessages ? 'active' : '' ?>">Messages <span class="nav-badge">2</span></a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory/" class="nav-item <?= $isMedicine ? 'active' : '' ?>">Medicine</a></li>
                    <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/settings/" class="nav-item <?= $isSettings ? 'active' : '' ?>">Settings</a></li>
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
                        <span class="greeting-text"><?= htmlspecialchars($data['greeting'] ?? 'Good Day') ?></span>
                        <span class="date-time"><?= htmlspecialchars($data['currentDate']) ?> &bull; <?= htmlspecialchars($data['currentTime']) ?></span>
                    </div>
                </div>
            </header>

            <section class="inventory-section dashboard-section">
                <div class="section-header">
                    <div>
                        <h2>Pharmacist Command Center</h2>
                        <p>Track prescriptions, review queues, and patient follow-ups from one dashboard.</p>
                    </div>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/" class="add-btn"><span>&#10010;</span> Review Queue</a>
                </div>

                <div class="summary-grid dashboard-summary-grid">
                    <article class="summary-card accent-blue">
                        <span class="summary-label">Pending Prescriptions</span>
                        <strong><?= $pendingCount ?></strong>
                        <small>Waiting for review and validation</small>
                    </article>
                    <article class="summary-card accent-green">
                        <span class="summary-label">Approved for Scheduling</span>
                        <strong><?= $approvedCount ?></strong>
                        <small>Ready for the next fulfillment step</small>
                    </article>
                    <article class="summary-card accent-purple">
                        <span class="summary-label">New Patients</span>
                        <strong><?= $newPatientCount ?></strong>
                        <small>Added during the last 24 hours</small>
                    </article>
                    <article class="summary-card accent-amber">
                        <span class="summary-label">Action Items Today</span>
                        <strong><?= $checkCount + $scheduleCount ?></strong>
                        <small><?= $checkCount ?> checks and <?= $scheduleCount ?> scheduling tasks</small>
                    </article>
                </div>

                <div class="insight-grid dashboard-insight-grid">
                    <section class="panel-card">
                        <div class="panel-head">
                            <h3>Review Overview</h3>
                            <span class="panel-chip">Today</span>
                        </div>
                        <div class="mini-list">
                            <article class="mini-row">
                                <div>
                                    <strong>Prescription validation queue</strong>
                                    <small>Patients waiting for pharmacist review</small>
                                </div>
                                <div class="mini-meta">
                                    <span class="status-badge low"><?= $pendingCount ?> pending</span>
                                    <small><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/" class="inline-panel-link">Open validation list</a></small>
                                </div>
                            </article>
                            <article class="mini-row">
                                <div>
                                    <strong>Scheduling handoff queue</strong>
                                    <small>Approved prescriptions ready for scheduling</small>
                                </div>
                                <div class="mini-meta">
                                    <span class="status-badge healthy"><?= $approvedCount ?> approved</span>
                                    <small><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions/" class="inline-panel-link">Open approved list</a></small>
                                </div>
                            </article>
                            <article class="mini-row">
                                <div>
                                    <strong>Patient registrations</strong>
                                    <small>New patient records created recently</small>
                                </div>
                                <div class="mini-meta">
                                    <span class="status-badge info"><?= $newPatientCount ?> new</span>
                                    <small><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients/" class="inline-panel-link">View patient directory</a></small>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="panel-card">
                        <div class="panel-head">
                            <h3>Inventory Details</h3>
                            <span class="panel-chip">Stock</span>
                        </div>
                        <div class="dashboard-inventory-stats">
                            <article class="dashboard-inventory-stat">
                                <span class="highlight-kicker">Medicines</span>
                                <strong><?= (int)($inventorySummary['total_items'] ?? 0) ?></strong>
                                <small><?= (int)($inventorySummary['total_units'] ?? 0) ?> units currently in stock</small>
                            </article>
                            <article class="dashboard-inventory-stat">
                                <span class="highlight-kicker">Low stock</span>
                                <strong><?= (int)($inventorySummary['low_stock_count'] ?? 0) ?></strong>
                                <small><?= (int)($inventorySummary['out_of_stock_count'] ?? 0) ?> item(s) are already out of stock</small>
                            </article>
                            <article class="dashboard-inventory-stat">
                                <span class="highlight-kicker">Inventory value</span>
                                <strong>Rs. <?= number_format((float)($inventorySummary['total_stock_value'] ?? 0), 2) ?></strong>
                                <small><?= (int)($inventorySummary['supplier_count'] ?? 0) ?> active suppliers linked</small>
                            </article>
                        </div>
                        <div class="dashboard-reorder-block">
                            <div class="dashboard-reorder-head">
                                <strong>Reorder Recommendations</strong>
                                <a href="<?= htmlspecialchars($base) ?>/pharmacist/inventory/" class="inline-panel-link">Open inventory</a>
                            </div>
                            <?php if (empty($inventoryReorders)): ?>
                                <p class="empty-msg">No medicines need reordering right now.</p>
                            <?php else: ?>
                                <div class="mini-list">
                                    <?php foreach ($inventoryReorders as $medicine): ?>
                                        <?php $medicineName = trim((string)($medicine['med_name'] ?? '')) !== '' ? (string)$medicine['med_name'] : (string)($medicine['name'] ?? 'Medicine'); ?>
                                        <article class="mini-row">
                                            <div>
                                                <strong><?= htmlspecialchars($medicineName) ?></strong>
                                                <small><?= htmlspecialchars((string)($medicine['supplier_name'] ?? 'No supplier assigned')) ?></small>
                                            </div>
                                            <div class="mini-meta">
                                                <span class="status-badge low">Reorder <?= (int)($medicine['recommended_reorder_quantity'] ?? 0) ?></span>
                                                <small>On hand <?= (int)($medicine['quantity_in_stock'] ?? 0) ?></small>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <section class="panel-card dashboard-table-panel">
                    <div class="panel-head">
                        <div>
                            <h3>Patient List &mdash; Check Required</h3>
                            <p class="panel-description">Review patients whose prescriptions still need pharmacist validation.</p>
                        </div>
                        <a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/" class="action-link secondary">See All</a>
                    </div>
                    <div class="table-shell">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Condition</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($patientsNeedingCheck)): ?>
                                    <?php foreach ($patientsNeedingCheck as $p): ?>
                                        <tr>
                                            <td class="dashboard-name-cell">
                                                <strong class="med-title"><?= htmlspecialchars($p['name']) ?></strong>
                                                <span class="med-subtext">Prescription review required</span>
                                            </td>
                                            <td>
                                                <span class="med-subtext"><?= htmlspecialchars($p['condition_text'] ?? 'None') ?></span>
                                            </td>
                                            <td>
                                                <div class="action-stack dashboard-action-stack">
                                                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/?id=<?= $p['prescription_id'] ?>" class="action-link">Check</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="empty-msg dashboard-empty-msg">No patients requiring checks at this time.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="panel-card dashboard-table-panel">
                    <div class="panel-head">
                        <div>
                            <h3>Patient List &mdash; Schedule Required</h3>
                            <p class="panel-description">Move approved prescriptions into the scheduling workflow.</p>
                        </div>
                        <a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions/" class="action-link secondary">See All</a>
                    </div>
                    <div class="table-shell">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Condition</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($patientsNeedingSchedule)): ?>
                                    <?php foreach ($patientsNeedingSchedule as $p): ?>
                                        <tr>
                                            <td class="dashboard-name-cell">
                                                <strong class="med-title"><?= htmlspecialchars($p['name']) ?></strong>
                                                <span class="med-subtext">Scheduling request is ready</span>
                                            </td>
                                            <td>
                                                <span class="med-subtext"><?= htmlspecialchars($p['condition_text'] ?? 'None') ?></span>
                                            </td>
                                            <td>
                                                <div class="action-stack dashboard-action-stack">
                                                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions/?id=<?= $p['prescription_id'] ?>&nic=<?= $p['nic'] ?>" class="action-link">Schedule</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="empty-msg dashboard-empty-msg">No patients requiring schedules at this time.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </section>
        </main>
    </div>
</body>
</html>

