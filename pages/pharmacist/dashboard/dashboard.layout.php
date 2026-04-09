<?php
/**
 * Pharmacist Dashboard Layout
 * Mirrors the original Java/JSP structure and class names.
 */
$metrics = $data['metrics'];
$patientsNeedingCheck = $data['patientsNeedingCheck'];
$patientsNeedingSchedule = $data['patientsNeedingSchedule'];
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
    <title>Medora - Dashboard</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
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
                <form method="post" action="<?= htmlspecialchars($base) ?>/auth/logout" style="margin-top:10px;">
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

            <h2 class="page-title">Dashboard</h2>

            <div class="metric-cards">
                <div class="metric-card">
                    <div class="metric-value"><?= (int)($metrics['pendingCount'] ?? 0) ?></div>
                    <div class="metric-label">Pending Prescriptions</div>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/" class="see-all">See All</a>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?= (int)($metrics['approvedCount'] ?? 0) ?></div>
                    <div class="metric-label">Pending Schedules</div>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions/" class="see-all">See All</a>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?= (int)($metrics['newPatientCount'] ?? 0) ?></div>
                    <div class="metric-label">New patients (last 24 hrs)</div>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/patients/" class="see-all">See All</a>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Patient List &mdash; Check Required</h3>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/" class="see-all-link">See All &raquo;</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Chronic Condition</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($patientsNeedingCheck)): ?>
                            <?php foreach ($patientsNeedingCheck as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['condition_text'] ?? 'None') ?></td>
                                    <td><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate/?id=<?= $p['prescription_id'] ?>" class="action-link">Check</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color:#888;">No patients requiring checks at this time.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Patient List &mdash; Schedule Required</h3>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions/" class="see-all-link">See All &raquo;</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Chronic Condition</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($patientsNeedingSchedule)): ?>
                            <?php foreach ($patientsNeedingSchedule as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['condition_text'] ?? 'None') ?></td>
                                    <td><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions/?id=<?= $p['prescription_id'] ?>&nic=<?= $p['nic'] ?>" class="action-link">Schedule</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color:#888;">No patients requiring schedules at this time.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
