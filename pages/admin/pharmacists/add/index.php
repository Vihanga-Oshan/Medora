<?php
/**
 * /admin/pharmacists/add — Add pharmacist handler and view
 */
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../../common/admin.activity.php';
require_once __DIR__ . '/../pharmacists.model.php';

$pharmacies = PharmacyContext::getPharmacies();

if (Request::isPost()) {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_pharmacists_add')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        if (PharmacistsModel::create($_POST)) {
            $createdName = trim((string)($_POST['name'] ?? 'Pharmacist'));
            if ($createdName !== '') {
                AdminActivityLog::log($user, "Created pharmacist account for {$createdName}", 'green', $user['name'] ?? 'Admin', 'pharmacist');
            }
            Response::redirect('/admin/pharmacists?msg=added');
        }
        $error = "Failed to create pharmacist. Please check the information.";
    }
}

$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pharmacist | Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css?v=6">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/add-pharmacist.css">
</head>
<body class="admin-body admin-add-pharmacist-page">
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

    <section class="section-container">
        <div class="section-header">
            <div>
                <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists" class="back-link">&#8592; Back to Pharmacists</a>
                <h1>Add New Pharmacist</h1>
                <p>Create a specialized pharmacist account with license validation.</p>
            </div>
        </div>

        <div class="card panel-card form-card">
            <?php if (isset($error)): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_pharmacists_add')) ?>">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Dr. John Doe">
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="field">
                    <label>License Number (ID)</label>
                    <input type="number" name="id" required min="1" step="1" placeholder="12345678">
                </div>
                <div class="field">
                    <label>Default Password</label>
                    <input type="password" name="password" required placeholder="Min 8 characters">
                </div>
                <div class="field">
                    <label>Assign Pharmacy</label>
                    <select name="pharmacy_id" required>
                        <option value="">Select pharmacy</option>
                        <?php foreach ($pharmacies as $ph): ?>
                            <option value="<?= (int)$ph['id'] ?>">
                                <?= htmlspecialchars((string)$ph['name']) ?><?= ((int)($ph['is_demo'] ?? 0) === 1 ? ' (Demo)' : '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists" class="btn btn-muted">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </section>
</main>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-search.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-profile-menu.js?v=6"></script>
</body>
</html>





