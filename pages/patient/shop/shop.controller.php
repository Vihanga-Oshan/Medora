<?php
require_once __DIR__ . '/shop.model.php';
require_once __DIR__ . '/shop.state.php';

$category = trim($_GET['category'] ?? '');
$q = trim($_GET['q'] ?? '');
$viewId = (int)($_GET['view'] ?? 0);
if ($viewId > 0) {
    shopTrackRecentlyViewed($viewId);
}

$recentIds = shopGetRecentlyViewedIds();
$recentlyViewed = ShopModel::getMedicinesByIds($recentIds);
$suggestions = ShopModel::getSuggestions(6, $recentIds);

$data = [
    'categories' => ShopModel::getCategories(),
    'medicines' => ShopModel::getMedicines($category, $q),
    'currentCategory' => $category,
    'searchQuery' => $q,
    'recentlyViewed' => $recentlyViewed,
    'suggestions' => $suggestions,
    'cartCount' => shopCartCount(),
    'flash' => shopPopFlash(),
];
