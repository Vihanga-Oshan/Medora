<?php
/**
 * Admin Settings Layout
 */
$admin = $data['admin'] ?? [];
$error = $data['error'] ?? null;
$success = $data['success'] ?? null;
$showPasswordCard = (bool)($data['showPasswordCard'] ?? false);
$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css?v=6">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/user-mgt.css?v=3">
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

    <section class="settings-shell">
        <div class="settings-card-v2">
            <div class="settings-card-head">
                <span class="head-icon">&#128100;</span>
                <div>
                    <h1>Edit Profile</h1>
                    <p>View and manage your account credentials</p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?= htmlspecialchars((string)$error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="success-msg"><?= htmlspecialchars((string)$success) ?></div>
            <?php endif; ?>

            <div class="settings-form-v2">
                <div class="field-group">
                    <label for="admin-full-name">Full Name</label>
                    <input id="admin-full-name" type="text" value="<?= htmlspecialchars((string)($admin['full_name'] ?? ($user['name'] ?? 'Admin'))) ?>" readonly>
                </div>

                <form method="post" id="email-change-form" class="credential-row">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_settings_update')) ?>">
                    <input type="hidden" name="action" value="update_email">
                    <div class="field-group credential-field">
                        <label for="admin-email">Email Address</label>
                        <div class="input-with-actions">
                            <input id="admin-email" type="email" name="admin_email" value="<?= htmlspecialchars((string)($admin['email'] ?? '')) ?>" data-original-value="<?= htmlspecialchars((string)($admin['email'] ?? '')) ?>" readonly>
                            <div class="inline-actions">
                                <button id="email-change-btn" type="button" class="btn btn-muted btn-small credential-btn">Change</button>
                                <button id="email-cancel-btn" type="button" class="btn btn-cancel btn-small credential-btn is-hidden">Cancel</button>
                                <button id="email-save-btn" type="submit" class="btn btn-save btn-small credential-btn is-hidden">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="credential-row">
                    <div class="field-group credential-field">
                        <label for="admin-password-mask">Password</label>
                        <div class="input-with-actions">
                            <input id="admin-password-mask" type="password" value="********" readonly>
                            <div class="inline-actions">
                                <button id="password-change-btn" type="button" class="btn btn-muted btn-small credential-btn">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="password-change-card" class="settings-card-v2 settings-password-card<?= $showPasswordCard ? '' : ' is-hidden' ?>">
            <div class="settings-card-head">
                <span class="head-icon">&#128274;</span>
                <div>
                    <h1>Change Password</h1>
                    <p>Enter and confirm your new password</p>
                </div>
            </div>

            <form method="post" class="settings-form-v2">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_settings_update')) ?>">
                <input type="hidden" name="action" value="update_password">
                <input type="hidden" id="verified-current-password" name="current_password" value="">

                <div class="field-group">
                    <label for="new-password">New Password</label>
                    <input id="new-password" type="password" name="new_password" placeholder="At least 8 characters" required>
                </div>
                <div class="field-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input id="confirm-password" type="password" name="confirm_password" placeholder="Re-enter new password" required>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="btn-save">Update Password</button>
                </div>
            </form>
        </div>
    </section>
</main>

<div id="password-verify-modal" class="modal hidden" style="display:none;">
    <div class="modal-content">
        <h3>Enter Current Password</h3>
        <p>To change password, please verify your current password first.</p>
        <div class="field-group">
            <input id="modal-current-password" type="password" placeholder="Current password">
        </div>
        <p id="password-verify-error" class="error-msg is-hidden" style="margin-top:10px;"></p>
        <div class="modal-actions">
            <button type="button" id="modal-cancel-btn" class="btn-cancel">Cancel</button>
            <button type="button" id="modal-verify-btn" class="btn-save">Verify</button>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-search.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-profile-menu.js?v=6"></script>
<script>
    window.AdminSettingsConfig = {
        verifyUrl: "<?= htmlspecialchars($base) ?>/admin/settings/verify-password",
        verifyCsrfToken: "<?= htmlspecialchars(Csrf::token('admin_settings_verify_password')) ?>",
        startWithPasswordCard: <?= $showPasswordCard ? 'true' : 'false' ?>
    };
</script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-settings.js?v=3"></script>
</body>
</html>





