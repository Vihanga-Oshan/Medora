<?php
$pageTitle = 'Medora - Pharmacist Registration';
$authCss = 'login/login-guardian.css';
$base = APP_BASE ?: '';
require_once __DIR__ . '/../../auth/common/auth.head.php';
?>

<body class="login-page">
    <div class="auth-shell">
        <section class="auth-form-panel">
            <div class="login-container">
                <div class="logo"><img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora Logo"></div>
                <h1>Pharmacist Registration</h1>
                <p class="subtitle">Create your account request for admin approval.</p>

                <?php if ($error): ?>
                    <p class="error-text"><?= htmlspecialchars($error) ?></p><?php endif; ?>
                <?php if ($success): ?>
                    <p style="color:#2e7d32;margin-bottom:12px;"><?= htmlspecialchars($success) ?></p><?php endif; ?>

                <form method="post">
                    <label>Full Name</label>
                    <input type="text" name="name" required
                        value="<?= htmlspecialchars(Request::post('name') ?? '') ?>">

                    <label>Email</label>
                    <input type="email" name="email" required
                        value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">

                    <label>Phone (Optional)</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars(Request::post('phone') ?? '') ?>">

                    <label>License Number</label>
                    <div
                        style="display:flex;align-items:center;height:42px;border:1px solid #cfd7e5;border-radius:9px;margin-bottom:11px;padding:0 10px;background:#fff;">
                        <span style="color:#4b5563;font-weight:600;margin-right:8px;">P-</span>
                        <input type="text" name="license_no" required inputmode="numeric" pattern="[0-9]{4}"
                            maxlength="4" minlength="4" placeholder="1234" title="Enter exactly 4 digits"
                            value="<?= htmlspecialchars(substr(preg_replace('/\D+/', '', (string) (Request::post('license_no') ?? '')) ?? '', 0, 4)) ?>"
                            style="border:0;outline:0;box-shadow:none;padding:0;height:100%;width:100%;margin:0;"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,4);">
                    </div>

                    <label>Pharmacy Location</label>
                    <select name="requested_pharmacy_id" required
                        style="height:42px;width:100%;border:1px solid #cfd7e5;border-radius:9px;padding:0 10px;margin-bottom:11px;">
                        <option value="">Select your pharmacy location</option>
                        <?php foreach ($pharmacies as $ph): ?>
                            <option value="<?= (int) $ph['id'] ?>" <?= ((int) (Request::post('requested_pharmacy_id') ?? 0) === (int) $ph['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $ph['name']) ?> -
                                <?= htmlspecialchars((string) ($ph['city'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Password</label>
                    <input type="password" name="password" required>

                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>

                    <button type="submit" class="btn-submit">Submit Request</button>
                    <p class="bottom-text"><a href="<?= htmlspecialchars($base) ?>/pharmacist/login">Back to Pharmacist
                            Login</a></p>
                </form>
            </div>
        </section>
        <aside class="auth-visual-panel">
            <div class="visual-card">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Pharmacist onboarding">
                <h2>Approval Required</h2>
                <p>Your request is reviewed by admin before activation.</p>
            </div>
        </aside>
    </div>
</body>

</html>