<?php
$base = APP_BASE ?: '';
$orders = $data['orders'] ?? [];
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isOrders = str_contains($currentPath, '/pharmacist/orders');
$isOrderHistory = str_contains($currentPath, '/pharmacist/orders-history');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Orders | Medora</title>
    <link rel="stylesheet"
        href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css?v=<?= time() ?>">
    <link rel="stylesheet"
        href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/medicine-inventory.css?v=<?= time() ?>">
</head>

<body>
    <div class="container">
        <?php require_once __DIR__ . '/../common/pharmacist.sidebar.php'; ?>

        <main class="main-content">
            <header class="header">
                <div class="user-info">
                    <img src="<?= htmlspecialchars($base) ?>/assets/img/avatar.png" alt="User Avatar" class="avatar">
                    <span class="user-role"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
                </div>
                <div class="greeting">
                    <span class="greeting-icon">&#128340;</span>
                    <div>
                        <span class="greeting-text">Completed Orders</span>
                        <span class="date-time">Track previously completed medicine orders</span>
                    </div>
                </div>
            </header>

            <section class="inventory-section">
                <div class="section-header">
                    <div>
                        <h2>Completed Orders Archive</h2>
                        <p>Read-only history of completed pharmacy orders.</p>
                    </div>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/orders" class="add-btn"><span>&larr;</span> Back
                        to Active Orders</a>
                </div>

                <?php if (empty($orders)): ?>
                    <section class="panel-card">
                        <p class="empty-msg">No completed orders yet.</p>
                    </section>
                <?php else: ?>
                    <div class="movement-list">
                        <?php foreach ($orders as $order): ?>
                            <section class="panel-card">
                                <div class="panel-head">
                                    <div>
                                        <h3><?= htmlspecialchars((string) ($order['order_title'] ?? 'Medicine order')) ?></h3>
                                        <p class="panel-description">
                                            <?= htmlspecialchars((string) ($order['patient_name'] ?? $order['patient_nic'] ?? 'Patient')) ?>
                                            â€¢ <?= htmlspecialchars((string) ($order['source'] ?? 'ORDER')) ?></p>
                                    </div>
                                    <span class="panel-chip">COMPLETED</span>
                                </div>

                                <div class="dashboard-inventory-stats">
                                    <article class="dashboard-inventory-stat">
                                        <span class="highlight-kicker">Completed At</span>
                                        <strong><?= htmlspecialchars(date('Y-m-d H:i', strtotime((string) ($order['updated_at'] ?? $order['created_at'] ?? 'now')))) ?></strong>
                                        <small>Order #<?= (int) ($order['id'] ?? 0) ?></small>
                                    </article>
                                    <article class="dashboard-inventory-stat">
                                        <span class="highlight-kicker">Items</span>
                                        <strong><?= (int) ($order['item_count'] ?? 0) ?></strong>
                                        <small><?= !empty($order['prescription_id']) ? 'Prescription-linked order' : 'E-shop order' ?></small>
                                    </article>
                                    <article class="dashboard-inventory-stat">
                                        <span class="highlight-kicker">Total</span>
                                        <strong>Rs. <?= number_format((float) ($order['total_amount'] ?? 0), 2) ?></strong>
                                        <small><?= htmlspecialchars((string) ($order['billing_phone'] ?? 'No phone')) ?></small>
                                    </article>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>

</html>
