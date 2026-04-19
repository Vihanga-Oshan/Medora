<?php
$base = APP_BASE ?: '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isOrders = str_contains($currentPath, '/pharmacist/orders') && !str_contains($currentPath, '/pharmacist/orders-history');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
$messageBadge = (int) ($data['unreadTotal'] ?? 0);
?>
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
            <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/orders"
                    class="nav-item <?= $isOrders ? 'active' : '' ?>">Orders</a></li>
            <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients"
                    class="nav-item <?= $isPatients ? 'active' : '' ?>">Patients</a></li>
            <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/messages"
                    class="nav-item <?= $isMessages ? 'active' : '' ?>">Messages<?php if ($messageBadge > 0): ?> <span
                            class="nav-badge"><?= $messageBadge ?></span><?php endif; ?></a></li>
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