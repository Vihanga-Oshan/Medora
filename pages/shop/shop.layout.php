<?php
$base = APP_BASE ?: '';
$cssVer = time();
$categories = $data['categories'] ?? [];
$medicines = $data['medicines'] ?? [];
$currentCategory = (string) ($data['currentCategory'] ?? '');
$searchQuery = (string) ($data['searchQuery'] ?? '');
$recentlyViewed = $data['recentlyViewed'] ?? [];
$suggestions = $data['suggestions'] ?? [];
$flash = $data['flash'] ?? null;

$toImageUrl = static function (string $rawPath) use ($base): string {
    $fallback = htmlspecialchars($base) . '/assets/img/logo.png';
    $img = trim($rawPath);
    if ($img === '') {
        return $fallback;
    }
    if (preg_match('/^https?:\/\//i', $img)) {
        return $img;
    }

    $img = str_replace('\\', '/', $img);
    $img = preg_replace('#^[A-Za-z]:/#', '', $img) ?? $img;
    if (str_contains($img, '/public/uploads/')) {
        $img = substr($img, strpos($img, '/public/uploads/') + 8);
    }
    $img = ltrim($img, '/');
    if (!str_starts_with($img, 'uploads/')) {
        $img = 'uploads/medicines/' . basename($img);
    }

    if (defined('ROOT')) {
        $abs = ROOT . '/public/' . ltrim($img, '/');
        if (!is_file($abs)) {
            return $fallback;
        }
    }

    return htmlspecialchars($base) . '/' . ltrim($img, '/');
};

$resolveMedicineText = static function (array $row): array {
    $brandName = trim((string) ($row['name'] ?? ''));
    $medName = trim((string) ($row['med_name'] ?? ''));
    $genericName = trim((string) ($row['generic_name'] ?? ''));
    $strength = trim((string) ($row['strength'] ?? ''));
    $dosageForm = trim((string) ($row['dosage_form'] ?? ''));

    $medicineName = $medName !== '' ? $medName : ($brandName !== '' ? $brandName : $genericName);
    if ($medicineName === '') {
        $medicineName = 'Medicine';
    }

    $genericAndStrength = trim(trim($genericName . ' ' . $strength));
    $genericLine = $genericAndStrength;
    if ($dosageForm !== '') {
        $genericLine = $genericLine !== '' ? ($genericLine . ' · ' . $dosageForm) : $dosageForm;
    }

    $brandLabel = ($brandName !== '' && strcasecmp($brandName, $medicineName) !== 0) ? $brandName : '';
    return [$medicineName, $brandLabel, $genericLine];
};

$buildStockMessage = static function (int $selectedStock, string $availableBranchName): string {
    if ($selectedStock > 0) {
        return '';
    }

    if ($availableBranchName !== '') {
        return 'Available at ' . $availableBranchName . '.';
    }

    return 'Currently unavailable in your selected branch. Please check again later or choose another pharmacy.';
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medora E-shop</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/shop-modern.css?v=<?= $cssVer ?>">
</head>

<body>
    <header
        style="padding:16px 24px; background:#fff; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; gap:10px;">
        <a href="<?= htmlspecialchars($base) ?>/"
            style="font-weight:700; text-decoration:none; color:#0f172a;">Medora</a>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="<?= htmlspecialchars($base) ?>/shop/pharmacy-select"
                style="text-decoration:none; color:#1d4ed8; font-weight:600;">Change Pharmacy</a>
            <a href="<?= htmlspecialchars($base) ?>/patient/login"
                style="text-decoration:none; background:#1d4ed8; color:#fff; padding:8px 14px; border-radius:8px; font-weight:600;">Patient
                Login</a>
        </div>
    </header>

    <div class="shop-layout">
        <aside class="shop-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-title">Categories</div>
                <ul class="category-list">
                    <li><a href="<?= htmlspecialchars($base) ?>/shop"
                            class="<?= $currentCategory === '' ? 'active' : '' ?>">All Medicines</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= htmlspecialchars($base) ?>/shop?category=<?= urlencode((string) $cat) ?>"
                                class="<?= $currentCategory === $cat ? 'active' : '' ?>">
                                <?= htmlspecialchars((string) $cat) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-card">
                <div class="sidebar-title">How Checkout Works</div>
                <p style="margin:0; color:#64748b; font-size:0.92rem; line-height:1.5;">
                    Browse as a guest, choose your branch, then log in as a patient when adding to cart.
                </p>
            </div>
        </aside>

        <main class="shop-main">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; gap:10px;">
                <form action="<?= htmlspecialchars($base) ?>/shop" method="get" class="search-container">
                    <?php if ($currentCategory !== ''): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
                    <?php endif; ?>
                    <input type="text" name="q" placeholder="Search product"
                        value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit">&#128269;</button>
                </form>
            </div>

            <?php if (!empty($flash['message'])): ?>
                <div
                    style="background:#fff7ed; border:1px solid #fdba74; color:#9a3412; border-radius:12px; padding:12px 14px; margin-bottom:16px;">
                    <?= htmlspecialchars((string) $flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="medicine-grid">
                <?php foreach ($medicines as $med): ?>
                    <?php
                    $id = (int) ($med['id'] ?? 0);
                    $cartId = (int) ($med['cart_id'] ?? 0);
                    [$medicineName, $brandLabel, $genericLine] = $resolveMedicineText($med);
                    $selectedStock = max(0, (int) ($med['quantity_in_stock'] ?? 0));
                    $availableBranchName = trim((string) ($med['available_branch_name'] ?? ''));
                    $stockMessage = $buildStockMessage($selectedStock, $availableBranchName);
                    $payload = [
                        'id' => $id,
                        'cartId' => $cartId,
                        'name' => $medicineName,
                        'brandName' => $brandLabel,
                        'category' => (string) ($med['category'] ?? 'General'),
                        'generic' => (string) ($med['generic_name'] ?? ''),
                        'dosage' => (string) ($med['dosage_form'] ?? ''),
                        'strength' => (string) ($med['strength'] ?? ''),
                        'manufacturer' => (string) ($med['manufacturer'] ?? ''),
                        'description' => (string) ($med['description'] ?? 'No description available.'),
                        'price' => (float) ($med['price'] ?? 0),
                        'stock' => $selectedStock,
                        'stockMessage' => $stockMessage,
                        'image' => $toImageUrl((string) ($med['image_path'] ?? '')),
                    ];
                    ?>
                    <div class="medicine-card med-clickable"
                        data-medicine='<?= htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
                        <div class="card-img-wrapper">
                            <img src="<?= $toImageUrl((string) ($med['image_path'] ?? '')) ?>"
                                alt="<?= htmlspecialchars($medicineName) ?>">
                        </div>
                        <div class="card-body">
                            <h3 class="med-title med-title-main"><?= htmlspecialchars($medicineName) ?></h3>
                            <p class="med-subtitle med-generic-line">
                                <?= htmlspecialchars($genericLine !== '' ? $genericLine : '-') ?></p>
                            <p class="med-subtitle med-category-line">
                                <?= htmlspecialchars((string) ($med['category'] ?? 'General')) ?></p>
                            <div class="price-container med-price-row">
                                <div class="price-tag">Rs. <?= number_format((float) ($med['price'] ?? 0), 2) ?></div>
                                <div class="med-stock-inline <?= $selectedStock > 0 ? 'in' : 'out' ?>">
                                    <?= $selectedStock > 0 ? 'In Stock' : 'Out of Stock' ?></div>
                            </div>
                            <?php if ($stockMessage !== ''): ?>
                                <div class="med-stock-message"><?= htmlspecialchars($stockMessage) ?></div>
                            <?php endif; ?>
                            <form method="post" action="<?= htmlspecialchars($base) ?>/shop/cart/add"
                                onsubmit="event.stopPropagation();">
                                <input type="hidden" name="id" value="<?= $cartId ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-add-cart"
                                    style="width:100%; border-radius:8px; gap:8px;<?= $selectedStock <= 0 ? 'opacity:.5;cursor:not-allowed;' : '' ?>"
                                    <?= $selectedStock <= 0 ? 'disabled' : '' ?>>
                                    <span>&#10010;</span> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($medicines)): ?>
                <div style="text-align:center; padding:50px; color:#666;">
                    <div style="font-size:3em; margin-bottom:20px; color:#ddd;">&#128230;</div>
                    <p>No medicines found.</p>
                    <a href="<?= htmlspecialchars($base) ?>/shop" style="color:#007dca; text-decoration:none;">Clear
                        Filters</a>
                </div>
            <?php endif; ?>
        </main>

        <aside class="right-panel">
            <div class="widget-card">
                <div class="widget-title">Recently Viewed</div>
                <?php if (!empty($recentlyViewed)): ?>
                    <?php foreach ($recentlyViewed as $item): ?>
                        <?php [$widgetMedicineName] = $resolveMedicineText($item); ?>
                        <div class="widget-item med-clickable" data-medicine='<?= htmlspecialchars(json_encode([
                            'id' => (int) ($item['id'] ?? 0),
                            'cartId' => (int) ($item['cart_id'] ?? 0),
                            'name' => $widgetMedicineName,
                            'category' => (string) ($item['category'] ?? 'General'),
                            'description' => (string) ($item['description'] ?? 'No description available.'),
                            'price' => (float) ($item['price'] ?? 0),
                            'stock' => max(0, (int) ($item['quantity_in_stock'] ?? 0)),
                            'stockMessage' => $buildStockMessage(max(0, (int) ($item['quantity_in_stock'] ?? 0)), (string) ($item['available_branch_name'] ?? '')),
                            'image' => $toImageUrl((string) ($item['image_path'] ?? '')),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
                            <img src="<?= $toImageUrl((string) ($item['image_path'] ?? '')) ?>"
                                alt="<?= htmlspecialchars($widgetMedicineName) ?>">
                            <div class="widget-info">
                                <h4><?= htmlspecialchars($widgetMedicineName) ?></h4>
                                <p><?= htmlspecialchars((string) ($item['category'] ?? 'General')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size:0.85rem; color:#94a3b8; margin:0;">No items viewed yet.</p>
                <?php endif; ?>
            </div>

            <div class="widget-card">
                <div class="widget-title">Suggestions</div>
                <?php foreach ($suggestions as $item): ?>
                    <?php [$suggestMedicineName] = $resolveMedicineText($item); ?>
                    <div class="widget-item">
                        <img src="<?= $toImageUrl((string) ($item['image_path'] ?? '')) ?>"
                            alt="<?= htmlspecialchars($suggestMedicineName) ?>">
                        <div class="widget-info">
                            <h4><?= htmlspecialchars($suggestMedicineName) ?></h4>
                            <p><?= htmlspecialchars((string) ($item['category'] ?? 'General')) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>

    <script>
        (function () {
            const basePath = <?= json_encode($base) ?>;
            document.querySelectorAll('.med-clickable').forEach((el) => {
                el.addEventListener('click', (e) => {
                    if (e.target.closest('form') || e.target.closest('button') || e.target.closest('a')) {
                        return;
                    }
                    try {
                        const payload = JSON.parse(el.getAttribute('data-medicine') || '{}');
                        if (payload && payload.id) {
                            fetch(basePath + '/shop/view?id=' + encodeURIComponent(payload.id), { credentials: 'same-origin' }).catch(() => { });
                        }
                    } catch (_) { }
                });
            });
        })();
    </script>
</body>

</html>