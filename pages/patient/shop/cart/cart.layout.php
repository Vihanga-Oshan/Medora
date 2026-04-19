<?php
$base = APP_BASE ?: '';
$cssVer = time();
$items = $data['items'] ?? [];
$cartTotal = (float) ($data['cartTotal'] ?? 0);
$flash = $data['flash'] ?? null;

$toImageUrl = static function (string $rawPath) use ($base): string {
    $img = trim($rawPath);
    if ($img === '') {
        return htmlspecialchars($base) . '/assets/img/logo.png';
    }
    if (preg_match('/^https?:\/\//i', $img)) {
        return $img;
    }
    $img = ltrim(str_replace('\\', '/', $img), '/');
    if (!str_starts_with($img, 'uploads/')) {
        $img = 'uploads/medicines/' . basename($img);
    }
    return htmlspecialchars($base) . '/' . $img;
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/shop-redesign.css?v=<?= $cssVer ?>">
</head>

<body>
    <?php require_once __DIR__ . '/../../common/patient.navbar.php'; ?>

    <main class="container">
        <div class="order-header">
            <div>
                <a href="<?= htmlspecialchars($base) ?>/patient/shop" class="text-muted"
                    style="text-decoration:none;">&larr; Back to Shop</a>
                <h1>Your Shopping Cart</h1>
            </div>
        </div>

        <?php if (!empty($flash['message'])): ?>
            <div class="card"
                style="padding:12px 16px; margin-bottom:16px; border-left:4px solid <?= ($flash['type'] ?? '') === 'error' ? '#dc3545' : '#28a745' ?>;">
                <?= htmlspecialchars((string) $flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="card" style="text-align:center; padding:60px;">
                <h2 class="text-muted">Your cart is empty</h2>
                <p>Looks like you haven't added any medicines yet.</p>
                <a href="<?= htmlspecialchars($base) ?>/patient/shop" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <div class="cart-items-list">
                    <div class="card">
                        <?php foreach ($items as $item): ?>
                            <?php $m = $item['medicine']; ?>
                            <div class="cart-item">
                                <img src="<?= $toImageUrl((string) ($m['image_path'] ?? '')) ?>"
                                    alt="<?= htmlspecialchars((string) ($m['name'] ?? 'Medicine')) ?>" class="cart-item-img">
                                <div class="cart-item-info">
                                    <h4><?= htmlspecialchars((string) ($m['name'] ?? 'Medicine')) ?></h4>
                                    <p class="text-muted"><?= htmlspecialchars((string) ($m['generic_name'] ?? '')) ?></p>
                                    <div class="cart-item-price">Rs. <?= number_format((float) ($m['price'] ?? 0), 2) ?></div>
                                </div>
                                <div class="cart-item-qty">
                                    <span class="text-muted">Qty:</span>
                                    <strong><?= (int) $item['quantity'] ?></strong>
                                </div>
                                <a href="<?= htmlspecialchars($base) ?>/patient/shop/cart/remove?id=<?= (int) ($m['id'] ?? 0) ?>"
                                    class="cart-item-remove">Remove</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="cart-summary">
                    <div class="card">
                        <h3 style="margin-top:0;">Order Summary</h3>
                        <div class="cart-summary-row">
                            <span class="text-muted">Subtotal</span>
                            <span>Rs. <?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <div class="cart-summary-row">
                            <span class="text-muted">Delivery</span>
                            <span>Rs. 0.00</span>
                        </div>
                        <div class="cart-summary-total">
                            <span>Total</span>
                            <span>Rs. <?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <form method="post" style="margin-top:14px;" id="checkoutForm">
                            <div style="display:grid; gap:12px;">
                                <input type="text" name="billing_name" placeholder="Billing name" required>
                                <input type="text" name="billing_phone" placeholder="Phone number" required>
                                <input type="email" name="billing_email" placeholder="Email" required>
                                <select name="delivery_method" id="cartDeliveryMethod">
                                    <option value="PICKUP">Pick up from pharmacy</option>
                                </select>
                                <textarea name="billing_address" id="cartBillingAddress" rows="3"
                                    placeholder="Delivery address"></textarea>
                                <input type="text" name="billing_city" placeholder="City">
                                <textarea name="billing_notes" rows="3" placeholder="Notes for the pharmacy"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"
                                style="width:100%; box-sizing:border-box; margin-top:14px;">Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script>
        (function () {
            const method = document.getElementById('cartDeliveryMethod');
            const address = document.getElementById('cartBillingAddress');
            if (!method || !address) return;

            function syncAddress() {
                const delivery = method.value === 'DELIVERY';
                address.style.display = delivery ? 'block' : 'none';
                address.required = delivery;
            }

            method.addEventListener('change', syncAddress);
            syncAddress();
        })();
    </script>
</body>

</html>