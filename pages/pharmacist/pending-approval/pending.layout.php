<?php
$pageTitle = 'Medora - Approval Pending';
$authCss = 'login/login-guardian.css';
$base = APP_BASE ?: '';
require_once __DIR__ . '/../../auth/common/auth.head.php';
$status = strtolower((string)($request['status'] ?? 'pending'));
?>
<body class="login-page">
<div class="auth-shell">
    <section class="auth-form-panel">
        <div class="login-container">
            <div class="logo"><img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora Logo"></div>

            <?php if ($error): ?>
                <h1>Request Status</h1>
                <p class="subtitle" style="color:#c62828;"><?= htmlspecialchars($error) ?></p>
                <p class="bottom-text"><a href="<?= htmlspecialchars($base) ?>/pharmacist/login">Go to pharmacist login</a></p>
            <?php elseif ($status === 'rejected'): ?>
                <h1>Request Rejected</h1>
                <p class="subtitle">Your request was rejected by admin.</p>
                <p class="subtitle">Reason: <?= htmlspecialchars((string)($request['note'] ?? 'No reason provided')) ?></p>
                <p class="bottom-text"><a href="<?= htmlspecialchars($base) ?>/pharmacist/register">Submit new request</a></p>
            <?php else: ?>
                <h1>Approval Pending</h1>
                <p class="subtitle">Your pharmacist registration request has been sent to admin.</p>
                <p class="subtitle">Please wait until your account is approved.</p>
                <p class="bottom-text">Request ID: #<?= (int)($request['id'] ?? 0) ?></p>
                <p class="bottom-text">This page auto-refreshes every 15 seconds.</p>
            <?php endif; ?>
        </div>
    </section>
    <aside class="auth-visual-panel">
        <div class="visual-card">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/counselor-login.png" alt="Pending approval">
            <h2>Admin Approval Required</h2>
            <p>Access will be enabled immediately after approval.</p>
        </div>
    </aside>
</div>
<?php if (!$error && $status === 'pending'): ?>
<script>
setTimeout(() => { window.location.reload(); }, 15000);
</script>
<?php endif; ?>
</body>
</html>
