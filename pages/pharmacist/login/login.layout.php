<?php
$pageTitle = 'Medora - Pharmacist Login';
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

                <h1>Pharmacist Portal</h1>
                <p class="subtitle">Access prescription reviews, schedules, and patient monitoring tools.</p>

                <?php if ($error !== null): ?>
                    <p class="error-text"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form class="login-form" id="counselorLoginForm" method="POST" action="">
                    <label for="id">Pharmacist ID</label>
                    <input
                        type="text"
                        id="id"
                        name="id"
                        placeholder="Enter your pharmacist ID"
                        required
                        value="<?= htmlspecialchars(Request::post('id') ?? '') ?>">

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
                            <input type="checkbox" id="keepSignedIn" name="rememberMe">
                            Keep me signed in on this device
                        </label>
                    </div>

                    <button type="submit" class="btn-submit form-submit-btn">Access Dashboard</button>

                    <p class="bottom-text">
                        <a href="<?= htmlspecialchars($base) ?>/patient/login">Back to patient login</a>
                    </p>
                    <p class="bottom-text">
                        Need access?
                        <a href="<?= htmlspecialchars($base) ?>/pharmacist/register">Create request</a>
                    </p>
                </form>
            </div>
        </section>

        <aside class="auth-visual-panel">
            <div class="visual-card">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/counselor-login.png" alt="Pharmacist login illustration">
                <h2>Review and schedule with confidence.</h2>
                <p>Manage prescriptions and patient plans from one place.</p>
            </div>
        </aside>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/js/auth/login.js" defer></script>
</body>

</html>

