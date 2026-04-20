<?php
/**
 * Admin Pharmacist List Layout
 * Based on: admin-pharmacists.jsp
 */
$pharmacists = $data['pharmacists'];
$pharmacies = $data['pharmacies'];
$pharmacyFilter = (int) ($data['pharmacyFilter'] ?? 0);
$stats = $data['stats'];
$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Management | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css?v=7">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/user-mgt.css">
</head>
<body class="admin-body admin-pharmacists-page">

<aside class="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
        <span>Medora Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="<?= htmlspecialchars($base) ?>/admin/dashboard"><i></i> Dashboard</a></li>
        <li class="active"><a href="<?= htmlspecialchars($base) ?>/admin/pharmacists"><i></i> Pharmacists</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacies"><i></i> Pharmacies</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacy-assignments"><i></i> Assignments</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests"><i></i> Requests</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/settings"><i></i> Settings</a></li>
    </ul>
        <div class="admin-profile js-admin-profile">
        <button type="button" class="admin-profile-trigger" aria-haspopup="true" aria-expanded="false">
            <div class="profile-icon">AD</div>
            <div class="profile-info">
                <div class="name"><?= htmlspecialchars($adminEmail ?? ($user['email'] ?? 'admin@medora.com')) ?></div>
            </div>
        </button>
        <div class="admin-profile-menu" role="menu" hidden>
            <div class="admin-profile-menu-email"><?= htmlspecialchars($adminEmail ?? 'admin@medora.com') ?></div>
            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/logout">
                <button type="submit" class="admin-profile-menu-logout">Logout</button>
            </form>
        </div>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <span></span>
            <input id="admin-global-search" type="text" placeholder="Search this page..." autocomplete="off" />
        </div>
    </header>

    <div class="section-container">
        <div class="section-header">
            <div>
                <h1>Pharmacist Management</h1>
                <p>Manage pharmacist accounts and permissions</p>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-mini-card">
                <h3>Total Pharmacists</h3>
                <h2><?= (int)($stats['active'] ?? 0) ?></h2>
            </div>
        </div>

        <section class="card panel-card" style="position:relative;">
            <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists/add" class="btn btn-primary" style="position:absolute; top:24px; right:24px;">+ Add Pharmacist</a>
            <h3 style="margin:0 0 12px;">Filter Pharmacists</h3>
            <form method="get" class="admin-grid">
                <select name="pharmacy_id">
                    <option value="">All Pharmacies</option>
                    <?php foreach ($pharmacies as $p): ?>
                        <option value="<?= (int) ($p['id'] ?? 0) ?>" <?= $pharmacyFilter === (int) ($p['id'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($p['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Filter</button>
            </form>
        </section>

        <div class="table-card card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>License</th>
                        <th>Pharmacy</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pharmacists)): ?>                        
                        <tr><td colspan="5" class="empty-msg">No pharmacists found for this filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pharmacists as $ph): ?>
                            <tr>
                                <td><?= htmlspecialchars($ph['name']) ?></td>
                                <td><?= htmlspecialchars($ph['email']) ?></td>
                                <td><?= htmlspecialchars($ph['license_no']) ?></td>
                                <td><?= htmlspecialchars((string) ($ph['pharmacy_name'] ?? 'Unassigned')) ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="menu-dots">&#8942;</button>
                                        <div class="dropdown-content">
                                            <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists/edit?id=<?= $ph['id'] ?>">Edit Profile</a>
                                            <form class="dropdown-action-form" action="<?= htmlspecialchars($base) ?>/admin/pharmacists/delete" method="post" onsubmit="return confirm('Delete this pharmacist? This can be restored later.')">
                                                <input type="hidden" name="id" value="<?= $ph['id'] ?>">
                                                <button type="submit" class="danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    // Simple dropdown toggle
    document.querySelectorAll('.menu-dots').forEach(btn => {
        btn.onclick = (e) => {
            e.stopPropagation();
            const dropdown = btn.nextElementSibling;
            document.querySelectorAll('.dropdown-content').forEach(d => {
                if (d !== dropdown) d.classList.remove('show');
            });
            dropdown.classList.toggle('show');
        }
    });
    window.onclick = () => {
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
    };
</script>

<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-search.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-profile-menu.js?v=6"></script>
</body>
</html>






