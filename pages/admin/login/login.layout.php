<?php
$pageTitle = 'Medora - Admin Login';
$authCss = 'login/login-guardian.css';
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

                <h1>Admin Portal</h1>
                <p class="subtitle">Access the system administration.</p>

                <?php if ($error !== null): ?>
                    <p class="error-text"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form class="login-form" id="adminLoginForm" method="POST" action="">
                    <label for="email">Admin Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your admin email"
                        required
                        value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">

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
                    </div>

                    <button type="submit" class="btn-submit form-submit-btn">Access Admin Panel</button>

                    <p class="bottom-text">
                        <a href="<?= htmlspecialchars($base) ?>/patient/login">Back to patient login</a>
                    </p>
                    <p class="bottom-text">Restricted access only.</p>
                </form>
            </div>
        </section>

        <aside class="auth-visual-panel">
            <div class="visual-card">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/admin-login.png" alt="Admin login illustration">
                <h2>Full system visibility.</h2>
                <p>Monitor users, operations, and platform health securely.</p>
            </div>
        </aside>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/js/auth/login.js" defer></script>
</body>

</html>

