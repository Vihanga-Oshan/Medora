<?php
$pageTitle = 'Medora - Patient Registration';
$authCss = 'register/register-patient.css';
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

                <h1>Create Account</h1>
                <p class="subtitle">Join Medora and start your wellness journey</p>

                <div class="toggle-btns">
                    <button class="active" type="button">Patient</button>
                    <button type="button" onclick="location.href='<?= htmlspecialchars($base) ?>/guardian/register'">Guardian</button>
                </div>

                <?php if ($error !== null): ?>
                    <p class="error-text"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="post" action="<?= htmlspecialchars($base) ?>/patient/register">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required placeholder="John Doe" value="<?= htmlspecialchars(Request::post('name') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label>Gender</label>
                            <?php $selectedGender = Request::post('gender') ?? ''; ?>
                            <div class="gender-selection">
                                <label><input type="radio" name="gender" value="Male" <?= $selectedGender === 'Male' ? 'checked' : '' ?> required> Male</label>
                                <label><input type="radio" name="gender" value="Female" <?= $selectedGender === 'Female' ? 'checked' : '' ?>> Female</label>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="emergencyContact">Emergency Contact</label>
                            <input type="text" id="emergencyContact" name="emergencyContact" required placeholder="+94 7X XXX XXXX" value="<?= htmlspecialchars(Request::post('emergencyContact') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="nic">NIC Number</label>
                            <input type="text" id="nic" name="nic" required placeholder="2001XXXXXXXX" value="<?= htmlspecialchars(Request::post('nic') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required placeholder="john@example.com" value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required placeholder="Enter your password">
                        </div>

                        <div class="input-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm your password">
                        </div>

                        <div class="input-group">
                            <label for="allergies">Known Allergies (Optional)</label>
                            <textarea id="allergies" name="allergies" placeholder="List allergies..."><?= htmlspecialchars(Request::post('allergies') ?? '') ?></textarea>
                        </div>

                        <div class="input-group">
                            <label for="chronic">Chronic Medical Conditions (Optional)</label>
                            <textarea id="chronic" name="chronic" placeholder="e.g. Diabetes, Hypertension..."><?= htmlspecialchars(Request::post('chronic') ?? '') ?></textarea>
                        </div>

                        <div class="input-group">
                            <label for="guardianNic">Guardian NIC (Optional)</label>
                            <input type="text" id="guardianNic" name="guardianNic" placeholder="Guardian NIC" value="<?= htmlspecialchars(Request::post('guardianNic') ?? '') ?>">
                        </div>

                        <button type="submit" class="btn-submit">Create Patient Account</button>
                    </div>
                </form>

                <p class="login-text">Already have an account? <a href="<?= htmlspecialchars($base) ?>/login">Log In</a></p>
            </div>
        </section>

        <aside class="auth-visual-panel">
            <div class="visual-card">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/register-patient1.png" alt="Patient registration illustration">
                <h2>Start your care journey today.</h2>
                <p>Track medication, appointments, and progress in one place.</p>
            </div>
        </aside>
    </div>
</body>

</html>
