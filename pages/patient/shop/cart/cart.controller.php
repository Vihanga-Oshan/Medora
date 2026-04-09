<?php
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

$data = [
    'items' => $items,
    'cartTotal' => $cartTotal,
    'cartCount' => shopCartCount(),
    'flash' => shopPopFlash(),
];

