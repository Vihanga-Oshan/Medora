<?php
/**
 * Admin Pharmacist List Layout
 * Based on: admin-pharmacists.jsp
 */
$pharmacists = $data['pharmacists'];
$stats = $data['stats'];
$search = $data['search'];
$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Management | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css?v=6">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/user-mgt.css">
</head>
<body class="admin-body admin-pharmacists-page">

<aside class="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
        <span>Medora Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="<?= htmlspecialchars($base) ?>/admin/dashboard"><i>&#128202;</i> Dashboard</a></li>
        <li class="active"><a href="<?= htmlspecialchars($base) ?>/admin/pharmacists"><i>&#128138;</i> Pharmacists</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacies"><i>&#127973;</i> Pharmacies</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacy-assignments"><i>&#128279;</i> Assignments</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests"><i>&#128221;</i> Requests</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/settings"><i>&#9881;</i> Settings</a></li>
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
            <span>&#128269;</span>
            <input id="admin-global-search" type="text" placeholder="Search this page..." autocomplete="off" />
        </div>
    </header>

    <div class="section-container">
        <div class="section-header">
            <div>
                <h1>Pharmacist Management</h1>
                <p>Manage pharmacist accounts and permissions</p>
            </div>
            <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists/add" class="btn btn-primary">+ Add Pharmacist</a>
        </div>

        <div class="stats-row">
            <div class="stat-mini-card">
                <h3>Total Pharmacists</h3>
                <h2><?= $stats['total'] ?></h2>
            </div>
            <div class="stat-mini-card">
                <h3>Active</h3>
                <h2><?= $stats['active'] ?></h2>
            </div>
            <div class="stat-mini-card">
                <h3>Deleted</h3>
                <h2><?= $stats['deleted'] ?></h2>
            </div>
        </div>

        <div class="table-card card">
            <form method="get" class="filter-form">
                <span class="search-icon">&#128269;</span>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, or license...">
            </form>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>License</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pharmacists)): ?>                        
                        <tr><td colspan="4" class="empty-msg">No pharmacists found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pharmacists as $ph): ?>
                            <tr>
                                <td><?= htmlspecialchars($ph['name']) ?></td>
                                <td><?= htmlspecialchars($ph['email']) ?></td>
                                <td><?= htmlspecialchars($ph['license_no']) ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="menu-dots">&#8942;</button>
                                        <div class="dropdown-content">
                                            <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists/edit?id=<?= $ph['id'] ?>">Edit Profile</a>
                                            <form action="<?= htmlspecialchars($base) ?>/admin/pharmacists/delete" method="post" onsubmit="return confirm('Suspend this pharmacist?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_pharmacists_delete')) ?>">
                                                <input type="hidden" name="id" value="<?= $ph['id'] ?>">
                                                <button type="submit" class="danger">Suspend Account</button>
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





