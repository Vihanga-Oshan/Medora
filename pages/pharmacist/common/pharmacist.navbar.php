<?php
/**
 * Pharmacist navbar partial.
 * $user is injected from pharmacist.head.php
 */
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$base = APP_BASE ?: '';
?>
<nav class="pharmacist-navbar">
    <div class="nav-brand">
        <a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
            <span>Medora Pharmacist</span>
        </a>
    </div>

    <ul class="nav-links">
        <?php
        $isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
        $isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
        $isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
        $isPatients = str_contains($currentPath, '/pharmacist/patients');
        $isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
        $isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
        $isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
        ?>
        <li class="<?= $isDashboard ? 'active' : '' ?>"><a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard"><i>&#9962;</i> Dashboard</a></li>
        <li class="<?= $isValidate ? 'active' : '' ?>"><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate"><i>&#128196;</i> Prescription Review</a></li>
        <li class="<?= $isApproved ? 'active' : '' ?>"><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions"><i>&#9989;</i> Approved Prescriptions</a></li>
        <li class="<?= $isPatients ? 'active' : '' ?>"><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients"><i>&#128101;</i> Patients</a></li>
        <li class="<?= $isMessages ? 'active' : '' ?>"><a href="<?= htmlspecialchars($base) ?>/pharmacist/messages"><i>&#128172;</i> Messages</a></li>
        <li class="<?= $isMedicine ? 'active' : '' ?>"><a href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory"><i>&#128138;</i> Medicine</a></li>
        <li class="<?= $isSettings ? 'active' : '' ?>"><a href="<?= htmlspecialchars($base) ?>/pharmacist/settings"><i>&#9881;</i> Settings</a></li>
    </ul>

    <div class="nav-user">
        <span class="nav-user-name"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
        <a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="nav-avatar" title="Dashboard">
            <?= strtoupper(substr($user['name'] ?? 'P', 0, 1)) ?>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/pharmacist/logout" class="nav-logout" title="Logout">&#128682;</a>
    </div>
</nav>
