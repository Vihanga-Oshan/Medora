<?php
/**
 * /admin/pharmacists/add — Add pharmacist handler and view
 */
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../pharmacists.model.php';

$pharmacies = PharmacyContext::getPharmacies();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_pharmacists_add')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        if (PharmacistsModel::create($_POST)) {
            $base = APP_BASE ?: '';
            header('Location: ' . $base . '/admin/pharmacists?msg=added');
            exit;
        }
        $error = "Failed to create pharmacist. Please check the information.";
    }
}

$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pharmacist | Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/add-pharmacist.css">
</head>
<body class="admin-body admin-add-pharmacist-page">
    <main class="form-container">
        <div class="form-card">
            <h2>Add New Pharmacist</h2>
            <p>Create a specialized pharmacist account with license validation.</p>
            
            <?php if (isset($error)): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_pharmacists_add')) ?>">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Dr. John Doe">
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="field">
                    <label>License Number (ID)</label>
                    <input type="number" name="id" required min="1" step="1" placeholder="12345678">
                </div>
                <div class="field">
                    <label>Default Password</label>
                    <input type="password" name="password" required placeholder="Min 8 characters">
                </div>
                <div class="field">
                    <label>Assign Pharmacy</label>
                    <select name="pharmacy_id" required style="height:42px; width:100%; border:1px solid #cfd7e5; border-radius:8px; padding:0 10px;">
                        <option value="">Select pharmacy</option>
                        <?php foreach ($pharmacies as $ph): ?>
                            <option value="<?= (int)$ph['id'] ?>">
                                <?= htmlspecialchars((string)$ph['name']) ?><?= ((int)($ph['is_demo'] ?? 0) === 1 ? ' (Demo)' : '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists" class="btn btn-muted">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
