<?php
$medicines = $data['medicines'] ?? [];
$search = $data['search'] ?? '';
$statusFilter = $data['status'] ?? 'all';
$supplierFilterId = (int) ($data['supplier_id'] ?? 0);
$categoryFilterId = (int) ($data['category_id'] ?? 0);
$sortBy = (string) ($data['sort_by'] ?? 'stock');
$sortDir = (string) ($data['sort_dir'] ?? 'asc');
$page = (int) ($data['page'] ?? 1);
$perPage = (int) ($data['per_page'] ?? 15);
$total = (int) ($data['total'] ?? 0);
$totalPages = (int) ($data['total_pages'] ?? 1);
$from = (int) ($data['from'] ?? 0);
$to = (int) ($data['to'] ?? 0);
$summary = $data['summary'] ?? [];
$suppliers = $data['suppliers'] ?? [];
$supplierFilters = $data['supplier_filters'] ?? [];
$categoryFilters = $data['category_filters'] ?? [];
$reorders = $data['reorders'] ?? [];
$movements = $data['movements'] ?? [];
$base = APP_BASE ?: '';
$cssVer = time();
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
$statusCode = (string) ($_GET['status'] ?? '');

$queryState = [
    'search' => $search,
    'status_filter' => $statusFilter,
    'supplier_id' => $supplierFilterId,
    'category_id' => $categoryFilterId,
    'sort_by' => $sortBy,
    'sort_dir' => $sortDir,
    'per_page' => $perPage,
    'page' => $page,
];

$buildInventoryUrl = function (array $overrides = []) use ($queryState, $base): string {
    $query = array_merge($queryState, $overrides);
    if ((int) ($query['supplier_id'] ?? 0) <= 0) {
        unset($query['supplier_id']);
    }
    if ((int) ($query['category_id'] ?? 0) <= 0) {
        unset($query['category_id']);
    }
    if ((int) ($query['page'] ?? 1) <= 1) {
        unset($query['page']);
    }
    if ((int) ($query['per_page'] ?? 15) === 15) {
        unset($query['per_page']);
    }
    if ((string) ($query['sort_by'] ?? 'stock') === 'stock') {
        unset($query['sort_by']);
    }
    if ((string) ($query['sort_dir'] ?? 'asc') === 'asc') {
        unset($query['sort_dir']);
    }
    if ((string) ($query['status_filter'] ?? 'all') === 'all') {
        unset($query['status_filter']);
    }
    if (trim((string) ($query['search'] ?? '')) === '') {
        unset($query['search']);
    }

    $qs = http_build_query($query);
    return htmlspecialchars($base . '/pharmacist/inventory' . ($qs !== '' ? ('?' . $qs) : ''));
};

$sortArrow = function (string $key) use ($sortBy, $sortDir): string {
    if ($sortBy !== $key) {
        return '<>';
    }
    return strtolower($sortDir) === 'desc' ? 'v' : '^';
};

$sortLink = function (string $key) use ($sortBy, $sortDir, $buildInventoryUrl): string {
    $nextDir = ($sortBy === $key && strtolower($sortDir) === 'asc') ? 'desc' : 'asc';
    return $buildInventoryUrl([
        'sort_by' => $key,
        'sort_dir' => $nextDir,
        'page' => 1,
    ]);
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Stock Management | Medora</title>
    <link rel="stylesheet"
        href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css?v=<?= $cssVer ?>">
    <link rel="stylesheet"
        href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/medicine-inventory.css?v=<?= $cssVer ?>">
</head>

<body>
    <div class="container">
        <?php require_once __DIR__ . '/../common/pharmacist.sidebar.php'; ?>

        <main class="main-content">
            <header class="header">
                <div class="user-info">
                    <img src="<?= htmlspecialchars($base) ?>/assets/img/avatar.png" alt="User Avatar" class="avatar">
                    <span class="user-role"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
                </div>
                <div class="greeting">
                    <span class="greeting-icon">&#128230;</span>
                    <div>
                        <span class="greeting-text">Medicine Stock Management</span>
                        <span class="date-time">Low stock alerts, suppliers, movements, and inventory control</span>
                    </div>
                </div>
            </header>

            <section class="inventory-section">
                <div class="section-header">
                    <div>
                        <h2>Inventory Control Center</h2>
                        <p>Track medicine availability, supplier coverage, and stock health in one place.</p>
                    </div>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/inventory/add" class="add-btn"><span>+</span> Add
                        Medicine</a>
                </div>

                <?php if ($statusCode === 'deleted'): ?>
                    <div class="alert alert-success">Medicine deleted successfully.</div>
                <?php elseif ($statusCode === 'added'): ?>
                    <div class="alert alert-success">Medicine added with supplier and stock settings.</div>
                <?php elseif ($statusCode === 'updated'): ?>
                    <div class="alert alert-success">Medicine stock settings updated successfully.</div>
                <?php elseif ($statusCode === 'stock_updated'): ?>
                    <div class="alert alert-success">Stock quantity updated and movement recorded.</div>
                <?php elseif ($statusCode === 'error'): ?>
                    <div class="alert alert-error"><?= htmlspecialchars((string) ($_GET['msg'] ?? 'Operation failed')) ?>
                    </div>
                <?php endif; ?>

                <div class="summary-grid">
                    <article class="summary-card accent-blue">
                        <span class="summary-label">Medicines</span>
                        <strong><?= (int) ($summary['total_items'] ?? 0) ?></strong>
                        <small><?= (int) ($summary['total_units'] ?? 0) ?> units currently available</small>
                    </article>
                    <article class="summary-card accent-amber">
                        <span class="summary-label">Low Stock</span>
                        <strong><?= (int) ($summary['low_stock_count'] ?? 0) ?></strong>
                        <small>Needs reorder attention</small>
                    </article>
                    <article class="summary-card accent-red">
                        <span class="summary-label">Out of Stock</span>
                        <strong><?= (int) ($summary['out_of_stock_count'] ?? 0) ?></strong>
                        <small>Unavailable for dispensing</small>
                    </article>
                    <article class="summary-card accent-purple">
                        <span class="summary-label">Expiring Soon</span>
                        <strong><?= (int) ($summary['expiring_soon_count'] ?? 0) ?></strong>
                        <small>Within the next 30 days</small>
                    </article>
                    <article class="summary-card accent-green">
                        <span class="summary-label">Inventory Value</span>
                        <strong>Rs. <?= number_format((float) ($summary['total_stock_value'] ?? 0), 2) ?></strong>
                        <small><?= (int) ($summary['supplier_count'] ?? 0) ?> active suppliers</small>
                    </article>
                </div>

                <div class="insight-grid">
                    <section class="panel-card">
                        <div class="panel-head">
                            <h3>Reorder Recommendations</h3>
                            <span class="panel-chip">Action Queue</span>
                        </div>
                        <?php if (empty($reorders)): ?>
                            <p class="empty-msg">No low-stock medicines right now.</p>
                        <?php else: ?>
                            <div class="mini-list">
                                <?php foreach ($reorders as $m): ?>
                                    <?php $displayName = trim((string) ($m['med_name'] ?? '')) !== '' ? (string) $m['med_name'] : (string) ($m['name'] ?? ''); ?>
                                    <article class="mini-row">
                                        <div>
                                            <strong><?= htmlspecialchars($displayName) ?></strong>
                                            <small><?= htmlspecialchars((string) ($m['supplier_name'] ?? 'No supplier')) ?></small>
                                        </div>
                                        <div class="mini-meta">
                                            <span class="status-badge low">Reorder
                                                <?= (int) ($m['recommended_reorder_quantity'] ?? 0) ?></span>
                                            <small>On hand <?= (int) ($m['quantity_in_stock'] ?? 0) ?>, est. Rs.
                                                <?= number_format((float) ($m['estimated_reorder_cost'] ?? 0), 2) ?></small>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="panel-card">
                        <div class="panel-head">
                            <h3>Supplier Snapshot</h3>
                            <span class="panel-chip">Contacts</span>
                        </div>
                        <?php if (empty($suppliers)): ?>
                            <p class="empty-msg">Suppliers will appear here once medicines are linked to them.</p>
                        <?php else: ?>
                            <div class="supplier-list">
                                <?php foreach ($suppliers as $supplier): ?>
                                    <article class="supplier-card">
                                        <strong><?= htmlspecialchars((string) ($supplier['name'] ?? 'Supplier')) ?></strong>
                                        <small><?= htmlspecialchars((string) ($supplier['contact_person'] ?? 'No contact person')) ?></small>
                                        <small><?= htmlspecialchars((string) ($supplier['phone'] ?? 'No phone')) ?></small>
                                        <small><?= (int) ($supplier['medicine_count'] ?? 0) ?> medicines,
                                            <?= (int) ($supplier['stocked_units'] ?? 0) ?> units</small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <form method="get" class="filters-bar">
                    <div class="search-box">
                        <span>&#128269;</span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            placeholder="Search medicine, generic, category, or supplier">
                    </div>
                    <select name="status_filter" class="filter-select">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All stock states</option>
                        <option value="low" <?= $statusFilter === 'low' ? 'selected' : '' ?>>Low stock</option>
                        <option value="out" <?= $statusFilter === 'out' ? 'selected' : '' ?>>Out of stock</option>
                        <option value="expiring" <?= $statusFilter === 'expiring' ? 'selected' : '' ?>>Expiring soon
                        </option>
                        <option value="healthy" <?= $statusFilter === 'healthy' ? 'selected' : '' ?>>Healthy stock</option>
                    </select>
                    <select name="supplier_id" class="filter-select">
                        <option value="0">All suppliers</option>
                        <?php foreach ($supplierFilters as $s): ?>
                            <option value="<?= (int) ($s['id'] ?? 0) ?>" <?= ((int) ($s['id'] ?? 0) === $supplierFilterId) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($s['name'] ?? 'Supplier')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="category_id" class="filter-select">
                        <option value="0">All categories</option>
                        <?php foreach ($categoryFilters as $c): ?>
                            <option value="<?= (int) ($c['id'] ?? 0) ?>" <?= ((int) ($c['id'] ?? 0) === $categoryFilterId) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['name'] ?? 'Category')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="sort_by" class="filter-select">
                        <option value="stock" <?= $sortBy === 'stock' ? 'selected' : '' ?>>Sort: Stock</option>
                        <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Sort: Name</option>
                        <option value="value" <?= $sortBy === 'value' ? 'selected' : '' ?>>Sort: Value</option>
                        <option value="expiry" <?= $sortBy === 'expiry' ? 'selected' : '' ?>>Sort: Expiry</option>
                        <option value="updated" <?= $sortBy === 'updated' ? 'selected' : '' ?>>Sort: Updated</option>
                        <option value="supplier" <?= $sortBy === 'supplier' ? 'selected' : '' ?>>Sort: Supplier</option>
                    </select>
                    <select name="sort_dir" class="filter-select">
                        <option value="asc" <?= strtolower($sortDir) === 'asc' ? 'selected' : '' ?>>Ascending</option>
                        <option value="desc" <?= strtolower($sortDir) === 'desc' ? 'selected' : '' ?>>Descending</option>
                    </select>
                    <select name="per_page" class="filter-select">
                        <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
                        <option value="15" <?= $perPage === 15 ? 'selected' : '' ?>>15 / page</option>
                        <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
                        <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
                    </select>
                    <input type="hidden" name="page" value="1">
                    <button type="submit" class="filter-btn">Apply</button>
                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/inventory"
                        class="action-link secondary">Clear</a>
                </form>

                <div class="table-meta">
                    <span>Showing <?= $from ?>-<?= $to ?> of <?= $total ?> medicines</span>
                    <span>Page <?= $page ?> of <?= max(1, $totalPages) ?></span>
                </div>

                <div class="table-shell">
                    <table class="data-table" id="inventoryTable">
                        <thead>
                            <tr>
                                <th><a class="sort-link" href="<?= $sortLink('name') ?>">Medicine
                                        <?= htmlspecialchars($sortArrow('name')) ?></a></th>
                                <th><a class="sort-link" href="<?= $sortLink('supplier') ?>">Supplier
                                        <?= htmlspecialchars($sortArrow('supplier')) ?></a></th>
                                <th><a class="sort-link" href="<?= $sortLink('stock') ?>">Stock Status
                                        <?= htmlspecialchars($sortArrow('stock')) ?></a></th>
                                <th><a class="sort-link" href="<?= $sortLink('value') ?>">Pricing
                                        <?= htmlspecialchars($sortArrow('value')) ?></a></th>
                                <th><a class="sort-link" href="<?= $sortLink('expiry') ?>">Expiry
                                        <?= htmlspecialchars($sortArrow('expiry')) ?></a></th>
                                <th>Batch</th>
                                <th><a class="sort-link" href="<?= $sortLink('updated') ?>">Actions
                                        <?= htmlspecialchars($sortArrow('updated')) ?></a></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($medicines)): ?>
                                <tr>
                                    <td colspan="7" class="empty-msg">No medicines found for the current filter.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($medicines as $m): ?>
                                    <?php
                                    $displayName = trim((string) ($m['med_name'] ?? '')) !== '' ? (string) $m['med_name'] : (string) ($m['name'] ?? '');
                                    $genericName = trim((string) ($m['generic_name'] ?? ''));
                                    $supplierName = trim((string) ($m['supplier_name'] ?? ''));
                                    ?>
                                    <tr>
                                        <td class="med-name-cell">
                                            <strong class="med-title"><?= htmlspecialchars($displayName) ?></strong>
                                            <div class="med-subtext">
                                                <?= htmlspecialchars($genericName !== '' ? $genericName : (string) ($m['strength'] ?? '')) ?>
                                            </div>
                                            <div class="med-meta">
                                                <?= htmlspecialchars((string) ($m['category'] ?? 'Uncategorized')) ?> |
                                                <?= htmlspecialchars((string) ($m['dosage_form'] ?? 'Form not set')) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($supplierName !== '' ? $supplierName : 'Supplier missing') ?></strong>
                                            <div class="med-subtext">
                                                <?= htmlspecialchars((string) ($m['supplier_phone'] ?? 'No phone')) ?>
                                            </div>
                                            <div class="med-meta">Lead time: <?= (int) ($m['supplier_lead_time_days'] ?? 0) ?>
                                                day(s)</div>
                                        </td>
                                        <td>
                                            <span
                                                class="status-badge <?= htmlspecialchars((string) ($m['stock_status_key'] ?? 'healthy')) ?>"><?= htmlspecialchars((string) ($m['stock_status_label'] ?? 'Healthy')) ?></span>
                                            <div class="med-subtext">On hand: <?= (int) ($m['quantity_in_stock'] ?? 0) ?>
                                                <?= htmlspecialchars((string) ($m['selling_unit'] ?? 'units')) ?>
                                            </div>
                                            <div class="med-meta">Low stock at <?= (int) ($m['low_stock_threshold'] ?? 0) ?>,
                                                reorder <?= (int) ($m['reorder_quantity'] ?? 0) ?></div>
                                        </td>
                                        <td>
                                            <strong>Sell: Rs. <?= number_format((float) ($m['price'] ?? 0), 2) ?></strong>
                                            <div class="med-subtext">Cost: Rs.
                                                <?= number_format((float) ($m['unit_cost'] ?? 0), 2) ?>
                                            </div>
                                            <div class="med-meta">Value: Rs.
                                                <?= number_format((float) ($m['total_stock_value'] ?? 0), 2) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars((string) ($m['expiry_date'] ?? 'Not set')) ?></strong>
                                            <div class="med-subtext">
                                                <?php if (($m['days_to_expiry'] ?? null) === null): ?>
                                                    No expiry tracked
                                                <?php elseif ((int) $m['days_to_expiry'] < 0): ?>
                                                    Expired
                                                <?php else: ?>
                                                    <?= (int) $m['days_to_expiry'] ?> day(s) left
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars((string) ($m['batch_number'] ?? 'Not set')) ?></strong>
                                            <div class="med-subtext">Restocked:
                                                <?= htmlspecialchars((string) ($m['last_restocked_at'] ?? 'Not recorded')) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-stack">
                                                <button class="action-link" type="button"
                                                    onclick="openStockModal(<?= (int) ($m['id'] ?? 0) ?>, '<?= htmlspecialchars($displayName, ENT_QUOTES) ?>')">Adjust
                                                    Stock</button>
                                                <a class="action-link secondary"
                                                    href="<?= htmlspecialchars($base) ?>/pharmacist/inventory/edit?id=<?= (int) ($m['id'] ?? 0) ?>">Edit</a>
                                                <button class="action-link danger" type="button"
                                                    onclick="confirmDelete('<?= (int) ($m['id'] ?? 0) ?>')">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Inventory pagination">
                        <a class="page-link <?= $page <= 1 ? 'disabled' : '' ?>"
                            href="<?= $page <= 1 ? '#' : $buildInventoryUrl(['page' => $page - 1]) ?>">Prev</a>
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($p = $start; $p <= $end; $p++):
                            ?>
                            <a class="page-link <?= $p === $page ? 'active' : '' ?>"
                                href="<?= $buildInventoryUrl(['page' => $p]) ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <a class="page-link <?= $page >= $totalPages ? 'disabled' : '' ?>"
                            href="<?= $page >= $totalPages ? '#' : $buildInventoryUrl(['page' => $page + 1]) ?>">Next</a>
                    </nav>
                <?php endif; ?>

                <section class="panel-card movement-panel">
                    <div class="panel-head">
                        <h3>Recent Stock Movements</h3>
                        <span class="panel-chip">History</span>
                    </div>
                    <?php if (empty($movements)): ?>
                        <p class="empty-msg">No stock movements recorded yet.</p>
                    <?php else: ?>
                        <div class="movement-list">
                            <?php foreach ($movements as $move): ?>
                                <?php $moveName = trim((string) ($move['med_name'] ?? '')) !== '' ? (string) $move['med_name'] : (string) ($move['name'] ?? 'Medicine'); ?>
                                <article class="movement-row">
                                    <div>
                                        <strong><?= htmlspecialchars($moveName) ?></strong>
                                        <small><?= htmlspecialchars((string) ($move['supplier_name'] ?? 'Supplier not linked')) ?></small>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars(strtoupper((string) ($move['movement_type'] ?? 'adjustment'))) ?></strong>
                                        <small><?= (int) ($move['quantity_before'] ?? 0) ?> ->
                                            <?= (int) ($move['quantity_after'] ?? 0) ?></small>
                                    </div>
                                    <div>
                                        <strong><?= (int) ($move['quantity_change'] ?? 0) ?></strong>
                                        <small><?= htmlspecialchars((string) ($move['created_at'] ?? '')) ?></small>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>

    <div id="deleteModal" class="modal hidden">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this medicine? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="cancel-btn" onclick="closeModal('deleteModal')">Cancel</button>
                <form id="deleteForm" method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/inventory/delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="delete-btn">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>

    <div id="stockModal" class="modal hidden">
        <div class="modal-content stock-modal-content">
            <h3>Adjust Medicine Stock</h3>
            <p id="stockModalMedicine">Update stock quantity and record the reason.</p>
            <form method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/inventory" class="stock-form">
                <input type="hidden" name="action" value="adjust_stock">
                <input type="hidden" name="medicine_id" id="stockMedicineId">
                <label>Adjustment Type</label>
                <select name="adjustment_mode" required>
                    <option value="add">Add stock</option>
                    <option value="remove">Remove stock</option>
                    <option value="set">Set exact stock</option>
                </select>
                <label>Quantity</label>
                <input type="number" name="adjustment_quantity" min="1" required>
                <label>Reference No</label>
                <input type="text" name="reference_no" placeholder="Invoice / GRN / Note number">
                <label>Note</label>
                <textarea name="adjustment_note" rows="3" placeholder="Why was this stock changed?"></textarea>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal('stockModal')">Cancel</button>
                    <button type="submit" class="add-btn">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        function openStockModal(id, name) {
            document.getElementById('stockMedicineId').value = id;
            document.getElementById('stockModalMedicine').textContent = 'Updating stock for ' + name + '.';
            document.getElementById('stockModal').classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.add('hidden');
            }
        });
    </script>
</body>

</html>
