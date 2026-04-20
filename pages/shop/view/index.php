<?php
require_once ROOT . '/pages/patient/shop/shop.state.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    shopTrackRecentlyViewed($id);
}

Response::json(['ok' => true]);
