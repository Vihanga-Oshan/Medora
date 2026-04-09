<?php
$pageTitle = 'Medora - Guardian Registration';
$authCss = 'register/register-guardian.css';
$base = APP_BASE ?: '';
require_once __DIR__ . '/../../auth/common/auth.head.php';
?>

<body class="register-page">
    <div class="auth-shell register-shell">
        <section class="auth-form-panel">
            <div class="register-box">
                <a class="back-link" href="<?= htmlspecialchars($base) ?>/landing">Back to Home</a>

                <div class="logo">
                    <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora Logo">
                </div>

                <h1>Guardian Registration</h1>
                <p class="subtitle">Join our network to care for your loved ones</p>

                <div class="toggle-btns">
                    <button type="button" onclick="location.href='<?= htmlspecialchars($base) ?>/patient/register'">Patient</button>
                    <button class="active" type="button">Guardian</button>
                </div>

                <?php if ($error !== null): ?>
                    <p class="error-text"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="post" action="<?= htmlspecialchars($base) ?>/guardian/register">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="g_name">Full Name</label>
                            <input type="text" id="g_name" name="g_name" required placeholder="Enter your full name" value="<?= htmlspecialchars(Request::post('g_name') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="nic">NIC Number</label>
                            <input type="text" id="nic" name="nic" required placeholder="2001XXXXXXXX" value="<?= htmlspecialchars(Request::post('nic') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" required placeholder="+94 7X XXX XXXX" value="<?= htmlspecialchars(Request::post('contact_number') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required placeholder="guardian@example.com" value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="password">Security Password</label>
                            <input type="password" id="password" name="password" required placeholder="Enter your password">
                        </div>

                        <label class="checkbox">
                            <input type="checkbox" name="agree" value="1" <?= Request::post('agree') ? 'checked' : '' ?>>
                            I agree to the <a href="#">Privacy Policies</a>
                        </label>

                        <button type="submit" class="btn-submit">Create Guardian Account</button>
                    </div>
                </form>

                <p class="login-text">Already have an account? <a href="<?= htmlspecialchars($base) ?>/guardian/login">Log In</a></p>
            </div>
        </section>

        <aside class="auth-visual-panel">
            <div class="visual-card">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/reg-guardian.webp" alt="Guardian registration illustration">
                <h2>Support every step of recovery.</h2>
                <p>Stay connected with schedules, reminders, and updates.</p>
            </div>
        </aside>
    </div>
</body>

</html>
