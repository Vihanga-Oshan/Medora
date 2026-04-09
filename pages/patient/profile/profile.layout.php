<?php
/**
 * Profile Layout
 * Ported from: profile.jsp
 */
$profile = $data['profile'] ?? [];
$success = $data['success'];
$error   = $data['error'];
$p       = fn(string $k) => htmlspecialchars($profile[$k] ?? '');
$base    = APP_BASE ?: '';
$cssVer  = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage your Medora patient profile">
    <title>My Profile | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/profile.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>

<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">My Profile</h1>
    <p class="section-subtitle">Manage your personal information</p>

    <?php if ($success): ?>
        <div class="alert alert-success">&#10003; Profile updated successfully.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="profile-header">
            <div>
                <h2 class="card-title">Personal Information</h2>
                <p class="card-subtitle">Update your profile details</p>
            </div>
            <div class="avatar" title="<?= $p('name') ?>">
                <?= strtoupper(substr($profile['name'] ?? 'P', 0, 1)) ?>
            </div>
        </div>

        <form class="profile-form" action="<?= htmlspecialchars($base) ?>/patient/profile" method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= $p('name') ?>" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <label for="nic_display">NIC (read-only)</label>
                    <input type="text" id="nic_display" value="<?= $p('nic') ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email_display">Email (read-only)</label>
                    <input type="email" id="email_display" value="<?= $p('email') ?>" disabled>
                    <small>Email cannot be changed</small>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= $p('phone') ?>" placeholder="e.g. 0771234567">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address"
                       value="<?= $p('address') ?>" placeholder="Enter your address">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="allergies">Known Allergies</label>
                    <input type="text" id="allergies" name="allergies"
                           value="<?= $p('allergies') ?>" placeholder="e.g. Penicillin, Pollen">
                </div>
                <div class="form-group">
                    <label for="chronic_issues">Chronic Conditions</label>
                    <input type="text" id="chronic_issues" name="chronic_issues"
                           value="<?= $p('chronic_issues') ?>" placeholder="e.g. Diabetes, Hypertension">
                </div>
            </div>

            <!-- Guardian Link Status -->
            <?php if (!empty($profile['guardian_nic'])): ?>
                <div class="guardian-status">
                    <span class="status-pill status-linked">&#128101; Guardian Linked</span>
                    <span class="guardian-nic"><?= $p('guardian_nic') ?></span>
                </div>
            <?php else: ?>
                <div class="guardian-status">
                    <span class="status-pill status-pending">No Guardian Linked</span>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../common/patient.footer.php'; ?>

</body>
</html>
