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
                    <div class="card cart-summary-card">
                        <div class="cart-summary-header">
                            <div>
                                <span class="cart-summary-kicker">Checkout</span>
                                <h3>Order Summary</h3>
                            </div>
                            <div class="cart-summary-chip">Pickup</div>
                        </div>
                        <div class="cart-summary-metrics">
                            <div class="cart-summary-row">
                                <span class="text-muted">Subtotal</span>
                                <span>Rs. <?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <div class="cart-summary-row">
                                <span class="text-muted">Delivery</span>
                                <span>Rs. 0.00</span>
                            </div>
                        </div>
                        <div class="cart-summary-total">
                            <span>Total</span>
                            <span>Rs. <?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <form method="post" class="checkout-form" id="checkoutForm">
                            <div class="checkout-form-grid">
                                <label class="checkout-field">
                                    <span>Billing name</span>
                                    <input type="text" name="billing_name" placeholder="Enter full name" required>
                                </label>
                                <label class="checkout-field">
                                    <span>Phone number</span>
                                    <input type="text" name="billing_phone" placeholder="Enter contact number" required>
                                </label>
                                <label class="checkout-field">
                                    <span>Email</span>
                                    <input type="email" name="billing_email" placeholder="Enter email address" required>
                                </label>
                                <label class="checkout-field">
                                    <span>Collection method</span>
                                    <select name="delivery_method" id="cartDeliveryMethod">
                                        <option value="PICKUP">Pick up from pharmacy</option>
                                    </select>
                                </label>
                                <label class="checkout-field checkout-field-wide" id="cartBillingAddressGroup">
                                    <span>Delivery address</span>
                                    <textarea name="billing_address" id="cartBillingAddress" rows="3"
                                        placeholder="Enter delivery address"></textarea>
                                </label>
                                <label class="checkout-field">
                                    <span>City</span>
                                    <input type="text" name="billing_city" placeholder="Enter city">
                                </label>
                                <label class="checkout-field checkout-field-wide">
                                    <span>Notes for the pharmacy</span>
                                    <textarea name="billing_notes" rows="3" placeholder="Add special instructions"></textarea>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary checkout-submit">Place Order</button>
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
            const addressGroup = document.getElementById('cartBillingAddressGroup');
            if (!method || !address || !addressGroup) return;

            function syncAddress() {
                const delivery = method.value === 'DELIVERY';
                addressGroup.style.display = delivery ? 'flex' : 'none';
                address.required = delivery;
            }

            method.addEventListener('change', syncAddress);
            syncAddress();
        })();
    </script>
</body>

</html>
