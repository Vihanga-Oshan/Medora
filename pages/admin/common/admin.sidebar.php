<?php

/**
 * Admin Sidebar
 * $user is already set by admin.head.php when this is included.
 * The active page is determined by current URL path.
 */
$currentPath = Request::path();

// Nav items: [label, href, icon filename]
$navItems = [
    ['Dashboard',            '/admin/dashboard',            'dashboard.svg'],
    ['User Management',      '/admin/user-management',      'users.svg'],
    ['Counselor Management', '/admin/counselor-management', 'counselors.svg'],
    ['Analytics',            '/admin/analytics',            'analytics.svg'],
    ['Recovery Plans',       '/admin/recovery-plans',       'recovery.svg'],
    ['Resources',            '/admin/resources',            'resources.svg'],
    ['Support Groups',       '/admin/support-groups',       'groups.svg'],
    ['Finances',             '/admin/finances',             'dollar.svg'],
    ['Content Management',   '/admin/content-management',   'content.svg'],
    ['Job Posts',            '/admin/job-posts',            'jobs.svg'],
    ['Help Center',          '/admin/help-center',          'help.svg'],
    ['Settings',             '/admin/settings',             'settings.svg'],
];
?>
<nav class="sidebar">

    <div class="sidebar__logo">
        <div class="logo-container">
            <img src="/assets/img/logo.svg" alt="New Path Logo" class="logo">
            <span class="logo-text">New<br>Path</span>
        </div>
    </div>

    <div class="sidebar__nav">
        <?php foreach ($navItems as [$label, $href, $icon]): ?>
            <a
                href="<?= $href ?>"
                class="sidebar__item<?= $currentPath === $href ? ' sidebar__item--active' : '' ?>">
                <img src="/assets/icons/<?= $icon ?>" alt="">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="sidebar__footer">
        <!-- User info + logout -->
        <div class="sidebar__item">
            <img src="/assets/icons/user.svg" alt="">
            <span><?= htmlspecialchars($user['name']) ?></span>
        </div>
        <form method="POST" action="/auth/logout">
            <button type="submit" class="sidebar__item btn--full" style="border:none;background:none;width:100%;text-align:left;">
                <img src="/assets/icons/logout.svg" alt="">
                Logout
            </button>
        </form>
    </div>

</nav>