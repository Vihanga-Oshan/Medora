<?php
$base = APP_BASE ?: '';
$cssVer = time();
$orders = $data['orders'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Medora Pharmacy</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/shop-redesign.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>
<?php require_once __DIR__ . '/../../common/patient.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">My Orders</h1>
    <p class="section-subtitle">Track your submitted medicine requests</p>

    <div class="checkout-card">
        <?php if (empty($orders)): ?>
            <p>No orders found yet.</p>
            <a href="<?= htmlspecialchars($base) ?>/patient/shop" class="checkout-btn" style="display:inline-block; text-decoration:none; margin-top: 12px;">Browse Medicines</a>
        <?php else: ?>
            <div class="order-list">
                <?php foreach ($orders as $o): ?>
                    <div class="order-card">
                        <div>
                            <h4><?= htmlspecialchars($o['file_name'] ?? ('Order #' . (int)$o['id'])) ?></h4>
                            <p>Placed: <?= htmlspecialchars(date('M d, Y H:i', strtotime((string)($o['uploaded_at'] ?? 'now')))) ?></p>
                        </div>
                        <span class="status-pill status-<?= strtolower((string)($o['status'] ?? 'pending')) ?>">
                            <?= htmlspecialchars((string)($o['status'] ?? 'PENDING')) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top: 16px;">
        <a href="<?= htmlspecialchars($base) ?>/patient/shop" style="text-decoration:none;">&larr; Back to e-shop</a>
    </div>
</main>

<?php require_once __DIR__ . '/../../common/patient.footer.php'; ?>
</body>
</html>

