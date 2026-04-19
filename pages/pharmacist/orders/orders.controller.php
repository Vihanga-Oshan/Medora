<?php
require_once __DIR__ . '/orders.model.php';

if (Request::isPost()) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    $notes = trim((string)($_POST['fulfillment_notes'] ?? ''));
    PharmacistOrdersModel::updateStatus($orderId, $status, $notes);
    Response::redirect('/pharmacist/orders');
}

$data = [
    'orders' => PharmacistOrdersModel::getOrders(),
];
