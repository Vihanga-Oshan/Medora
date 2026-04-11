<?php
/**
 * Guardian navbar partial - patient-style header pattern.
 */
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$base = APP_BASE ?: '';
$navItems = [
    ['label' => 'Dashboard', 'href' => $base . '/guardian/dashboard', 'match' => '/guardian/dashboard'],
    ['label' => 'Patients', 'href' => $base . '/guardian/patients', 'match' => '/guardian/patients'],
    ['label' => 'Alerts', 'href' => $base . '/guardian/alerts', 'match' => '/guardian/alerts'],
    ['label' => 'Profile', 'href' => $base . '/guardian/profile', 'match' => '/guardian/profile'],
];
?>
<header class="header">
    <a class="logo" href="<?= htmlspecialchars($base) ?>/guardian/dashboard">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora Logo" onerror="this.style.display='none'">
        <span>Medora</span>
    </a>

    <nav class="nav-links">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = str_contains($currentPath, $item['match']); ?>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $isActive ? 'active' : '' ?>">
                <?= htmlspecialchars($item['label']) ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= htmlspecialchars($base) ?>/guardian/logout">Logout</a>
    </nav>
</header>

