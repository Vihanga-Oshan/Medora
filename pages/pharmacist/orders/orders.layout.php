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
$statusOptions = [
    'PREPARING',
    'READY_FOR_PICKUP',
    'COMPLETED',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Orders | Medora</title>
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
                    <span class="greeting-icon">&#128230;</span>
                    <div>
                        <span class="greeting-text">Medicine Orders</span>
                        <span class="date-time">Track prescription and e-shop medicine fulfillment</span>
                    </div>
                </div>
            </header>

            <section class="inventory-section">
                <div class="section-header">
                    <div>
                        <h2>Order Fulfillment Queue</h2>
                        <p>Review billing details, delivery preference, and current order progress.</p>
                    </div>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/orders-history"
                        class="add-btn"><span>&#128340;</span> View Completed Orders</a>
                </div>

                <?php if (empty($orders)): ?>
                    <section class="panel-card">
                        <p class="empty-msg">No medicine orders are waiting right now.</p>
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
                                    <span
                                        class="panel-chip"><?= htmlspecialchars((string) ($order['status'] ?? 'PENDING')) ?></span>
                                </div>

                                <div class="dashboard-inventory-stats">
                                    <article class="dashboard-inventory-stat">
                                        <span class="highlight-kicker">Delivery</span>
                                        <strong><?= htmlspecialchars(ucwords(strtolower((string) ($order['delivery_method'] ?? 'PICKUP')))) ?></strong>
                                        <small><?= htmlspecialchars((string) ($order['billing_city'] ?? '')) ?></small>
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

                                <form method="post" class="filters-bar">
                                    <input type="hidden" name="order_id" value="<?= (int) ($order['id'] ?? 0) ?>">
                                    <select name="status" class="filter-select">
                                        <?php foreach ($statusOptions as $status): ?>
                                            <option value="<?= htmlspecialchars($status) ?>" <?= strtoupper((string) ($order['status'] ?? '')) === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="fulfillment_notes" class="filter-select" style="min-width:280px;"
                                        placeholder="Fulfillment notes"
                                        value="<?= htmlspecialchars((string) ($order['fulfillment_notes'] ?? '')) ?>">
                                    <button type="submit" class="filter-btn">Update Order</button>
                                </form>

                                <div class="mini-list">
                                    <article class="mini-row">
                                        <div>
                                            <strong>Billing Contact</strong>
                                            <small><?= htmlspecialchars((string) ($order['billing_name'] ?? 'Not provided')) ?> â€¢
                                                <?= htmlspecialchars((string) ($order['billing_email'] ?? 'No email')) ?></small>
                                        </div>
                                        <div class="mini-meta">
                                            <small><?= htmlspecialchars((string) ($order['billing_address'] ?? 'Pickup - no address provided')) ?></small>
                                        </div>
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
