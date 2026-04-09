<?php
/**
 * Guardian Profile Layout
 * Based on: guardian-profile.jsp
 */
$g = $data['guardian'];
$base = APP_BASE ?: '';
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Medora Guardian</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/profile.css?v=<?= $cssVer ?>">
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<main class="main-content">
    <div class="profile-container wrapper">
        <div class="card-panel">
            <header class="profile-header">
                <h2>Account Settings</h2>
                <p>Manage your guardian profile and contact information.</p>
            </header>

            <form action="<?= htmlspecialchars($base) ?>/guardian/profile" method="post" class="profile-form">
                <div class="form-grid">
                    <div class="field-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($g['name']) ?>" required>
                    </div>
                    <div class="field-group">
                        <label>NIC (Read-only)</label>
                        <input type="text" value="<?= htmlspecialchars($g['nic']) ?>" disabled>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="field-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($g['phone'] ?? '') ?>" required>
                    </div>
                    <div class="field-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($g['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                        <span class="success-msg">&#10003; Profile updated successfully!</span>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</main>

</body>
</html>
