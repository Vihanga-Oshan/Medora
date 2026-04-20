<?php
require_once ROOT . '/pages/patient/shop/shop.model.php';
require_once ROOT . '/pages/patient/shop/shop.state.php';

if (PharmacyContext::selectedPharmacyId() <= 0) {
    Response::redirect('/shop/pharmacy-select');
}

$category = trim((string) ($_GET['category'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));
$viewId = (int) ($_GET['view'] ?? 0);
if ($viewId > 0) {
    shopTrackRecentlyViewed($viewId);
}

$recentIds = shopGetRecentlyViewedIds();
$recentlyViewed = ShopModel::getMedicinesByIds($recentIds);
$suggestions = ShopModel::getSuggestionsByViewedCategories($recentIds, 4);

$data = [
    'categories' => ShopModel::getCategories(),
    'medicines' => ShopModel::getMedicines($category, $q),
    'currentCategory' => $category,
    'searchQuery' => $q,
    'recentlyViewed' => $recentlyViewed,
    'suggestions' => $suggestions,
    'selectedPharmacyId' => PharmacyContext::selectedPharmacyId(),
    'flash' => shopPopFlash(),
];
