<?php
$base = APP_BASE ?: '';
$patient = $data['patient'] ?? [];
$schedules = $data['schedules'] ?? [];
$selectedDate = $data['selectedDate'] ?? date('Y-m-d');
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isPatients = str_contains($currentPath, '/pharmacist/patients') || str_contains($currentPath, '/pharmacist/view-schedule');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Schedule | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/view-schedule.css">
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

    <main class="main-content schedule-viewer">
        <div class="table-header">
            <h2>Patient Schedule</h2>
            <div class="search-bar">
                <a href="<?= htmlspecialchars($base) ?>/pharmacist/patients" class="btn-edit">Back to Patients</a>
            </div>
        </div>

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
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($schedule['medicine_name'] ?? 'Medication')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['dosage'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['frequency'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['meal_timing'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['instructions'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string) ($schedule['status'] ?? 'PENDING')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
