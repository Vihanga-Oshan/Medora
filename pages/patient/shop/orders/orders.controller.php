<?php
require_once __DIR__ . '/../shop.model.php';

$data = [
    'orders' => ShopModel::getOrderHistory($user['nic']),
];

