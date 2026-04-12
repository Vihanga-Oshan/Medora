<?php
/**
 * /admin/pharmacists/edit — Edit pharmacist handler and view
 */
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../../common/admin.activity.php';
require_once __DIR__ . '/../pharmacists.model.php';

$id = (int)($_GET['id'] ?? $_POST['current_id'] ?? 0);
if (!$id) {
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/admin/pharmacists');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_pharmacists_edit')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        if (PharmacistsModel::update($id, $_POST)) {
            $updatedName = trim((string)($_POST['name'] ?? 'Pharmacist'));
            if ($updatedName !== '') {
                AdminActivityLog::log($user, "Updated pharmacist profile for {$updatedName}", 'blue', $user['name'] ?? 'Admin', 'pharmacist', $id);
            }
            $base = APP_BASE ?: '';
            header('Location: ' . $base . '/admin/pharmacists?msg=updated');
            exit;
        }
        $error = "Failed to update pharmacist. Please try again.";
    }
}

$ph = PharmacistsModel::getById($id);
if (!$ph) {
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/admin/pharmacists');
    exit;
}

$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pharmacist | Admin</title>
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
                <h1>Edit Pharmacist Details</h1>
                <p>Update account information for <?= htmlspecialchars($ph['name']) ?></p>
            </div>
        </div>

        <div class="card panel-card form-card">
            <?php if (isset($error)): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_pharmacists_edit')) ?>">
                <input type="hidden" name="current_id" value="<?= $ph['id'] ?>">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($ph['name']) ?>" required>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($ph['email']) ?>" required>
                </div>
                <div class="field">
                    <label>License Number (ID)</label>
                    <input type="number" name="id" value="<?= htmlspecialchars((string)$ph['id']) ?>" required min="1" step="1">
                </div>
                <div class="field">
                    <label>Update Password (leave blank if unchanged)</label>
                    <input type="password" name="password" placeholder="New password">
                </div>

                <div class="form-actions">
                    <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists" class="btn btn-muted">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Updates</button>
                </div>
            </form>
        </div>
    </section>
</main>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-search.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-profile-menu.js?v=6"></script>
</body>
</html>





