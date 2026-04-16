
<?php
$medicines = $data['medicines'] ?? [];
$search = $data['search'] ?? '';
$statusFilter = $data['status'] ?? 'all';
$summary = $data['summary'] ?? [];
$suppliers = $data['suppliers'] ?? [];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Stock Management | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/medicine-inventory.css?v=<?= $cssVer ?>">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="logo-section">
            <div class="logo-icon">&#10010;</div>
            <h1 class="logo-text">Medora</h1>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="nav-item <?= $isDashboard ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate" class="nav-item <?= $isValidate ? 'active' : '' ?>">Prescription Review</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions" class="nav-item <?= $isApproved ? 'active' : '' ?>">Approved Prescriptions</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients" class="nav-item <?= $isPatients ? 'active' : '' ?>">Patients</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/messages" class="nav-item <?= $isMessages ? 'active' : '' ?>">Messages</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory" class="nav-item <?= $isMedicine ? 'active' : '' ?>">Medicine</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/settings" class="nav-item <?= $isSettings ? 'active' : '' ?>">Settings</a></li>
            </ul>
        </nav>
        <div class="footer-section">
            <form method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/logout" style="margin-top:10px;">
                <button type="submit" class="nav-item logout-link" style="display:block; width:100%; text-align:left; border:none; background:none; cursor:pointer;">Logout</button>
            </form>
            <div class="copyright">Medora &copy; 2022</div>
            <div class="version">v 1.1.2</div>
        </div>
    </aside>

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
                <a href="<?= htmlspecialchars($base) ?>/pharmacist/addMedicine" class="add-btn"><span>+</span> Add Medicine</a>
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
                <div class="alert alert-error"><?= htmlspecialchars((string) ($_GET['msg'] ?? 'Operation failed')) ?></div>
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
                <article class="summary-card accent-green">
                    <span class="summary-label">Inventory Value</span>
                    <strong>Rs. <?= number_format((float) ($summary['total_stock_value'] ?? 0), 2) ?></strong>
                    <small><?= (int) ($summary['supplier_count'] ?? 0) ?> active suppliers</small>
                </article>
            </div>

            <div class="insight-grid">
                <section class="panel-card">
                    <div class="panel-head">
                        <h3>Priority Reorders</h3>
                        <span class="panel-chip">Low stock</span>
                    </div>
                    <?php $priority = array_values(array_filter($medicines, fn($m) => !empty($m['is_low_stock']) || !empty($m['is_out_of_stock']))); ?>
                    <?php if (empty($priority)): ?>
                        <p class="empty-msg">No low-stock medicines right now.</p>
                    <?php else: ?>
                        <div class="mini-list">
                            <?php foreach (array_slice($priority, 0, 5) as $m): ?>
                                <?php $displayName = trim((string) ($m['med_name'] ?? '')) !== '' ? (string) $m['med_name'] : (string) ($m['name'] ?? ''); ?>
                                <article class="mini-row">
                                    <div>
                                        <strong><?= htmlspecialchars($displayName) ?></strong>
                                        <small><?= htmlspecialchars((string) ($m['supplier_name'] ?? 'No supplier')) ?></small>
                                    </div>
                                    <div class="mini-meta">
                                        <span class="status-badge <?= htmlspecialchars((string) ($m['stock_status_key'] ?? 'healthy')) ?>"><?= htmlspecialchars((string) ($m['stock_status_label'] ?? 'Healthy')) ?></span>
                                        <small><?= (int) ($m['quantity_in_stock'] ?? 0) ?> / reorder <?= (int) ($m['reorder_quantity'] ?? 0) ?></small>
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
                                    <small><?= (int) ($supplier['medicine_count'] ?? 0) ?> medicines, <?= (int) ($supplier['stocked_units'] ?? 0) ?> units</small>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <form method="get" class="filters-bar">
                <div class="search-box">
                    <span>&#128269;</span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search medicine, generic, category, or supplier">
                </div>
                <select name="status_filter" class="filter-select">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All stock states</option>
                    <option value="low" <?= $statusFilter === 'low' ? 'selected' : '' ?>>Low stock</option>
                    <option value="out" <?= $statusFilter === 'out' ? 'selected' : '' ?>>Out of stock</option>
                    <option value="expiring" <?= $statusFilter === 'expiring' ? 'selected' : '' ?>>Expiring soon</option>
                    <option value="healthy" <?= $statusFilter === 'healthy' ? 'selected' : '' ?>>Healthy stock</option>
                </select>
                <button type="submit" class="filter-btn">Apply</button>
            </form>

            <div class="table-shell">
                <table class="data-table" id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Supplier</th>
                            <th>Stock Status</th>
                            <th>Pricing</th>
                            <th>Expiry</th>
                            <th>Batch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($medicines)): ?>
                        <tr><td colspan="7" class="empty-msg">No medicines found for the current filter.</td></tr>
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
                                    <div class="med-subtext"><?= htmlspecialchars($genericName !== '' ? $genericName : (string) ($m['strength'] ?? '')) ?></div>
                                    <div class="med-meta"><?= htmlspecialchars((string) ($m['category'] ?? 'Uncategorized')) ?> • <?= htmlspecialchars((string) ($m['dosage_form'] ?? 'Form not set')) ?></div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($supplierName !== '' ? $supplierName : 'Supplier missing') ?></strong>
                                    <div class="med-subtext"><?= htmlspecialchars((string) ($m['supplier_phone'] ?? 'No phone')) ?></div>
                                    <div class="med-meta">Lead time: <?= (int) ($m['supplier_lead_time_days'] ?? 0) ?> day(s)</div>
                                </td>
                                <td>
                                    <span class="status-badge <?= htmlspecialchars((string) ($m['stock_status_key'] ?? 'healthy')) ?>"><?= htmlspecialchars((string) ($m['stock_status_label'] ?? 'Healthy')) ?></span>
                                    <div class="med-subtext">On hand: <?= (int) ($m['quantity_in_stock'] ?? 0) ?> <?= htmlspecialchars((string) ($m['selling_unit'] ?? 'units')) ?></div>
                                    <div class="med-meta">Low stock at <?= (int) ($m['low_stock_threshold'] ?? 0) ?>, reorder <?= (int) ($m['reorder_quantity'] ?? 0) ?></div>
                                </td>
                                <td>
                                    <strong>Sell: Rs. <?= number_format((float) ($m['price'] ?? 0), 2) ?></strong>
                                    <div class="med-subtext">Cost: Rs. <?= number_format((float) ($m['unit_cost'] ?? 0), 2) ?></div>
                                    <div class="med-meta">Value: Rs. <?= number_format((float) ($m['total_stock_value'] ?? 0), 2) ?></div>
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
                                    <div class="med-subtext">Restocked: <?= htmlspecialchars((string) ($m['last_restocked_at'] ?? 'Not recorded')) ?></div>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <button class="action-link" type="button" onclick="openStockModal(<?= (int) ($m['id'] ?? 0) ?>, '<?= htmlspecialchars($displayName, ENT_QUOTES) ?>')">Adjust Stock</button>
                                        <a class="action-link secondary" href="<?= htmlspecialchars($base) ?>/pharmacist/edit-medicine?id=<?= (int) ($m['id'] ?? 0) ?>">Edit</a>
                                        <button class="action-link danger" type="button" onclick="confirmDelete('<?= (int) ($m['id'] ?? 0) ?>')">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

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
                                    <small><?= (int) ($move['quantity_before'] ?? 0) ?> -> <?= (int) ($move['quantity_after'] ?? 0) ?></small>
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
            <form id="deleteForm" method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/delete-medicine">
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
