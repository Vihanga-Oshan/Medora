<?php
require_once __DIR__ . '/../../common/pharmacist.head.php';
require_once __DIR__ . '/../inventory.model.php';

$base = APP_BASE ?: '';
$error = '';

if (Request::isPost()) {
    $name = trim((string)($_POST['brand_new'] ?? ''));
    if ($name === '') {
        $name = trim((string)($_POST['brand_existing'] ?? ''));
    }
    $medName = trim((string)($_POST['med_name'] ?? ''));
    $strength = trim((string)($_POST['strength'] ?? ''));
    $price = (float)($_POST['price'] ?? 0);
    $supplierId = (int)($_POST['supplier_existing'] ?? 0);
    $supplierNew = trim((string)($_POST['supplier_new'] ?? ''));
    $lowStockThreshold = (int)($_POST['low_stock_threshold'] ?? 0);

    if ($name === '') {
        $error = 'Brand name is required.';
    } elseif ($medName === '') {
        $error = 'Medicine name is required.';
    } elseif ($supplierId <= 0 && $supplierNew === '') {
        $error = 'Supplier is required.';
    } elseif ($strength === '') {
        $error = 'Strength is required.';
    } elseif ($lowStockThreshold < 0) {
        $error = 'Low stock threshold cannot be negative.';
    } elseif ($price < 0) {
        $error = 'Price must be zero or positive.';
    } else {
        $payload = $_POST;
        $payload['added_by'] = (int)($user['id'] ?? 0);
        $ok = InventoryModel::create($payload, $_FILES['imageFile'] ?? null);
        if ($ok) {
            Response::redirect('/pharmacist/inventory?status=added');
        }
        $error = 'Failed to add medicine. Please check inputs and database columns.';
    }
}

$categories = InventoryModel::getCategories();
$brands = InventoryModel::getBrands();
$dosageForms = InventoryModel::getDosageForms();
$sellingUnits = InventoryModel::getSellingUnits();
$manufacturers = InventoryModel::getManufacturers();
$suppliers = InventoryModel::getSuppliers();
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Medicine - Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css" />
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/formstyles.css" />
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="logo-section"><div class="logo-icon">&#10010;</div><h1 class="logo-text">Medora</h1></div>
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
            <div class="greeting"><span class="greeting-icon">&#128138;</span><div><span class="greeting-text">Add New Medicine</span><span class="date-time">Fill medicine details</span></div></div>
        </header>

        <section class="form-section">
            <div class="section-header">
                <div><h2>Add Medicine to Inventory</h2><p>Fill in required details to create a medicine entry</p></div>
                <a class="add-btn" href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory"><span>&larr;</span> Back to Inventory</a>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error" style="grid-column: span 2;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data" class="styled-form">
                <div class="form-section-title"><span>&#8505;&#65039;</span> Basic Identification</div>
                <div class="form-group">
                    <label>Brand Name</label>
                    <select name="brand_existing">
                        <option value="">Select Existing Brand</option>
                        <?php foreach ($brands as $b): ?>
                            <?php $selected = ((string)($_POST['brand_existing'] ?? '') === (string)$b) ? 'selected' : ''; ?>
                            <option value="<?= htmlspecialchars((string)$b) ?>" <?= $selected ?>><?= htmlspecialchars((string)$b) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new brand</small>
                    <input type="text" name="brand_new" list="brand-options" value="<?= htmlspecialchars((string)($_POST['brand_new'] ?? '')) ?>" placeholder="Type new brand name">
                    <datalist id="brand-options">
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= htmlspecialchars((string)$b) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group"><label>Generic Name</label><input type="text" name="generic_name" value="<?= htmlspecialchars((string)($_POST['generic_name'] ?? '')) ?>"></div>
                <div class="form-group"><label>MEDICINE NAME</label><input type="text" name="med_name" value="<?= htmlspecialchars((string)($_POST['med_name'] ?? '')) ?>" placeholder="e.g. Lipitor"></div>
                <div class="form-group">
                    <label>Manufacturer</label>
                    <select name="manufacturer_existing">
                        <option value="">Select Existing Manufacturer</option>
                        <?php foreach ($manufacturers as $m): ?>
                            <?php $selected = ((string)($_POST['manufacturer_existing'] ?? '') === (string)$m) ? 'selected' : ''; ?>
                            <option value="<?= htmlspecialchars((string)$m) ?>" <?= $selected ?>><?= htmlspecialchars((string)$m) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new manufacturer</small>
                    <input type="text" name="manufacturer_new" list="manufacturer-options" value="<?= htmlspecialchars((string)($_POST['manufacturer_new'] ?? '')) ?>" placeholder="Type new manufacturer">
                    <datalist id="manufacturer-options">
                        <?php foreach ($manufacturers as $m): ?>
                            <option value="<?= htmlspecialchars((string)$m) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Supplier <span style="color:#dc2626;">*</span></label>
                    <select name="supplier_existing">
                        <option value="">Select Existing Supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <?php $selected = ((string)($_POST['supplier_existing'] ?? '') === (string)($supplier['id'] ?? '')) ? 'selected' : ''; ?>
                            <option value="<?= (int)($supplier['id'] ?? 0) ?>" <?= $selected ?>><?= htmlspecialchars((string)($supplier['name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or create a new supplier</small>
                    <input type="text" name="supplier_new" value="<?= htmlspecialchars((string)($_POST['supplier_new'] ?? '')) ?>" placeholder="Type new supplier name">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <?php if (!empty($categories)): ?>
                        <select name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php $cid = (int)($cat['id'] ?? 0); ?>
                                <option value="<?= $cid ?>" <?= ((string)($_POST['category_id'] ?? '') === (string)$cid) ? 'selected' : '' ?>><?= htmlspecialchars((string)($cat['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="category" value="<?= htmlspecialchars((string)($_POST['category'] ?? '')) ?>" placeholder="Category name">
                    <?php endif; ?>
                </div>
                <div class="form-group full-width"><label>Description</label><textarea name="description"><?= htmlspecialchars((string)($_POST['description'] ?? '')) ?></textarea></div>

                <div class="form-section-title"><span>&#128222;</span> Supplier Contact</div>
                <div class="form-group"><label>Contact Person</label><input type="text" name="supplier_contact_person" value="<?= htmlspecialchars((string)($_POST['supplier_contact_person'] ?? '')) ?>"></div>
                <div class="form-group"><label>Supplier Phone</label><input type="text" name="supplier_phone" value="<?= htmlspecialchars((string)($_POST['supplier_phone'] ?? '')) ?>"></div>
                <div class="form-group"><label>Supplier Email</label><input type="email" name="supplier_email" value="<?= htmlspecialchars((string)($_POST['supplier_email'] ?? '')) ?>"></div>
                <div class="form-group"><label>Lead Time (Days)</label><input type="number" name="supplier_lead_time_days" min="0" value="<?= htmlspecialchars((string)($_POST['supplier_lead_time_days'] ?? '0')) ?>"></div>
                <div class="form-group full-width"><label>Supplier Address</label><textarea name="supplier_address"><?= htmlspecialchars((string)($_POST['supplier_address'] ?? '')) ?></textarea></div>

                <div class="form-section-title"><span>&#128138;</span> Dosage &amp; Presentation</div>
                <div class="form-group">
                    <label>Dosage Form</label>
                    <select name="dosage_form_existing">
                        <option value="">Select Dosage Form</option>
                        <?php foreach ($dosageForms as $d): ?>
                            <?php $selected = ((string)($_POST['dosage_form_existing'] ?? '') === (string)$d) ? 'selected' : ''; ?>
                            <option value="<?= htmlspecialchars((string)$d) ?>" <?= $selected ?>><?= htmlspecialchars((string)$d) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new dosage form</small>
                    <input type="text" name="dosage_form_new" value="<?= htmlspecialchars((string)($_POST['dosage_form_new'] ?? '')) ?>" placeholder="Type new dosage form">
                </div>
                <div class="form-group"><label>Strength</label><input type="text" name="strength" required value="<?= htmlspecialchars((string)($_POST['strength'] ?? '')) ?>" placeholder="e.g. 500mg"></div>

                <div class="form-section-title"><span>&#128230;</span> Inventory &amp; Measurement</div>
                <div class="form-group">
                    <label>Selling Unit</label>
                    <select name="selling_unit_existing">
                        <option value="">Select Selling Unit</option>
                        <?php foreach ($sellingUnits as $u): ?>
                            <?php $selected = ((string)($_POST['selling_unit_existing'] ?? '') === (string)$u) ? 'selected' : ''; ?>
                            <option value="<?= htmlspecialchars((string)$u) ?>" <?= $selected ?>><?= htmlspecialchars((string)$u) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new selling unit</small>
                    <input type="text" name="selling_unit_new" value="<?= htmlspecialchars((string)($_POST['selling_unit_new'] ?? '')) ?>" placeholder="Type new selling unit">
                </div>
                <div class="form-group"><label>Doses per Unit</label><input type="number" name="unit_quantity" min="1" value="<?= htmlspecialchars((string)($_POST['unit_quantity'] ?? '1')) ?>"></div>
                <div class="form-group"><label>Current Stock (Units)</label><input type="number" name="quantity_in_stock" min="0" value="<?= htmlspecialchars((string)($_POST['quantity_in_stock'] ?? '0')) ?>"></div>
                <div class="form-group"><label>Low Stock Threshold</label><input type="number" name="low_stock_threshold" min="0" value="<?= htmlspecialchars((string)($_POST['low_stock_threshold'] ?? '10')) ?>"></div>
                <div class="form-group"><label>Reorder Quantity</label><input type="number" name="reorder_quantity" min="0" value="<?= htmlspecialchars((string)($_POST['reorder_quantity'] ?? '25')) ?>"></div>
                <div class="form-group"><label>Price per Unit</label><input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars((string)($_POST['price'] ?? '0')) ?>"></div>
                <div class="form-group"><label>Unit Cost</label><input type="number" name="unit_cost" step="0.01" min="0" value="<?= htmlspecialchars((string)($_POST['unit_cost'] ?? ($_POST['price'] ?? '0'))) ?>"></div>

                <div class="form-section-title"><span>&#128197;</span> Logistics &amp; Media</div>
                <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date" value="<?= htmlspecialchars((string)($_POST['expiry_date'] ?? '')) ?>"></div>
                <div class="form-group"><label>Batch Number</label><input type="text" name="batch_number" value="<?= htmlspecialchars((string)($_POST['batch_number'] ?? '')) ?>" placeholder="Batch / lot number"></div>
                <div class="form-group"><label>Medicine Image</label><input type="file" name="imageFile" accept="image/*"></div>

                <div class="btn-group"><button type="submit" class="btn-submit">Add Medicine to Inventory</button></div>
            </form>
        </section>
    </main>
</div>
</body>
</html>

