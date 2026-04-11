<?php
/**
 * Patient navbar partial - Java-style header.
 */
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$base = APP_BASE ?: '';
$navItems = [
    ['label' => 'Dashboard', 'href' => $base . '/patient/dashboard', 'match' => '/patient/dashboard'],
    ['label' => 'Prescriptions', 'href' => $base . '/patient/prescriptions', 'match' => '/patient/prescriptions'],
    ['label' => 'History', 'href' => $base . '/patient/adherence', 'match' => '/patient/adherence'],
    ['label' => 'Messages', 'href' => $base . '/patient/messages', 'match' => '/patient/messages'],
    ['label' => 'Pharmacy', 'href' => $base . '/patient/pharmacy-select', 'match' => '/patient/pharmacy-select'],
    ['label' => 'Profile', 'href' => $base . '/patient/profile', 'match' => '/patient/profile'],
];
?>
<header class="header">
    <a class="logo" href="<?= htmlspecialchars($base) ?>/patient/dashboard">
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
        <form method="post" action="<?= htmlspecialchars($base) ?>/patient/logout" style="display:inline;">
            <button type="submit" style="border:none;background:none;cursor:pointer;color:inherit;font:inherit;padding:0 8px;">Logout</button>
        </form>
    </nav>
</header>

