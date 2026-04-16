<?php
require_once __DIR__ . '/../../common/pharmacist.head.php';
require_once __DIR__ . '/../inventory.model.php';

$base = APP_BASE ?: '';
$error = '';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    Response::redirect('/pharmacist/inventory');
}

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
        $ok = InventoryModel::update($id, $_POST, $_FILES['imageFile'] ?? null);
        if ($ok) {
            Response::redirect('/pharmacist/inventory?status=updated');
        }
        $error = 'Failed to update medicine. Please check inputs and database columns.';
    }
}

$medicine = InventoryModel::getById($id);
if (!$medicine) {
    Response::redirect('/pharmacist/inventory?status=error&msg=not_found');
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

$fv = function (string $key, $fallback = '') use ($medicine): string {
    if (isset($_POST[$key])) {
        return (string)$_POST[$key];
    }
    return (string)($medicine[$key] ?? $fallback);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Medicine - Medora</title>
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
            <div class="greeting"><span class="greeting-icon">&#9998;</span><div><span class="greeting-text">Edit Medicine</span><span class="date-time">Modify medicine details</span></div></div>
        </header>

        <section class="form-section">
            <div class="section-header">
                <div><h2>Edit Medicine</h2><p>Modify details and save changes.</p></div>
                <a class="add-btn" href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory"><span>&larr;</span> Back to Inventory</a>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error" style="grid-column: span 2;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data" class="styled-form">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="form-section-title"><span>&#8505;&#65039;</span> Basic Identification</div>
                <div class="form-group">
                    <label>Brand Name</label>
                    <?php $selectedBrand = (string)($_POST['brand_existing'] ?? $fv('name')); ?>
                    <select name="brand_existing">
                        <option value="">Select Existing Brand</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= htmlspecialchars((string)$b) ?>" <?= $selectedBrand === (string)$b ? 'selected' : '' ?>><?= htmlspecialchars((string)$b) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new brand</small>
                    <input type="text" name="brand_new" value="<?= htmlspecialchars((string)($_POST['brand_new'] ?? '')) ?>" placeholder="Type new brand name">
                </div>
                <div class="form-group"><label>Generic Name</label><input type="text" name="generic_name" value="<?= htmlspecialchars($fv('generic_name')) ?>"></div>
                <div class="form-group"><label>MEDICINE NAME</label><input type="text" name="med_name" value="<?= htmlspecialchars($fv('med_name')) ?>" placeholder="e.g. Lipitor"></div>
                <div class="form-group">
                    <label>Manufacturer</label>
                    <?php $selectedMaker = (string)($_POST['manufacturer_existing'] ?? $fv('manufacturer')); ?>
                    <select name="manufacturer_existing">
                        <option value="">Select Existing Manufacturer</option>
                        <?php foreach ($manufacturers as $m): ?>
                            <option value="<?= htmlspecialchars((string)$m) ?>" <?= $selectedMaker === (string)$m ? 'selected' : '' ?>><?= htmlspecialchars((string)$m) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new manufacturer</small>
                    <input type="text" name="manufacturer_new" value="<?= htmlspecialchars((string)($_POST['manufacturer_new'] ?? '')) ?>" placeholder="Type new manufacturer">
                </div>
                <div class="form-group">
                    <label>Supplier <span style="color:#dc2626;">*</span></label>
                    <?php $selectedSupplier = (string)($_POST['supplier_existing'] ?? ($medicine['supplier_id'] ?? '')); ?>
                    <select name="supplier_existing">
                        <option value="">Select Existing Supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int)($supplier['id'] ?? 0) ?>" <?= $selectedSupplier === (string)($supplier['id'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars((string)($supplier['name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new supplier</small>
                    <input type="text" name="supplier_new" value="<?= htmlspecialchars((string)($_POST['supplier_new'] ?? '')) ?>" placeholder="Type new supplier name">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <?php if (!empty($categories)): ?>
                        <?php $selectedCategoryId = (string)($_POST['category_id'] ?? ($medicine['category_id'] ?? '')); ?>
                        <select name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php $cid = (int)($cat['id'] ?? 0); ?>
                                <option value="<?= $cid ?>" <?= ($selectedCategoryId === (string)$cid) ? 'selected' : '' ?>><?= htmlspecialchars((string)($cat['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="category" value="<?= htmlspecialchars($fv('category')) ?>" placeholder="Category name">
                    <?php endif; ?>
                </div>
                <div class="form-group full-width"><label>Description</label><textarea name="description"><?= htmlspecialchars($fv('description')) ?></textarea></div>

                <div class="form-section-title"><span>&#128222;</span> Supplier Contact</div>
                <div class="form-group"><label>Contact Person</label><input type="text" name="supplier_contact_person" value="<?= htmlspecialchars((string)($_POST['supplier_contact_person'] ?? ($medicine['supplier_contact_person'] ?? ''))) ?>"></div>
                <div class="form-group"><label>Supplier Phone</label><input type="text" name="supplier_phone" value="<?= htmlspecialchars((string)($_POST['supplier_phone'] ?? ($medicine['supplier_phone'] ?? ''))) ?>"></div>
                <div class="form-group"><label>Supplier Email</label><input type="email" name="supplier_email" value="<?= htmlspecialchars((string)($_POST['supplier_email'] ?? ($medicine['supplier_email'] ?? ''))) ?>"></div>
                <div class="form-group"><label>Lead Time (Days)</label><input type="number" name="supplier_lead_time_days" min="0" value="<?= htmlspecialchars((string)($_POST['supplier_lead_time_days'] ?? ($medicine['supplier_lead_time_days'] ?? '0'))) ?>"></div>
                <div class="form-group full-width"><label>Supplier Address</label><textarea name="supplier_address"><?= htmlspecialchars((string)($_POST['supplier_address'] ?? ($medicine['supplier_address'] ?? ''))) ?></textarea></div>

                <div class="form-section-title"><span>&#128138;</span> Dosage &amp; Presentation</div>
                <div class="form-group">
                    <label>Dosage Form</label>
                    <?php $selectedDosage = (string)($_POST['dosage_form_existing'] ?? $fv('dosage_form')); ?>
                    <select name="dosage_form_existing">
                        <option value="">Select Dosage Form</option>
                        <?php foreach ($dosageForms as $d): ?>
                            <option value="<?= htmlspecialchars((string)$d) ?>" <?= $selectedDosage === (string)$d ? 'selected' : '' ?>><?= htmlspecialchars((string)$d) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new dosage form</small>
                    <input type="text" name="dosage_form_new" value="<?= htmlspecialchars((string)($_POST['dosage_form_new'] ?? '')) ?>" placeholder="Type new dosage form">
                </div>
                <div class="form-group"><label>Strength</label><input type="text" name="strength" required value="<?= htmlspecialchars($fv('strength')) ?>"></div>

                <div class="form-section-title"><span>&#128230;</span> Inventory &amp; Measurement</div>
                <div class="form-group">
                    <label>Selling Unit</label>
                    <?php $selectedUnit = (string)($_POST['selling_unit_existing'] ?? $fv('selling_unit')); ?>
                    <select name="selling_unit_existing">
                        <option value="">Select Selling Unit</option>
                        <?php foreach ($sellingUnits as $u): ?>
                            <option value="<?= htmlspecialchars((string)$u) ?>" <?= $selectedUnit === (string)$u ? 'selected' : '' ?>><?= htmlspecialchars((string)$u) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#64748b;display:block;margin-top:4px;">or add a new selling unit</small>
                    <input type="text" name="selling_unit_new" value="<?= htmlspecialchars((string)($_POST['selling_unit_new'] ?? '')) ?>" placeholder="Type new selling unit">
                </div>
                <div class="form-group"><label>Doses per Unit</label><input type="number" name="unit_quantity" min="1" value="<?= htmlspecialchars($fv('unit_quantity', '1')) ?>"></div>
                <div class="form-group"><label>Current Stock (Units)</label><input type="number" name="quantity_in_stock" min="0" value="<?= htmlspecialchars($fv('quantity_in_stock', '0')) ?>"></div>
                <div class="form-group"><label>Low Stock Threshold</label><input type="number" name="low_stock_threshold" min="0" value="<?= htmlspecialchars($fv('low_stock_threshold', '10')) ?>"></div>
                <div class="form-group"><label>Reorder Quantity</label><input type="number" name="reorder_quantity" min="0" value="<?= htmlspecialchars($fv('reorder_quantity', '25')) ?>"></div>
                <div class="form-group"><label>Price per Unit</label><input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($fv('price', '0')) ?>"></div>
                <div class="form-group"><label>Unit Cost</label><input type="number" name="unit_cost" step="0.01" min="0" value="<?= htmlspecialchars($fv('unit_cost', $fv('price', '0'))) ?>"></div>

                <div class="form-section-title"><span>&#128197;</span> Logistics &amp; Media</div>
                <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date" value="<?= htmlspecialchars(substr($fv('expiry_date'), 0, 10)) ?>"></div>
                <div class="form-group"><label>Batch Number</label><input type="text" name="batch_number" value="<?= htmlspecialchars($fv('batch_number')) ?>" placeholder="Batch / lot number"></div>
                <div class="form-group">
                    <label>Medicine Image</label>
                    <input type="file" name="imageFile" accept="image/*">
                    <?php if (!empty($medicine['image_path'])): ?>
                        <small style="color:#64748b;">Current: <?= htmlspecialchars((string)$medicine['image_path']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="btn-group"><button type="submit" class="btn-submit">Update Medicine Details</button></div>
            </form>
        </section>
    </main>
</div>
</body>
</html>

