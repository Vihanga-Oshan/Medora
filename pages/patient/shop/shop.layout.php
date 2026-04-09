<?php
$base = APP_BASE ?: '';
$cssVer = time();
$categories = $data['categories'] ?? [];
$medicines = $data['medicines'] ?? [];
$currentCategory = $data['currentCategory'] ?? '';
$searchQuery = $data['searchQuery'] ?? '';
$recentlyViewed = $data['recentlyViewed'] ?? [];
$suggestions = $data['suggestions'] ?? [];
$cartCount = (int)($data['cartCount'] ?? 0);
$flash = $data['flash'] ?? null;

$toImageUrl = static function (string $rawPath) use ($base): string {
    $img = trim($rawPath);
    if ($img === '') {
        return htmlspecialchars($base) . '/assets/img/logo.png';
    }
    if (preg_match('/^https?:\/\//i', $img)) {
        return $img;
    }
    $img = ltrim(str_replace('\\', '/', $img), '/');
    if (!str_starts_with($img, 'uploads/')) {
        $img = 'uploads/medicines/' . basename($img);
    }
    return htmlspecialchars($base) . '/' . $img;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medora Pharmacy | Smart Catalog</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/shop-modern.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
    <style>
        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 24px; }
        .modal-overlay.open { display: flex; }
        .medicine-modal { width: min(960px, 100%); background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 24px 60px rgba(2, 6, 23, 0.25); }
        .medicine-modal-body { display: grid; grid-template-columns: 360px 1fr; }
        .medicine-modal-image { min-height: 320px; background: #f8fbff; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .medicine-modal-image img { width: 100%; max-height: 300px; object-fit: contain; }
        .medicine-modal-details { padding: 26px; }
        .medicine-modal-close { position: absolute; right: 16px; top: 14px; background: #f1f5f9; border: none; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; font-size: 20px; color: #334155; }
        .medicine-modal-top { position: relative; }
        .modal-desc { color: #475569; margin: 10px 0 18px; line-height: 1.5; max-height: 90px; overflow: auto; }
        .modal-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .modal-badge { background: #f1f5f9; color: #475569; border-radius: 999px; padding: 6px 10px; font-size: 12px; font-weight: 600; }
        .modal-stock { font-size: 13px; font-weight: 700; margin-top: 10px; }
        .modal-stock.in { color: #0f9d58; }
        .modal-stock.out { color: #dc2626; }
        .modal-actions { display: flex; align-items: end; gap: 12px; margin-top: 18px; }
        .modal-actions input[type="number"] { width: 90px; height: 44px; border: 1px solid #cbd5e1; border-radius: 10px; text-align: center; font-size: 15px; }
        .quick-add-form { width: 100%; }
        .med-clickable { cursor: pointer; }
        @media (max-width: 1000px) { .medicine-modal-body { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<div class="shop-layout">
    <aside class="shop-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-title">Categories</div>
            <ul class="category-list">
                <li>
                    <a href="<?= htmlspecialchars($base) ?>/patient/shop" class="<?= $currentCategory === '' ? 'active' : '' ?>">All Medicines</a>
                </li>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= htmlspecialchars($base) ?>/patient/shop?category=<?= urlencode((string)$cat) ?>" class="<?= $currentCategory === $cat ? 'active' : '' ?>">
                            <?= htmlspecialchars((string)$cat) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="sidebar-card">
            <div class="sidebar-title">Quick Links</div>
            <ul class="category-list">
                <li><a href="<?= htmlspecialchars($base) ?>/patient/shop/cart">&#128722; Cart</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/patient/shop/orders">&#128230; My Orders</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/patient/dashboard">&larr; Back to Dashboard</a></li>
            </ul>
        </div>
    </aside>

    <main class="shop-main">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <form action="<?= htmlspecialchars($base) ?>/patient/shop" method="get" class="search-container">
                <?php if ($currentCategory !== ''): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
                <?php endif; ?>
                <input type="text" name="q" placeholder="Search product" value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit">&#128269;</button>
            </form>
            <div class="header-actions">
                <a href="<?= htmlspecialchars($base) ?>/patient/shop/cart" class="cart-link">
                    <span class="icon-span">&#128722;</span>
                    <span>Cart</span>
                    <?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
                </a>
                <a href="<?= htmlspecialchars($base) ?>/patient/shop/orders" class="cart-link">
                    <span class="icon-span">&#128221;</span>
                    <span>Orders</span>
                </a>
            </div>
        </div>

        <?php if (!empty($flash['message'])): ?>
            <div style="background:#f0fdf4; border:1px solid #86efac; color:#166534; border-radius:12px; padding:12px 14px; margin-bottom:16px;">
                <?= htmlspecialchars((string)$flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="shop-hero">
            <div class="main-promo-card">
                <div class="promo-tag">Biggest Offer Revealed</div>
                <h1>MORE DEALS INSIDE<br>UP TO 50% OFF</h1>
                <p>Premium medical essentials for every household.</p>
            </div>
            <div class="sub-promo-card" style="background:#fdf2f2;">
                <div>
                    <div class="promo-tag" style="background: rgba(0,0,0,0.05); color:#333;">NEW ARRIVAL</div>
                    <h3>First Aid Essentials</h3>
                    <p>Complete kits for emergencies.</p>
                </div>
                <div class="price-box">UP TO 30% OFF</div>
            </div>
            <div class="sub-promo-card" style="background:#f0f7ff;">
                <div>
                    <div class="promo-tag" style="background: rgba(0,0,0,0.05); color:#333;">SUGGESTION</div>
                    <h3>Vitamins & Energy</h3>
                    <p>Boost your daily vitality.</p>
                </div>
                <div class="price-box">Starting from Rs. 1500</div>
            </div>
        </section>

        <div class="medicine-grid">
            <?php foreach ($medicines as $med): ?>
                <?php
                $id = (int)($med['id'] ?? 0);
                $name = (string)($med['name'] ?? 'Medicine');
                $payload = [
                    'id' => $id,
                    'name' => $name,
                    'category' => (string)($med['category'] ?? 'General'),
                    'generic' => (string)($med['generic_name'] ?? ''),
                    'dosage' => (string)($med['dosage_form'] ?? ''),
                    'strength' => (string)($med['strength'] ?? ''),
                    'manufacturer' => (string)($med['manufacturer'] ?? ''),
                    'description' => (string)($med['description'] ?? 'No description available.'),
                    'price' => (float)($med['price'] ?? 0),
                    'stock' => max(0, (int)($med['quantity_in_stock'] ?? 0)),
                    'sellingUnit' => (string)($med['selling_unit'] ?? 'Item'),
                    'unitQty' => (int)($med['unit_quantity'] ?? 1),
                    'image' => $toImageUrl((string)($med['image_path'] ?? '')),
                ];
                ?>
                <div class="medicine-card med-clickable" data-medicine='<?= htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
                    <div class="card-img-wrapper">
                        <img src="<?= $toImageUrl((string)($med['image_path'] ?? '')) ?>" alt="<?= htmlspecialchars($name) ?>">
                    </div>
                    <div class="card-body">
                        <div class="med-category"><?= htmlspecialchars((string)($med['category'] ?? 'General')) ?></div>
                        <h3 class="med-title"><?= htmlspecialchars($name) ?></h3>
                        <div class="price-container">
                            <div class="price-tag">Rs. <?= number_format((float)($med['price'] ?? 0), 2) ?></div>
                        </div>
                        <form method="post" action="<?= htmlspecialchars($base) ?>/patient/shop/cart/add" class="quick-add-form" onsubmit="event.stopPropagation();">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-add-cart" style="width:100%; border-radius:8px; gap:8px; margin-top:15px;">
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
                <a href="<?= htmlspecialchars($base) ?>/patient/shop" style="color:#007dca; text-decoration:none;">Clear Filters</a>
            </div>
        <?php endif; ?>
    </main>

    <aside class="right-panel">
        <div class="widget-card">
            <div class="widget-title">Recently Viewed</div>
            <?php if (!empty($recentlyViewed)): ?>
                <?php foreach ($recentlyViewed as $item): ?>
                    <div class="widget-item med-clickable" data-medicine='<?= htmlspecialchars(json_encode([
                        'id' => (int)($item['id'] ?? 0),
                        'name' => (string)($item['name'] ?? 'Medicine'),
                        'category' => (string)($item['category'] ?? 'General'),
                        'generic' => (string)($item['generic_name'] ?? ''),
                        'dosage' => (string)($item['dosage_form'] ?? ''),
                        'strength' => (string)($item['strength'] ?? ''),
                        'manufacturer' => (string)($item['manufacturer'] ?? ''),
                        'description' => (string)($item['description'] ?? 'No description available.'),
                        'price' => (float)($item['price'] ?? 0),
                        'stock' => max(0, (int)($item['quantity_in_stock'] ?? 0)),
                        'sellingUnit' => (string)($item['selling_unit'] ?? 'Item'),
                        'unitQty' => (int)($item['unit_quantity'] ?? 1),
                        'image' => $toImageUrl((string)($item['image_path'] ?? '')),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
                        <img src="<?= $toImageUrl((string)($item['image_path'] ?? '')) ?>" alt="<?= htmlspecialchars((string)($item['name'] ?? 'Medicine')) ?>">
                        <div class="widget-info">
                            <h4><?= htmlspecialchars((string)($item['name'] ?? 'Medicine')) ?></h4>
                            <p><?= htmlspecialchars((string)($item['category'] ?? 'General')) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size:0.85rem; color:#94a3b8; margin:0;">No items viewed yet.</p>
            <?php endif; ?>
        </div>

        <div class="widget-card">
            <div class="widget-title">Suggestions for You</div>
            <?php foreach ($suggestions as $item): ?>
                <div class="widget-item med-clickable" data-medicine='<?= htmlspecialchars(json_encode([
                    'id' => (int)($item['id'] ?? 0),
                    'name' => (string)($item['name'] ?? 'Medicine'),
                    'category' => (string)($item['category'] ?? 'General'),
                    'generic' => (string)($item['generic_name'] ?? ''),
                    'dosage' => (string)($item['dosage_form'] ?? ''),
                    'strength' => (string)($item['strength'] ?? ''),
                    'manufacturer' => (string)($item['manufacturer'] ?? ''),
                    'description' => (string)($item['description'] ?? 'No description available.'),
                    'price' => (float)($item['price'] ?? 0),
                    'stock' => max(0, (int)($item['quantity_in_stock'] ?? 0)),
                    'sellingUnit' => (string)($item['selling_unit'] ?? 'Item'),
                    'unitQty' => (int)($item['unit_quantity'] ?? 1),
                    'image' => $toImageUrl((string)($item['image_path'] ?? '')),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
                    <img src="<?= $toImageUrl((string)($item['image_path'] ?? '')) ?>" alt="<?= htmlspecialchars((string)($item['name'] ?? 'Medicine')) ?>">
                    <div class="widget-info">
                        <h4><?= htmlspecialchars((string)($item['name'] ?? 'Medicine')) ?></h4>
                        <p><?= htmlspecialchars((string)($item['category'] ?? 'General')) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($suggestions)): ?>
                <p style="font-size:0.85rem; color:#94a3b8; margin:0;">No suggestions available.</p>
            <?php endif; ?>
        </div>
    </aside>
</div>

<div class="modal-overlay" id="medicineModal" aria-hidden="true">
    <div class="medicine-modal">
        <div class="medicine-modal-top">
            <button class="medicine-modal-close" id="closeMedicineModal" type="button">&times;</button>
        </div>
        <div class="medicine-modal-body">
            <div class="medicine-modal-image"><img id="modalImage" src="" alt="Medicine"></div>
            <div class="medicine-modal-details">
                <div class="med-category" id="modalCategory">General</div>
                <h2 class="med-title" id="modalName" style="margin-bottom:6px;"></h2>
                <div style="color:#64748b; margin-bottom:8px;" id="modalGeneric"></div>
                <div class="modal-badges" id="modalBadges"></div>
                <div class="modal-desc" id="modalDescription"></div>
                <div style="font-weight:800; font-size:1.6rem; color:#0f172a;">Rs. <span id="modalPrice">0.00</span></div>
                <div class="modal-stock" id="modalStock"></div>

                <form method="post" action="<?= htmlspecialchars($base) ?>/patient/shop/cart/add" id="modalAddForm" class="modal-actions">
                    <input type="hidden" name="id" id="modalMedicineId" value="0">
                    <input type="number" name="quantity" id="modalQty" min="1" value="1">
                    <button type="submit" class="btn-add-cart" id="modalAddBtn"><span>&#10010;</span> Add to Cart</button>
                    <input type="hidden" name="return_to" value="cart">
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../common/patient.footer.php'; ?>
<script>
(function(){
    const basePath = <?= json_encode($base) ?>;
    const modal = document.getElementById('medicineModal');
    const closeBtn = document.getElementById('closeMedicineModal');
    const modalImage = document.getElementById('modalImage');
    const modalCategory = document.getElementById('modalCategory');
    const modalName = document.getElementById('modalName');
    const modalGeneric = document.getElementById('modalGeneric');
    const modalBadges = document.getElementById('modalBadges');
    const modalDescription = document.getElementById('modalDescription');
    const modalPrice = document.getElementById('modalPrice');
    const modalStock = document.getElementById('modalStock');
    const modalMedicineId = document.getElementById('modalMedicineId');
    const modalQty = document.getElementById('modalQty');
    const modalAddBtn = document.getElementById('modalAddBtn');

    function openModalFromPayload(payload) {
        if (!payload || !payload.id) return;
        fetch(basePath + '/patient/shop/view?id=' + encodeURIComponent(payload.id), { credentials: 'same-origin' }).catch(() => {});
        modalImage.src = payload.image || '<?= htmlspecialchars($base) ?>/assets/img/logo.png';
        modalCategory.textContent = payload.category || 'General';
        modalName.textContent = payload.name || 'Medicine';
        modalGeneric.textContent = payload.generic ? ('Generic: ' + payload.generic) : '';
        modalDescription.textContent = payload.description || 'No description available.';
        modalPrice.textContent = Number(payload.price || 0).toFixed(2);

        modalBadges.innerHTML = '';
        [payload.dosage, payload.strength, payload.manufacturer].filter(Boolean).forEach((txt) => {
            const chip = document.createElement('span');
            chip.className = 'modal-badge';
            chip.textContent = txt;
            modalBadges.appendChild(chip);
        });

        const stock = Math.max(0, parseInt(payload.stock || 0, 10));
        modalStock.className = 'modal-stock ' + (stock > 0 ? 'in' : 'out');
        modalStock.textContent = stock > 0 ? ('In stock: ' + stock) : 'Out of stock';

        modalMedicineId.value = String(payload.id);
        modalQty.value = '1';
        modalQty.max = stock > 0 ? String(stock) : '1';
        modalQty.disabled = stock <= 0;
        modalAddBtn.disabled = stock <= 0;
        modalAddBtn.style.opacity = stock <= 0 ? '0.5' : '1';

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    document.querySelectorAll('.med-clickable').forEach((el) => {
        el.addEventListener('click', (e) => {
            if (e.target.closest('form') || e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            try {
                const payload = JSON.parse(el.getAttribute('data-medicine') || '{}');
                openModalFromPayload(payload);
            } catch (_) {}
        });
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        }
    });
})();
</script>
</body>
</html>
