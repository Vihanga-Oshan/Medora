<?php
$base = APP_BASE ?: '';

$base = APP_BASE ?: '';

$base = APP_BASE ?: '';

$base = APP_BASE ?: '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/settings.css">
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
                <span class="greeting-icon">&#9881;</span>
                <div>
                    <span class="greeting-text">Settings</span>
                    <span class="date-time">Manage account and pharmacist tools</span>
                </div>
            </div>
        </header>

        <section class="settings-wrap">
            <h1 class="page-title">Settings</h1>

            <div class="settings-grid">
                <article class="settings-card">
                    <h3>Account</h3>
                    <div class="kv"><span>Name</span><strong><?= htmlspecialchars((string)($user['name'] ?? 'Pharmacist')) ?></strong></div>
                    <div class="kv"><span>Email</span><strong><?= htmlspecialchars((string)($user['email'] ?? '-')) ?></strong></div>
                    <div class="kv"><span>Phone</span><strong><?= htmlspecialchars((string)($user['phone'] ?? '-')) ?></strong></div>
                    <div class="kv"><span>License</span><strong><?= htmlspecialchars((string)($user['licenseNo'] ?? '-')) ?></strong></div>
                </article>

                <article class="settings-card">
                    <h3>Tools</h3>
                    <p>Medication plans are managed on a dedicated page.</p>
                    <a class="settings-link" href="<?= htmlspecialchars($base) ?>/pharmacist/medication-plans">Open Medication Plans</a>
                </article>
            </div>
        </section>
    </main>
</div>
</body>
</html>