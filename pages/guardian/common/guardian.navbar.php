<?php
/**
 * Guardian navbar partial.
 * $user is injected from guardian.head.php
 */
$currentPath = $_SERVER['REQUEST_URI'];
$base = APP_BASE ?: '';
?>
<nav class="guardian-navbar">
    <div class="nav-brand">
        <a href="<?= htmlspecialchars($base) ?>/guardian/dashboard">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
            <span>Medora Guardian</span>
        </a>
    </div>

    <ul class="nav-links">
        <li class="<?= str_contains($currentPath, '/guardian/dashboard') ? 'active' : '' ?>">
            <a href="<?= htmlspecialchars($base) ?>/guardian/dashboard">
                <i>&#127968;</i> Dashboard
            </a>
        </li>
        <li class="<?= str_contains($currentPath, '/guardian/patients') ? 'active' : '' ?>">
            <a href="<?= htmlspecialchars($base) ?>/guardian/patients">
                <i>&#128101;</i> Patients
            </a>
        </li>
        <li class="<?= str_contains($currentPath, '/guardian/alerts') ? 'active' : '' ?>">
            <a href="<?= htmlspecialchars($base) ?>/guardian/alerts">
                <i>&#128276;</i> Alerts
            </a>
        </li>
        <li class="<?= str_contains($currentPath, '/guardian/reports') ? 'active' : '' ?>">
            <a href="<?= htmlspecialchars($base) ?>/guardian/reports">
                <i>&#128200;</i> Reports
            </a>
        </li>
    </ul>

    <div class="nav-user">
        <span class="nav-user-name"><?= htmlspecialchars($user['name'] ?? 'Guardian') ?></span>
        <a href="<?= htmlspecialchars($base) ?>/guardian/profile" class="nav-avatar" title="My Profile">
            <?= strtoupper(substr($user['name'] ?? 'G', 0, 1)) ?>
        </a>
        <a href="<?= htmlspecialchars($base) ?>/auth/logout" class="nav-logout" title="Logout">&#128682;</a>
    </div>
</nav>
