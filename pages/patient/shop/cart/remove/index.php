<?php
require_once __DIR__ . '/../../../common/patient.head.php';
require_once __DIR__ . '/../../shop.state.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id > 0) {
    shopRemoveFromCart($id);
    shopSetFlash('Item removed from cart.');
}

Response::redirect('/patient/shop/cart');

