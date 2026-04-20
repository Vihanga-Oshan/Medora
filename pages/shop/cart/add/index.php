<?php
require_once ROOT . '/pages/patient/shop/shop.state.php';
require_once ROOT . '/pages/patient/shop/shop.model.php';

if (!Request::isPost()) {
    Response::redirect('/shop');
}

$auth = Auth::getUser();
$role = strtolower((string) ($auth['role'] ?? ''));
if ($role !== 'patient') {
    shopSetFlash('Please log in to add medicines to your cart.', 'error');
    Response::redirect('/patient/login');
}

$id = (int) ($_POST['id'] ?? 0);
$qty = max(1, (int) ($_POST['quantity'] ?? 1));

if ($id <= 0) {
    shopSetFlash('Invalid medicine selected.', 'error');
    Response::redirect('/shop');
}

$medicine = ShopModel::getSelectedBranchMedicineById($id);
if (!$medicine) {
    shopSetFlash('This medicine is not available in your selected branch.', 'error');
    Response::redirect('/shop');
}

$stock = (int) ($medicine['quantity_in_stock'] ?? 0);
if ($stock > 0 && $qty > $stock) {
    $qty = $stock;
}
if ($qty < 1) {
    shopSetFlash('This medicine is currently out of stock.', 'error');
    Response::redirect('/shop');
}

shopAddToCart($id, $qty);
shopTrackRecentlyViewed($id);
shopSetFlash('Added to cart successfully.');
Response::redirect('/patient/shop/cart');
