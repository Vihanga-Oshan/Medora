<?php
$pageTitle = 'Medora - Login';
$authCss = 'login/login-patient.css';
$base = APP_BASE ?: '';
require_once __DIR__ . '/../../auth/common/auth.head.php';
?>

<body class="login-page">
    <div class="auth-shell">
        <section class="auth-form-panel">
            <div class="login-container">
                <div class="logo">
                    <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora Logo">
                </div>

                <h1>Log in to your Account</h1>
                <p class="subtitle">Welcome back! Select method to log in.</p>

                <div class="form-toggle">
                    <button class="active" type="button" onclick="location.href='<?= htmlspecialchars($base) ?>/patient/login'">Patient</button>
                    <button type="button" onclick="location.href='<?= htmlspecialchars($base) ?>/guardian/login'">Guardian</button>
                </div>

                <?php if ($error !== null): ?>
                    <p class="error-text"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="">
                    <label for="nic">NIC Number</label>
                    <input
                        type="text"
                        id="nic"
                        name="nic"
                        placeholder="Enter your NIC"
                        required
                        value="<?= htmlspecialchars(Request::post('nic') ?? '') ?>">

                    <label for="password">Password</label>
                    <div class="password-input-container">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required>
                        <button type="button" id="passwordToggle" class="password-toggle" aria-label="Toggle password">&#128065;</button>
                    </div>

                    <div class="form-options">
                        <label>
                            <input type="checkbox" id="keepSignedIn" name="rememberMe" <?= Request::post('rememberMe') ? 'checked' : '' ?>>
                            Keep me signed in on this device
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-submit form-submit-btn">Log in</button>

                    <p class="bottom-text">
                        New here?
                        <a href="<?= htmlspecialchars($base) ?>/patient/register">Create account</a>
                    </p>
                </form>
            </div>
        </section>

        <aside class="auth-visual-panel">
            <div class="visual-card">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/login-illustration.png" alt="Login illustration">
                <h2>Connect with every application.</h2>
                <p>Everything you need in one medication dashboard.</p>
            </div>
        </aside>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/js/auth/login.js" defer></script>
</body>

</html>

