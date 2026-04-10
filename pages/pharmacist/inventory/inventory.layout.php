<?php
/**
 * Medicine Inventory Layout
 * Based on: medicine-inventory.jsp
 */
$medicines = $data['medicines'];
$search = $data['search'];
$base = APP_BASE ?: '';

/**
 * Medicine Inventory Layout
 * Based on: medicine-inventory.jsp
 */
$medicines = $data['medicines'];
$search = $data['search'];
$base = APP_BASE ?: '';

/**
 * Medicine Inventory Layout
 * Based on: medicine-inventory.jsp
 */
$medicines = $data['medicines'];
$search = $data['search'];
$base = APP_BASE ?: '';

/**
 * Medicine Inventory Layout
 * Based on: medicine-inventory.jsp
 */
$medicines = $data['medicines'];
$search = $data['search'];
$base = APP_BASE ?: '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/medicine-inventory.css">
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
            <form method="post" action="<?= htmlspecialchars($base) ?>/auth/logout" style="margin-top:10px;">
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
                    <span class="greeting-text">Medicine Inventory</span>
                    <span class="date-time">Current stock and details</span>
                </div>
            </div>
        </header>

        <div class="inventory-section">
            <div class="section-header">
                <div>
                    <h2>Existing Medicines</h2>
                    <p>Review the inventory of all medicines in the system</p>
                </div>
                <a href="<?= htmlspecialchars($base) ?>/pharmacist/addMedicine" class="add-btn">
                    <span>+</span> Add Medicine
                </a>
            </div>

            <?php if (($_GET['status'] ?? '') === 'deleted'): ?>
                <div class="alert alert-success">&#10004; Medicine deleted successfully.</div>
            <?php elseif (($_GET['status'] ?? '') === 'added'): ?>
                <div class="alert alert-success">&#10004; Medicine added successfully.</div>
            <?php elseif (($_GET['status'] ?? '') === 'updated'): ?>
                <div class="alert alert-success">&#10004; Medicine updated successfully.</div>
            <?php elseif (($_GET['status'] ?? '') === 'error'): ?>
                <div class="alert alert-error">&#9888; <?= htmlspecialchars((string)($_GET['msg'] ?? 'Operation failed')) ?></div>
            <?php endif; ?>

            <div class="search-box">
                <span>&#128269;</span>
                <input type="text" id="searchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, generic, category...">
            </div>

            <table class="data-table small-table" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Category</th>
                        <th>Strength</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Expiry</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($medicines)): ?>
                        <tr><td colspan="7" class="empty-msg">No medicines found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($medicines as $m): ?>
                            <?php
                            $brandName = trim((string)($m['name'] ?? ''));
                            $genericName = trim((string)($m['generic_name'] ?? ''));
                            $medicineName = $genericName !== '' ? $genericName : $brandName;
                            $smallBrand = ($brandName !== '' && strcasecmp($brandName, $medicineName) !== 0) ? $brandName : '';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($medicineName) ?></strong>
                                    <?php if ($smallBrand !== ''): ?>
                                        <div style="font-size:12px;color:#64748b;">Brand: <?= htmlspecialchars($smallBrand) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string)($m['category'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($m['strength'] ?? '')) ?></td>
                                <td class="<?= ((int)($m['quantity_in_stock'] ?? 0) < 10) ? 'text-danger' : '' ?>">
                                    <?= (int)($m['quantity_in_stock'] ?? 0) ?>
                                </td>
                                <td>Rs. <?= number_format((float)($m['price'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars((string)($m['expiry_date'] ?? '')) ?></td>
                                <td>
                                    <button class="action-btn" onclick="openActionsMenu(this)" type="button">&#8942;</button>
                                    <div class="action-menu hidden">
                                        <ul>
                                            <li class="edit" onclick="window.location.href='<?= htmlspecialchars($base) ?>/pharmacist/edit-medicine?id=<?= (int)($m['id'] ?? 0) ?>'">&#9998; Edit</li>
                                            <li class="delete" onclick="confirmDelete('<?= (int)($m['id'] ?? 0) ?>')">&#128465; Delete</li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="deleteModal" class="modal hidden">
    <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete this medicine? This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="cancel-btn" onclick="closeModal()">Cancel</button>
            <form id="deleteForm" method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/delete-medicine">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" class="delete-btn">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
function openActionsMenu(button) {
    const menu = button.nextElementSibling;
    document.querySelectorAll('.action-menu').forEach(m => {
        if (m !== menu) m.classList.add('hidden');
    });
    menu.classList.toggle('hidden');
}
document.getElementById('searchInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#inventoryTable tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
document.addEventListener('click', e => {
    if (!e.target.closest('.action-btn') && !e.target.closest('.action-menu')) {
        document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));
    }
});
</script>

</body>
</html>
