<?php
require_once ROOT . '/core/PharmacyOrderSupport.php';

$formData = [
    'delivery_method' => 'PICKUP',
    'billing_name' => (string)($user['name'] ?? ''),
    'billing_phone' => '',
    'billing_email' => (string)($user['email'] ?? ''),
    'billing_address' => '',
    'billing_city' => '',
    'billing_notes' => '',
];

$cart = shopGetCart();
$medicineIds = array_map('intval', array_keys($cart));
$medicines = ShopModel::getMedicinesByIds($medicineIds);

$byId = [];
foreach ($medicines as $m) {
    $id = (int)($m['id'] ?? 0);
    if ($id > 0) {
        $byId[$id] = $m;
    }
}

$items = [];
$cartTotal = 0.0;

foreach ($cart as $idStr => $qtyRaw) {
    $id = (int)$idStr;
    $qty = max(1, (int)$qtyRaw);
    if (!isset($byId[$id])) {
        continue;
    }
    $medicine = $byId[$id];
    $price = (float)($medicine['price'] ?? 0);
    $lineTotal = $price * $qty;
    $cartTotal += $lineTotal;

    $items[] = [
        'medicine' => $medicine,
        'quantity' => $qty,
        'lineTotal' => $lineTotal,
    ];
}

if (Request::isPost()) {
    $formData = [
        'delivery_method' => PharmacyOrderSupport::normalizeDeliveryMethod((string)($_POST['delivery_method'] ?? 'PICKUP')),
        'billing_name' => trim((string)($_POST['billing_name'] ?? '')),
        'billing_phone' => trim((string)($_POST['billing_phone'] ?? '')),
        'billing_email' => trim((string)($_POST['billing_email'] ?? '')),
        'billing_address' => trim((string)($_POST['billing_address'] ?? '')),
        'billing_city' => trim((string)($_POST['billing_city'] ?? '')),
        'billing_notes' => trim((string)($_POST['billing_notes'] ?? '')),
    ];

    if (empty($items)) {
        shopSetFlash('Your cart is empty.', 'error');
        Response::redirect('/patient/shop/cart');
    }

    $billing = PharmacyOrderSupport::sanitizeBillingData($formData);
    $billingError = PharmacyOrderSupport::validateBillingData($billing, true);
    $selectedPharmacyId = (int)(($items[0]['medicine']['pharmacy_id'] ?? 0));

    if ($billingError !== null) {
        $flash = ['message' => $billingError, 'type' => 'error'];
    } elseif ($selectedPharmacyId <= 0) {
        $flash = ['message' => 'Please select a pharmacy branch before checking out.', 'type' => 'error'];
    } else {
        $saved = PharmacyOrderSupport::createShopOrder((string)$user['nic'], $selectedPharmacyId, $billing, $items);
        if ($saved) {
            shopClearCart();
            shopSetFlash('Your medicine order was placed successfully.');
            Response::redirect('/patient/shop/orders');
        }
        $flash = ['message' => 'Unable to place your order right now. Please try again.', 'type' => 'error'];
    }
} else {
    $flash = shopPopFlash();
}

$data = [
    'items' => $items,
    'cartTotal' => $cartTotal,
    'cartCount' => shopCartCount(),
    'flash' => $flash,
    'formData' => $formData,
];
