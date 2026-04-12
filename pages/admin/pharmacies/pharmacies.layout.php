<?php
$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pharmacies | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css?v=6">
</head>
<body class="admin-body">
<aside class="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
        <span>Medora Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="<?= htmlspecialchars($base) ?>/admin/dashboard"><i>&#128202;</i> Dashboard</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacists"><i>&#128138;</i> Pharmacists</a></li>
        <li class="active"><a href="<?= htmlspecialchars($base) ?>/admin/pharmacies"><i>&#127973;</i> Pharmacies</a></li>
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

    <section class="section-container">
        <div class="section-header">
            <div>
                <h1>Pharmacies</h1>
                <p>Manage pharmacies and their active status</p>
            </div>
        </div>

        <?php if (!empty($error)): ?><p class="error-text"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <section class="card panel-card">
            <h3>Add Pharmacy</h3>
            <form method="post" class="admin-grid">
                <input type="hidden" name="action" value="create">
                <input name="name" placeholder="Pharmacy name" required>
                <input name="address_line1" placeholder="Address" required>
                <input name="city" placeholder="City" required>
                <input name="district" placeholder="District">
                <input name="latitude" placeholder="Latitude" required>
                <input name="longitude" placeholder="Longitude" required>
                <input name="phone" placeholder="Phone">
                <input name="email" placeholder="Email">
                <button class="btn btn-primary" type="submit">Create Pharmacy</button>
            </form>
        </section>

        <section class="card panel-card">
            <h3>All Pharmacies</h3>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Address</th><th>City</th><th>Lat/Lng</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($pharmacies as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$p['name']) ?></td>
                        <td><?= htmlspecialchars((string)$p['address_line1']) ?></td>
                        <td><?= htmlspecialchars((string)$p['city']) ?></td>
                        <td><?= htmlspecialchars((string)$p['latitude']) ?>, <?= htmlspecialchars((string)$p['longitude']) ?></td>
                        <td><?= htmlspecialchars((string)$p['status']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="btn btn-primary btn-small" type="submit">Toggle</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </section>
</main>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-search.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-profile-menu.js?v=6"></script>
</body>
</html>





