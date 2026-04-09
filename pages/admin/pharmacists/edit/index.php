<?php
/**
 * /admin/pharmacists/edit — Edit pharmacist handler and view
 */
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../pharmacists.model.php';

$id = (int)($_GET['id'] ?? $_POST['current_id'] ?? 0);
if (!$id) {
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/admin/pharmacists');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null, 'admin_pharmacists_edit')) {
        $error = "Security validation failed. Please refresh and try again.";
    } else {
        if (PharmacistsModel::update($id, $_POST)) {
            $base = APP_BASE ?: '';
            header('Location: ' . $base . '/admin/pharmacists?msg=updated');
            exit;
        }
        $error = "Failed to update pharmacist. Please try again.";
    }
}

$ph = PharmacistsModel::getById($id);
if (!$ph) {
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/admin/pharmacists');
    exit;
}

$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pharmacist | Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/add-pharmacist.css">
</head>
<body class="admin-body admin-add-pharmacist-page">
    <main class="form-container">
        <div class="form-card">
            <h2>Edit Pharmacist Details</h2>
            <p>Update account information for <?= htmlspecialchars($ph['name']) ?></p>
            
            <?php if (isset($error)): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_pharmacists_edit')) ?>">
                <input type="hidden" name="current_id" value="<?= $ph['id'] ?>">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($ph['name']) ?>" required>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($ph['email']) ?>" required>
                </div>
                <div class="field">
                    <label>License Number (ID)</label>
                    <input type="number" name="id" value="<?= htmlspecialchars((string)$ph['id']) ?>" required min="1" step="1">
                </div>
                <div class="field">
                    <label>Update Password (leave blank if unchanged)</label>
                    <input type="password" name="password" placeholder="New password">
                </div>
                
                <div class="form-actions">
                    <a href="<?= htmlspecialchars($base) ?>/admin/pharmacists" class="btn btn-muted">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Updates</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
