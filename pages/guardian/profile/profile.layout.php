<?php
/**
 * Guardian Profile Layout
 * Admin-style credential editing UI.
 */
$g = $data['guardian'];
$success = (string)($data['success'] ?? '');
$error = (string)($data['error'] ?? '');
$showPasswordCard = (bool)($data['showPasswordCard'] ?? false);
$base = APP_BASE ?: '';
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Medora Guardian</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/profile.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
    <style>
        .settings-shell { max-width: 980px; margin: 0 auto; }
        .settings-card-v2 {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 24px rgba(2, 34, 68, 0.08);
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
        }

        .settings-form-v2 .field-group { margin-bottom: 14px; }
        .settings-form-v2 label {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .settings-form-v2 input[type="text"],
        .settings-form-v2 input[type="email"],
        .settings-form-v2 input[type="password"] {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 16px;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .settings-form-v2 input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.14);
            background: #ffffff;
        }

        .settings-form-v2 input[readonly] {
            background: #eef2f7;
            color: #334155;
        }

        .credential-row { display:block; margin-bottom:14px; }
        .input-with-actions { display:flex; align-items:center; gap:12px; }
        .input-with-actions input { flex:1; }
        .inline-actions { display:inline-flex; align-items:center; gap:8px; flex-shrink:0; }
        .credential-btn { min-width:92px; font-weight: 600; border: none; cursor: pointer; }
        .btn-small { padding:10px 14px; font-size:14px; border-radius: 10px; }
        .btn-muted { background:#e2e8f0; color:#0f172a; }
        .btn-muted:hover { background:#cbd5e1; }
        .btn-cancel { background:#f1f5f9; color:#334155; border:none; border-radius:10px; }
        .btn-cancel:hover { background:#e2e8f0; }
        .btn-save { background:var(--primary-blue); color:#fff; border:none; border-radius:10px; }
        .btn-save:hover { background: var(--primary-hover); }
        .is-hidden { display:none !important; }
        .settings-password-card { margin-top: 12px; }
        .modal { position:fixed; inset:0; background:rgba(15,23,42,.45); display:flex; align-items:center; justify-content:center; z-index:2000; padding:16px; }
        .modal.hidden { display:none !important; }
        .modal-content { width:100%; max-width:460px; background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:20px; box-shadow:0 18px 45px rgba(15,23,42,.2); }
        .modal-content h3 { margin-bottom: 6px; }
        .modal-content p { color: #64748b; margin-bottom: 12px; }
        .verify-feedback {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
        }
        .verify-feedback.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .verify-feedback.success {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }
        .modal-content .field-group input[type="password"] {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 16px;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .modal-content .field-group input[type="password"]:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.14);
            background: #ffffff;
        }
        .modal-actions .btn {
            min-width: 110px;
            height: 44px;
            border-radius: 10px;
            font-weight: 700;
            border: none;
            cursor: pointer;
        }
        .modal-actions .btn-cancel {
            background: #e2e8f0;
            color: #334155;
        }
        .modal-actions .btn-cancel:hover {
            background: #cbd5e1;
        }
        .modal-actions .btn-save {
            background: var(--primary-blue);
            color: #fff;
        }
        .modal-actions .btn-save:hover {
            background: var(--primary-hover);
        }
        .modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:12px; }
        #guardian-ux-toast {
            position: fixed;
            right: 20px;
            bottom: 24px;
            z-index: 2500;
            min-width: 260px;
            max-width: 360px;
            padding: 12px 14px;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(2, 34, 68, 0.25);
            opacity: 0;
            transform: translateY(8px);
            pointer-events: none;
            transition: opacity .25s ease, transform .25s ease;
        }
        #guardian-ux-toast.show { opacity: 1; transform: translateY(0); }
        #guardian-ux-toast.success { background: #15803d; }
        #guardian-ux-toast.info { background: #0369a1; }
        .update-meta {
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #475569;
            background: #eef6ff;
            border: 1px solid #dbeafe;
            border-radius: 999px;
            padding: 6px 12px;
        }
        @media (max-width: 980px) {
            .settings-card-v2 { padding: 18px; }
            .input-with-actions { flex-direction:column; align-items:stretch; }
            .inline-actions { width:100%; }
            .inline-actions .credential-btn { flex:1; }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">My Profile</h1>
    <p class="section-subtitle">Manage your guardian account information</p>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">&#10003; <?= htmlspecialchars($success) ?></div>
        <div id="guardian-last-updated" class="update-meta" data-updated-at="<?= time() ?>">Last updated just now</div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="settings-shell">
        <div class="settings-card-v2 card">
            <div class="profile-header">
                <div>
                    <h2 class="card-title">Account Settings</h2>
                    <p class="card-subtitle">Keep your guardian profile up to date</p>
                </div>
                <div class="avatar" title="<?= htmlspecialchars((string)($g['name'] ?? 'G')) ?>">
                    <?= strtoupper(substr((string)($g['name'] ?? 'G'), 0, 1)) ?>
                </div>
            </div>

            <div class="settings-form-v2">
                <form method="post" id="name-change-form" class="credential-row">
                    <input type="hidden" name="action" value="update_name">
                    <div class="field-group">
                        <label for="guardian-name">Full Name</label>
                        <div class="input-with-actions">
                            <input id="guardian-name" type="text" name="name" value="<?= htmlspecialchars((string)($g['name'] ?? '')) ?>" data-original-value="<?= htmlspecialchars((string)($g['name'] ?? '')) ?>" readonly>
                            <div class="inline-actions">
                                <button id="name-change-btn" type="button" class="btn btn-muted btn-small credential-btn">Change</button>
                                <button id="name-cancel-btn" type="button" class="btn btn-cancel btn-small credential-btn is-hidden">Cancel</button>
                                <button id="name-save-btn" type="submit" class="btn btn-save btn-small credential-btn is-hidden">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="credential-row">
                    <div class="field-group">
                        <label for="guardian-nic-display">NIC (read-only)</label>
                        <input id="guardian-nic-display" type="text" value="<?= htmlspecialchars((string)($g['nic'] ?? '')) ?>" readonly>
                    </div>
                </div>

                <form method="post" id="email-change-form" class="credential-row">
                    <input type="hidden" name="action" value="update_email">
                    <div class="field-group">
                        <label for="guardian-email">Email Address</label>
                        <div class="input-with-actions">
                            <input id="guardian-email" type="email" name="email" value="<?= htmlspecialchars((string)($g['email'] ?? '')) ?>" data-original-value="<?= htmlspecialchars((string)($g['email'] ?? '')) ?>" readonly>
                            <div class="inline-actions">
                                <button id="email-change-btn" type="button" class="btn btn-muted btn-small credential-btn">Change</button>
                                <button id="email-cancel-btn" type="button" class="btn btn-cancel btn-small credential-btn is-hidden">Cancel</button>
                                <button id="email-save-btn" type="submit" class="btn btn-save btn-small credential-btn is-hidden">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                <form method="post" id="phone-change-form" class="credential-row">
                    <input type="hidden" name="action" value="update_phone">
                    <div class="field-group">
                        <label for="guardian-phone">Phone Number</label>
                        <div class="input-with-actions">
                            <input id="guardian-phone" type="text" name="phone" value="<?= htmlspecialchars((string)($g['phone'] ?? '')) ?>" data-original-value="<?= htmlspecialchars((string)($g['phone'] ?? '')) ?>" readonly>
                            <div class="inline-actions">
                                <button id="phone-change-btn" type="button" class="btn btn-muted btn-small credential-btn">Change</button>
                                <button id="phone-cancel-btn" type="button" class="btn btn-cancel btn-small credential-btn is-hidden">Cancel</button>
                                <button id="phone-save-btn" type="submit" class="btn btn-save btn-small credential-btn is-hidden">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="credential-row">
                    <div class="field-group">
                        <label for="guardian-password-mask">Password</label>
                        <div class="input-with-actions">
                            <input id="guardian-password-mask" type="password" value="********" readonly>
                            <div class="inline-actions">
                                <button id="password-change-btn" type="button" class="btn btn-muted btn-small credential-btn">Change</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="password-change-card" class="settings-card-v2 card settings-password-card<?= $showPasswordCard ? '' : ' is-hidden' ?>">
            <div class="profile-header">
                <div>
                    <h2 class="card-title">Change Password</h2>
                    <p class="card-subtitle">Enter and confirm your new password</p>
                </div>
            </div>

            <form class="profile-form settings-form-v2" action="<?= htmlspecialchars($base) ?>/guardian/profile" method="post">
                <input type="hidden" name="action" value="update_password">
                <input type="hidden" id="verified-current-password" name="current_password" value="">

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="8" placeholder="At least 8 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" placeholder="Re-enter new password" required>
                </div>

                <div class="form-actions">
                    <button type="button" id="password-card-cancel-btn" class="btn btn-cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../patient/common/patient.footer.php'; ?>

<div id="password-verify-modal" class="modal hidden" style="display:none;">
    <div class="modal-content">
        <h3>Enter Current Password</h3>
        <p>To change password, please verify your current password first.</p>
        <div class="field-group">
            <input id="modal-current-password" type="password" placeholder="Current password">
        </div>
        <p id="password-verify-error" class="alert alert-error is-hidden" style="margin-top:10px;"></p>
        <p id="password-verify-success" class="verify-feedback success is-hidden"></p>
        <div class="modal-actions">
            <button type="button" id="modal-cancel-btn" class="btn btn-cancel">Cancel</button>
            <button type="button" id="modal-verify-btn" class="btn btn-save">Verify</button>
        </div>
    </div>
</div>

<div id="guardian-ux-toast" role="status" aria-live="polite"></div>

<script>
    window.GuardianSettingsConfig = {
        verifyUrl: "<?= htmlspecialchars($base) ?>/guardian/profile/verify-password",
        startWithPasswordCard: <?= $showPasswordCard ? 'true' : 'false' ?>
    };
</script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/guardian/guardian-settings.js?v=1"></script>

</body>
</html>
