<?php
/**
 * Admin Settings Layout
 */
$s = $data['settings'];
$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/user-mgt.css">
</head>
<body class="admin-body admin-settings-page">

<aside class="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
        <span>Medora Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="<?= htmlspecialchars($base) ?>/admin/dashboard"><i>&#128202;</i> Dashboard</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacists"><i>&#128138;</i> Pharmacists</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacies"><i>&#127973;</i> Pharmacies</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacy-assignments"><i>&#128279;</i> Assignments</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests"><i>&#128221;</i> Requests</a></li>
        <li class="active"><a href="<?= htmlspecialchars($base) ?>/admin/settings"><i>&#9881;</i> Settings</a></li>
    </ul>
    <div class="admin-profile">
        <div class="profile-icon">AD</div>
        <div class="profile-info">
            <div class="name"><?= htmlspecialchars($user['name'] ?? 'Admin User') ?></div>
            <div class="email">admin@medora.com</div>
        </div>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <span>&#128269;</span>
            <input type="text" placeholder="Search users, pharmacists..." />
        </div>
        <div class="top-icons">
            <i title="Notifications">&#128276;</i>
        </div>
    </header>

    <section class="settings-shell">
        <div class="settings-card-v2">
            <div class="settings-card-head">
                <span class="head-icon">&#128100;</span>
                <div>
                    <h1>Edit Profile</h1>
                    <p>Edit admin profile</p>
                </div>
            </div>

            <form method="post" class="settings-form-v2">
                <div class="field-group">
                    <label for="admin-full-name">Full Name</label>
                    <input id="admin-full-name" type="text" name="admin_full_name" value="<?= htmlspecialchars($s['admin_full_name'] ?? ($user['name'] ?? '')) ?>" placeholder="Enter full name">
                </div>
                <div class="field-group">
                    <label for="admin-email">Email Address</label>
                    <input id="admin-email" type="email" name="admin_email" value="<?= htmlspecialchars($s['admin_email'] ?? 'admin@medora.com') ?>" placeholder="Enter email address">
                </div>
                <div class="field-group">
                    <label for="admin-nic">NIC</label>
                    <input id="admin-nic" type="text" name="admin_nic" value="<?= htmlspecialchars($s['admin_nic'] ?? '') ?>" placeholder="Enter NIC number">
                </div>
                <div class="field-group">
                    <label for="admin-contact">Contact Number</label>
                    <input id="admin-contact" type="text" name="admin_contact_number" value="<?= htmlspecialchars($s['admin_contact_number'] ?? '') ?>" placeholder="Enter contact number">
                </div>

                <div class="settings-actions">
                    <button type="reset" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
                        <span class="success-msg">&#10003; Saved</span>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>
</main>

</body>
</html>
