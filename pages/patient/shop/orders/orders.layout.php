<?php
$base = APP_BASE ?: '';
$cssVer = time();
$orders = $data['orders'] ?? [];

$totalOrders = count($orders);
$activeStatuses = ['AWAITING_PRESCRIPTION_APPROVAL', 'PENDING_FULFILLMENT', 'PREPARING', 'READY_FOR_PICKUP', 'OUT_FOR_DELIVERY'];
$activeCount = 0;
foreach ($orders as $row) {
    $status = strtoupper((string) ($row['status'] ?? ''));
    if (in_array($status, $activeStatuses, true)) {
        $activeCount++;
    }
}

$statusClass = static function (string $status): string {
    return match (strtoupper($status)) {
        'COMPLETED' => 'status-completed',
        'CANCELLED' => 'status-cancelled',
        'READY_FOR_PICKUP' => 'status-ready',
        'OUT_FOR_DELIVERY' => 'status-delivery',
        default => 'status-progress',
    };
};

$formatStatus = static function (string $status): string {
    return ucwords(strtolower(str_replace('_', ' ', $status)));
};
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
    <style>
        .orders-shell {
            background: #ffffff;
            border: 1px solid #e4e9f0;
            border-radius: 14px;
            padding: 20px;
        }

        .orders-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .orders-summary .summary-card {
            border: 1px solid #e4e9f0;
            border-radius: 12px;
            background: #f8fbff;
            padding: 14px;
        }

        .orders-summary .summary-label {
            display: block;
            font-size: 0.82rem;
            color: #556173;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .orders-summary .summary-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1d2a3b;
        }

        .orders-list {
            display: grid;
            grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
            gap: 14px;
        }

        .orders-list-panel {
            border: 1px solid #e4e9f0;
            border-radius: 12px;
            background: #ffffff;
            padding: 10px;
            max-height: 560px;
            overflow-y: auto;
        }

        .orders-list-item {
            width: 100%;
            text-align: left;
            border: 1px solid #e4e9f0;
            border-radius: 10px;
            background: #ffffff;
            padding: 10px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .orders-list-item:last-child {
            margin-bottom: 0;
        }

        .orders-list-item.is-active {
            border-color: #c8dafc;
            background: #f4f8ff;
        }

        .orders-list-item-title {
            margin: 0 0 6px;
            color: #142033;
            font-size: 0.98rem;
            font-weight: 700;
        }

        .orders-list-item-meta {
            margin: 0;
            font-size: 0.86rem;
            color: #5f6d7f;
        }

        .orders-details-panel {
            border: 1px solid #e4e9f0;
            border-radius: 12px;
            padding: 14px;
            background: #ffffff;
        }

        .orders-item-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
        }

        .orders-item-title {
            margin: 0;
            font-size: 1.05rem;
            color: #142033;
        }

        .orders-item-meta {
            margin: 4px 0 0;
            color: #5f6d7f;
            font-size: 0.92rem;
        }

        .orders-status {
            font-size: 0.82rem;
            font-weight: 700;
            border-radius: 999px;
            padding: 6px 10px;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .status-progress {
            background: #eef4ff;
            color: #2a62d5;
            border-color: #d7e5ff;
        }

        .status-ready {
            background: #eefbf5;
            color: #1f8f57;
            border-color: #d4f2e3;
        }

        .status-delivery {
            background: #fff8eb;
            color: #b96a00;
            border-color: #ffe6bf;
        }

        .status-completed {
            background: #e8f7ef;
            color: #1e7a4a;
            border-color: #cdecd9;
        }

        .status-cancelled {
            background: #fff1f2;
            color: #b5303d;
            border-color: #ffd8dc;
        }

        .orders-item-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 6px;
        }

        .orders-kv {
            background: #f8fbff;
            border: 1px solid #e4e9f0;
            border-radius: 10px;
            padding: 10px;
        }

        .orders-kv-label {
            display: block;
            color: #5f6d7f;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .orders-kv-value {
            color: #182437;
            font-weight: 600;
            font-size: 0.96rem;
        }

        .orders-empty {
            text-align: center;
            padding: 28px 12px;
        }

        .orders-empty p {
            color: #5f6d7f;
            margin-bottom: 12px;
        }

        .orders-actions {
            margin-top: 14px;
        }

        @media (max-width: 840px) {
            .orders-list {
                grid-template-columns: 1fr;
            }

            .orders-list-panel {
                max-height: 320px;
            }

            .orders-item-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../../common/patient.navbar.php'; ?>

    <main class="container">
        <h1 class="section-title">My Orders</h1>
        <p class="section-subtitle">Track prescription and e-shop medicine requests in one place</p>

        <div class="orders-shell">
            <div class="orders-summary">
                <article class="summary-card">
                    <span class="summary-label">Total Orders</span>
                    <strong class="summary-value"><?= (int) $totalOrders ?></strong>
                </article>
                <article class="summary-card">
                    <span class="summary-label">Active Orders</span>
                    <strong class="summary-value"><?= (int) $activeCount ?></strong>
                </article>
            </div>

            <?php if (empty($orders)): ?>
                <section class="orders-empty">
                    <p>No orders found yet.</p>
                    <a href="<?= htmlspecialchars($base) ?>/patient/shop" class="checkout-btn"
                        style="display:inline-block; text-decoration:none;">Browse Medicines</a>
                </section>
            <?php else: ?>
                <div class="orders-list">
                    <div class="orders-list-panel" id="ordersListPanel">
                        <?php foreach ($orders as $idx => $o): ?>
                            <?php
                            $status = strtoupper((string) ($o['status'] ?? 'PENDING'));
                            $source = strtoupper((string) ($o['source'] ?? 'ORDER'));
                            $title = (string) ($o['order_title'] ?? ('Order #' . (int) $o['id']));
                            $placedAt = date('M d, Y h:i A', strtotime((string) ($o['created_at'] ?? 'now')));
                            $delivery = ucwords(strtolower((string) ($o['delivery_method'] ?? 'PICKUP')));
                            $total = 'Rs. ' . number_format((float) ($o['total_amount'] ?? 0), 2);
                            ?>
                            <button type="button" class="orders-list-item <?= $idx === 0 ? 'is-active' : '' ?>"
                                data-order-id="<?= (int) ($o['id'] ?? 0) ?>" data-title="<?= htmlspecialchars($title) ?>"
                                data-source="<?= htmlspecialchars($source) ?>"
                                data-status="<?= htmlspecialchars($formatStatus($status)) ?>"
                                data-status-class="<?= htmlspecialchars($statusClass($status)) ?>"
                                data-placed-at="<?= htmlspecialchars($placedAt) ?>"
                                data-delivery="<?= htmlspecialchars($delivery) ?>" data-total="<?= htmlspecialchars($total) ?>"
                                data-schedule="<?= !empty($o['wants_schedule']) ? 'Yes' : 'No' ?>">
                                <h2 class="orders-list-item-title"><?= htmlspecialchars($title) ?></h2>
                                <p class="orders-list-item-meta">Order #<?= (int) ($o['id'] ?? 0) ?> •
                                    <?= htmlspecialchars($source) ?></p>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    $first = $orders[0] ?? [];
                    $firstStatus = strtoupper((string) ($first['status'] ?? 'PENDING'));
                    $firstSource = strtoupper((string) ($first['source'] ?? 'ORDER'));
                    $firstTitle = (string) ($first['order_title'] ?? ('Order #' . (int) ($first['id'] ?? 0)));
                    $firstPlacedAt = date('M d, Y h:i A', strtotime((string) ($first['created_at'] ?? 'now')));
                    $firstDelivery = ucwords(strtolower((string) ($first['delivery_method'] ?? 'PICKUP')));
                    $firstTotal = 'Rs. ' . number_format((float) ($first['total_amount'] ?? 0), 2);
                    ?>
                    <article class="orders-details-panel" id="ordersDetailsPanel">
                        <div class="orders-item-head">
                            <div>
                                <h2 class="orders-item-title" id="orderDetailTitle"><?= htmlspecialchars($firstTitle) ?>
                                </h2>
                                <p class="orders-item-meta" id="orderDetailMeta">Order #<?= (int) ($first['id'] ?? 0) ?> •
                                    <?= htmlspecialchars($firstSource) ?></p>
                            </div>
                            <span class="orders-status <?= htmlspecialchars($statusClass($firstStatus)) ?>"
                                id="orderDetailStatus">
                                <?= htmlspecialchars($formatStatus($firstStatus)) ?>
                            </span>
                        </div>

                        <div class="orders-item-grid">
                            <div class="orders-kv">
                                <span class="orders-kv-label">Placed At</span>
                                <div class="orders-kv-value" id="orderDetailPlacedAt">
                                    <?= htmlspecialchars($firstPlacedAt) ?></div>
                            </div>
                            <div class="orders-kv">
                                <span class="orders-kv-label">Delivery</span>
                                <div class="orders-kv-value" id="orderDetailDelivery">
                                    <?= htmlspecialchars($firstDelivery) ?></div>
                            </div>
                            <div class="orders-kv">
                                <span class="orders-kv-label">Total</span>
                                <div class="orders-kv-value" id="orderDetailTotal"><?= htmlspecialchars($firstTotal) ?>
                                </div>
                            </div>
                            <div class="orders-kv">
                                <span class="orders-kv-label">Schedule Requested</span>
                                <div class="orders-kv-value" id="orderDetailSchedule">
                                    <?= !empty($first['wants_schedule']) ? 'Yes' : 'No' ?></div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endif; ?>
        </div>

        <div class="orders-actions">
            <a href="<?= htmlspecialchars($base) ?>/patient/shop" style="text-decoration:none;">&larr; Back to
                e-shop</a>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../common/patient.footer.php'; ?>
    <?php if (!empty($orders)): ?>
        <script>
            (function () {
                const items = document.querySelectorAll('.orders-list-item');
                const titleEl = document.getElementById('orderDetailTitle');
                const metaEl = document.getElementById('orderDetailMeta');
                const statusEl = document.getElementById('orderDetailStatus');
                const placedAtEl = document.getElementById('orderDetailPlacedAt');
                const deliveryEl = document.getElementById('orderDetailDelivery');
                const totalEl = document.getElementById('orderDetailTotal');
                const scheduleEl = document.getElementById('orderDetailSchedule');

                if (!items.length) {
                    return;
                }

                items.forEach(function (item) {
                    item.addEventListener('click', function () {
                        items.forEach(function (row) {
                            row.classList.remove('is-active');
                        });
                        item.classList.add('is-active');

                        const orderId = item.getAttribute('data-order-id') || '0';
                        const title = item.getAttribute('data-title') || '';
                        const source = item.getAttribute('data-source') || 'ORDER';
                        const status = item.getAttribute('data-status') || 'Pending';
                        const statusClass = item.getAttribute('data-status-class') || 'status-progress';
                        const placedAt = item.getAttribute('data-placed-at') || '';
                        const delivery = item.getAttribute('data-delivery') || 'Pickup';
                        const total = item.getAttribute('data-total') || 'Rs. 0.00';
                        const schedule = item.getAttribute('data-schedule') || 'No';

                        titleEl.textContent = title;
                        metaEl.textContent = 'Order #' + orderId + ' • ' + source;
                        statusEl.textContent = status;
                        statusEl.className = 'orders-status ' + statusClass;
                        placedAtEl.textContent = placedAt;
                        deliveryEl.textContent = delivery;
                        totalEl.textContent = total;
                        scheduleEl.textContent = schedule;
                    });
                });
            })();
        </script>
    <?php endif; ?>
</body>

</html>