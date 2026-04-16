<?php
require_once __DIR__ . '/../../common/patient.head.php';
require_once __DIR__ . '/../shop.state.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    shopTrackRecentlyViewed($id);
}

Response::json(['ok' => true]);
