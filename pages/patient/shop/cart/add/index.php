<?php
require_once __DIR__ . '/../../../common/patient.head.php';
require_once __DIR__ . '/../../shop.state.php';
require_once __DIR__ . '/../../shop.model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::redirect('/patient/shop');
}

$id = (int)($_POST['id'] ?? 0);
$qty = max(1, (int)($_POST['quantity'] ?? 1));
$returnTo = trim((string)($_POST['return_to'] ?? ''));

if ($id <= 0) {
    shopSetFlash('Invalid medicine selected.', 'error');
    Response::redirect('/patient/shop');
}

$medicine = ShopModel::getSelectedBranchMedicineById($id);
if (!$medicine) {
    shopSetFlash('This medicine is not available in your selected branch.', 'error');
    Response::redirect('/patient/shop');
}

$stock = (int)($medicine['quantity_in_stock'] ?? 0);
if ($stock > 0 && $qty > $stock) {
    $qty = $stock;
}
if ($qty < 1) {
    shopSetFlash('This medicine is currently out of stock.', 'error');
    Response::redirect('/patient/shop');
}

shopAddToCart($id, $qty);
shopTrackRecentlyViewed($id);
shopSetFlash('Added to cart successfully.');

if ($returnTo === 'cart') {
    Response::redirect('/patient/shop/cart');
}
Response::redirect('/patient/shop');
