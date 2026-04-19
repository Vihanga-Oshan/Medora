<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/env.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/core/PharmacyContext.php';

set_time_limit(0);

function slugify_label(string $value): string
{
    $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? $value;
    $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    return $value;
}

function titleize_stem(string $stem): string
{
    $stem = str_replace(['_', '-', '.'], ' ', $stem);
    $stem = preg_replace('/\s+/', ' ', $stem) ?? $stem;
    $stem = trim($stem);
    if ($stem === '') {
        return 'Sample Medicine';
    }
    return ucwords(strtolower($stem));
}

function ensure_named_rows(string $table, array $names): array
{
    $ids = [];
    foreach ($names as $name) {
        $name = trim((string) $name);
        if ($name === '') {
            continue;
        }
        $row = Database::fetchOne("SELECT id FROM `$table` WHERE name = ? LIMIT 1", 's', [$name]);
        if (!$row) {
            Database::execute("INSERT IGNORE INTO `$table` (name, is_active, created_at) VALUES (?, 1, NOW())", 's', [$name]);
            $row = Database::fetchOne("SELECT id FROM `$table` WHERE name = ? LIMIT 1", 's', [$name]);
        }
        $id = (int) ($row['id'] ?? 0);
        if ($id > 0) {
            $ids[$name] = $id;
        }
    }
    return $ids;
}

function ensure_suppliers(array $suppliers): array
{
    $ids = [];
    foreach ($suppliers as $supplier) {
        $name = trim((string) ($supplier['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $row = Database::fetchOne("SELECT id FROM medicine_suppliers WHERE name = ? LIMIT 1", 's', [$name]);
        if (!$row) {
            Database::execute(
                "INSERT INTO medicine_suppliers (name, contact_person, phone, email, address, lead_time_days, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
                'sssssi',
                [
                    $name,
                    (string) ($supplier['contact_person'] ?? ''),
                    (string) ($supplier['phone'] ?? ''),
                    (string) ($supplier['email'] ?? ''),
                    (string) ($supplier['address'] ?? ''),
                    (int) ($supplier['lead_time_days'] ?? 0),
                ]
            );
            $row = Database::fetchOne("SELECT id FROM medicine_suppliers WHERE name = ? LIMIT 1", 's', [$name]);
        }
        $id = (int) ($row['id'] ?? 0);
        if ($id > 0) {
            $ids[$name] = $id;
        }
    }
    return $ids;
}

$categoryIds = ensure_named_rows('categories', [
    'Pain Relief',
    'Antibiotics',
    'Cardiac Care',
    'Diabetes Care',
    'Respiratory',
    'Digestive Health',
    'Vitamins',
    'Neurology',
    'Eye Care',
    'Lifestyle',
    'General',
]);

$dosageIds = ensure_named_rows('dosage_forms', [
    'Tablet',
    'Capsule',
    'Syrup',
    'Inhaler',
    'Cream',
    'Drops',
    'Injection',
    'Gel',
]);

$sellingUnitIds = ensure_named_rows('selling_units', [
    'Strip',
    'Box',
    'Bottle',
    'Tube',
    'Pack',
]);

$brandIds = ensure_named_rows('medicine_brands', [
    'Aspirin',
    'Augmentin',
    'Biaxin',
    'Brufen',
    'Cipro',
    'Crestor',
    'Diovan',
    'Farxiga',
    'Lipitor',
    'Nexium',
    'Ritalin',
    'Synthroid',
    'Ventolin',
    'Viagra',
    'Voltaren',
    'Xarelto',
    'Zantac',
    'Zithromax',
]);

$manufacturerIds = ensure_named_rows('medicine_manufacturers', [
    'Medora Labs',
    'Cura Pharmaceuticals',
    'Prime Health',
    'BluePeak Pharma',
    'Sunrise Medical',
]);

$supplierIds = ensure_suppliers([
    [
        'name' => 'Medora Distributors',
        'contact_person' => 'Nimal Perera',
        'phone' => '011-2000101',
        'email' => 'orders@medoradistributors.test',
        'address' => 'Colombo, Sri Lanka',
        'lead_time_days' => 3,
    ],
    [
        'name' => 'HealthLine Traders',
        'contact_person' => 'Sajith Fernando',
        'phone' => '011-2000102',
        'email' => 'sales@healthline.test',
        'address' => 'Kandy, Sri Lanka',
        'lead_time_days' => 5,
    ],
    [
        'name' => 'CityCare Supply',
        'contact_person' => 'Anushka Silva',
        'phone' => '011-2000103',
        'email' => 'contact@citycare.test',
        'address' => 'Galle, Sri Lanka',
        'lead_time_days' => 4,
    ],
]);

$activePharmacies = Database::fetchAll("SELECT id, name FROM pharmacies WHERE status = 'active' ORDER BY id ASC");
if (empty($activePharmacies)) {
    fwrite(STDERR, "No active pharmacies found. Aborting seed.\n");
    exit(1);
}

$uploadDir = ROOT . '/public/uploads/medicines';
if (!is_dir($uploadDir)) {
    fwrite(STDERR, "Upload directory not found: {$uploadDir}\n");
    exit(1);
}

$files = array_values(array_filter(scandir($uploadDir) ?: [], static function (string $file): bool {
    if ($file === '.' || $file === '..' || $file === '.gitkeep') {
        return false;
    }
    return (bool) preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $file);
}));
sort($files, SORT_NATURAL | SORT_FLAG_CASE);

if (empty($files)) {
    fwrite(STDERR, "No medicine images found in {$uploadDir}\n");
    exit(1);
}

$profiles = [
    'aspirin' => [
        'med_name' => 'Aspirin 100 mg Tablets',
        'generic_name' => 'Acetylsalicylic Acid',
        'category' => 'Pain Relief',
        'dosage_form' => 'Tablet',
        'strength' => '100 mg',
        'description' => 'Low-dose pain relief and anti-inflammatory support.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'augmentin' => [
        'med_name' => 'Augmentin 625 mg Tablets',
        'generic_name' => 'Amoxicillin and Clavulanate',
        'category' => 'Antibiotics',
        'dosage_form' => 'Tablet',
        'strength' => '625 mg',
        'description' => 'Broad-spectrum antibiotic used for common bacterial infections.',
        'selling_unit' => 'Box',
        'unit_quantity' => 14,
    ],
    'biaxin' => [
        'med_name' => 'Biaxin 250 mg Tablets',
        'generic_name' => 'Clarithromycin',
        'category' => 'Antibiotics',
        'dosage_form' => 'Tablet',
        'strength' => '250 mg',
        'description' => 'Macrolide antibiotic used for respiratory and skin infections.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'brufen' => [
        'med_name' => 'Brufen 400 mg Tablets',
        'generic_name' => 'Ibuprofen',
        'category' => 'Pain Relief',
        'dosage_form' => 'Tablet',
        'strength' => '400 mg',
        'description' => 'Common anti-inflammatory medicine for pain and fever.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'cipro' => [
        'med_name' => 'Cipro 500 mg Tablets',
        'generic_name' => 'Ciprofloxacin',
        'category' => 'Antibiotics',
        'dosage_form' => 'Tablet',
        'strength' => '500 mg',
        'description' => 'Antibiotic often prescribed for urinary and intestinal infections.',
        'selling_unit' => 'Box',
        'unit_quantity' => 14,
    ],
    'crestor' => [
        'med_name' => 'Crestor 10 mg Tablets',
        'generic_name' => 'Rosuvastatin',
        'category' => 'Cardiac Care',
        'dosage_form' => 'Tablet',
        'strength' => '10 mg',
        'description' => 'Cholesterol-lowering medicine for long-term heart care.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'diovan' => [
        'med_name' => 'Diovan 80 mg Tablets',
        'generic_name' => 'Valsartan',
        'category' => 'Cardiac Care',
        'dosage_form' => 'Tablet',
        'strength' => '80 mg',
        'description' => 'Blood pressure medicine for hypertension management.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'farxiga' => [
        'med_name' => 'Farxiga 10 mg Tablets',
        'generic_name' => 'Dapagliflozin',
        'category' => 'Diabetes Care',
        'dosage_form' => 'Tablet',
        'strength' => '10 mg',
        'description' => 'Diabetes support medicine used for blood sugar control.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'lipitor' => [
        'med_name' => 'Lipitor 20 mg Tablets',
        'generic_name' => 'Atorvastatin',
        'category' => 'Cardiac Care',
        'dosage_form' => 'Tablet',
        'strength' => '20 mg',
        'description' => 'Statin medicine for cholesterol and heart risk reduction.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'nexium' => [
        'med_name' => 'Nexium 20 mg Capsules',
        'generic_name' => 'Esomeprazole',
        'category' => 'Digestive Health',
        'dosage_form' => 'Capsule',
        'strength' => '20 mg',
        'description' => 'Acid-reducing medicine for reflux and stomach protection.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 14,
    ],
    'ritalin' => [
        'med_name' => 'Ritalin 10 mg Tablets',
        'generic_name' => 'Methylphenidate',
        'category' => 'Neurology',
        'dosage_form' => 'Tablet',
        'strength' => '10 mg',
        'description' => 'CNS medicine commonly used under close supervision.',
        'selling_unit' => 'Box',
        'unit_quantity' => 10,
    ],
    'synthroid' => [
        'med_name' => 'Synthroid 50 mcg Tablets',
        'generic_name' => 'Levothyroxine',
        'category' => 'General',
        'dosage_form' => 'Tablet',
        'strength' => '50 mcg',
        'description' => 'Thyroid replacement medicine for daily hormone support.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'ventolin' => [
        'med_name' => 'Ventolin Inhaler',
        'generic_name' => 'Salbutamol',
        'category' => 'Respiratory',
        'dosage_form' => 'Inhaler',
        'strength' => '100 mcg',
        'description' => 'Fast relief inhaler for bronchospasm and breathing difficulty.',
        'selling_unit' => 'Pack',
        'unit_quantity' => 1,
    ],
    'viagra' => [
        'med_name' => 'Viagra 50 mg Tablets',
        'generic_name' => 'Sildenafil',
        'category' => 'Lifestyle',
        'dosage_form' => 'Tablet',
        'strength' => '50 mg',
        'description' => 'Prescription medicine for erectile dysfunction support.',
        'selling_unit' => 'Box',
        'unit_quantity' => 4,
    ],
    'voltaren' => [
        'med_name' => 'Voltaren Gel 1%',
        'generic_name' => 'Diclofenac',
        'category' => 'Pain Relief',
        'dosage_form' => 'Gel',
        'strength' => '1%',
        'description' => 'Topical anti-inflammatory gel for joint and muscle pain.',
        'selling_unit' => 'Tube',
        'unit_quantity' => 1,
    ],
    'xarelto' => [
        'med_name' => 'Xarelto 20 mg Tablets',
        'generic_name' => 'Rivaroxaban',
        'category' => 'Cardiac Care',
        'dosage_form' => 'Tablet',
        'strength' => '20 mg',
        'description' => 'Blood-thinning medicine used under specialist supervision.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'zantac' => [
        'med_name' => 'Zantac 150 mg Tablets',
        'generic_name' => 'Ranitidine',
        'category' => 'Digestive Health',
        'dosage_form' => 'Tablet',
        'strength' => '150 mg',
        'description' => 'Acid-control medicine for stomach discomfort and reflux care.',
        'selling_unit' => 'Strip',
        'unit_quantity' => 10,
    ],
    'zithromax' => [
        'med_name' => 'Zithromax 500 mg Tablets',
        'generic_name' => 'Azithromycin',
        'category' => 'Antibiotics',
        'dosage_form' => 'Tablet',
        'strength' => '500 mg',
        'description' => 'Popular antibiotic for respiratory and skin infections.',
        'selling_unit' => 'Box',
        'unit_quantity' => 3,
    ],
];

$pharmacyNames = [];
foreach ($activePharmacies as $pharmacy) {
    $pid = (int) ($pharmacy['id'] ?? 0);
    if ($pid > 0) {
        $pharmacyNames[] = (string) ($pharmacy['name'] ?? ('Pharmacy ' . $pid));
    }
}

$existingRows = Database::fetchAll("SELECT pharmacy_id, image_path FROM medicines WHERE image_path LIKE 'uploads/medicines/%'");
$existingMap = [];
foreach ($existingRows as $row) {
    $pid = (int) ($row['pharmacy_id'] ?? 0);
    $path = trim((string) ($row['image_path'] ?? ''));
    if ($pid > 0 && $path !== '') {
        $existingMap[$pid][$path] = true;
    }
}

$inserted = 0;
$skipped = 0;
$defaultSupplierIds = array_values($supplierIds);
$defaultManufacturerIds = array_values($manufacturerIds);
$defaultCategoryIds = array_values($categoryIds);
$defaultDosageNames = array_keys($dosageIds);
$defaultSellingUnitNames = array_keys($sellingUnitIds);

foreach ($files as $fileIndex => $fileName) {
    $stem = pathinfo($fileName, PATHINFO_FILENAME);
    $slug = strtolower(slugify_label($stem));
    $profile = $profiles[$slug] ?? [];

    $brandName = $profile['name'] ?? titleize_stem($stem);
    $medName = $profile['med_name'] ?? ($brandName . ' Tablets');
    $genericName = $profile['generic_name'] ?? titleize_stem($stem);
    $categoryName = $profile['category'] ?? 'General';
    $dosageForm = $profile['dosage_form'] ?? 'Tablet';
    $strength = $profile['strength'] ?? '10 mg';
    $description = $profile['description'] ?? ('Sample listing for ' . $brandName . '.');
    $sellingUnit = $profile['selling_unit'] ?? 'Strip';
    $unitQuantity = (int) ($profile['unit_quantity'] ?? 10);

    $categoryId = (int) ($categoryIds[$categoryName] ?? 0);
    if ($categoryId <= 0 && !empty($defaultCategoryIds)) {
        $categoryId = $defaultCategoryIds[$fileIndex % count($defaultCategoryIds)];
    }

    $dosageId = (int) ($dosageIds[$dosageForm] ?? 0);
    if ($dosageId <= 0 && !empty($defaultDosageNames)) {
        $dosageForm = $defaultDosageNames[$fileIndex % count($defaultDosageNames)];
    }

    $sellingUnitId = (int) ($sellingUnitIds[$sellingUnit] ?? 0);
    if ($sellingUnitId <= 0 && !empty($defaultSellingUnitNames)) {
        $sellingUnit = $defaultSellingUnitNames[$fileIndex % count($defaultSellingUnitNames)];
    }

    $manufacturerName = array_keys($manufacturerIds)[$fileIndex % max(1, count($manufacturerIds))] ?? 'Medora Labs';
    $supplierName = array_keys($supplierIds)[$fileIndex % max(1, count($supplierIds))] ?? 'Medora Distributors';

    $pricing = max(75, 120 + (($fileIndex % 7) * 45) + (int) (crc32($stem) % 60));
    $unitCost = round($pricing * 0.58, 2);
    $quantity = 24 + (($fileIndex % 6) * 12) + (int) (crc32($stem . 'qty') % 18);
    $lowStock = max(8, (int) round($quantity * 0.35));
    $reorderQty = max(20, $lowStock + 10);
    $expiry = date('Y-m-d', strtotime('+' . (240 + ($fileIndex * 29) % 460) . ' days'));
    $lastRestockedAt = date('Y-m-d H:i:s', strtotime('-' . ($fileIndex % 5) . ' days'));
    $batchNumber = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $slug) ?: 'MED', 0, 12)) . '-' . str_pad((string) ($fileIndex + 1), 3, '0', STR_PAD_LEFT);
    $imagePath = 'uploads/medicines/' . $fileName;

    foreach ($activePharmacies as $pharmacyIndex => $pharmacy) {
        $pharmacyId = (int) ($pharmacy['id'] ?? 0);
        if ($pharmacyId <= 0) {
            continue;
        }

        if (!empty($existingMap[$pharmacyId][$imagePath])) {
            $skipped++;
            continue;
        }

        $ok = Database::execute(
            "INSERT INTO medicines
                (name, med_name, generic_name, category, category_id, description, dosage_form, strength,
                 quantity_in_stock, low_stock_threshold, reorder_quantity, pricing, unit_cost, manufacturer,
                 supplier_id, batch_number, expiry_date, last_restocked_at, image_path, selling_unit,
                 unit_quantity, pharmacy_id, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            'ssssisssiiiddsisssssii',
            [
                $brandName,
                $medName,
                $genericName,
                $categoryName,
                $categoryId > 0 ? $categoryId : null,
                $description,
                $dosageForm,
                $strength,
                $quantity,
                $lowStock,
                $reorderQty,
                $pricing,
                $unitCost,
                $manufacturerName,
                $manufacturerIds[$manufacturerName] ?? null,
                $batchNumber,
                $expiry,
                $lastRestockedAt,
                $imagePath,
                $sellingUnit,
                $unitQuantity,
                $pharmacyId,
            ]
        );

        if ($ok) {
            $inserted++;
            $existingMap[$pharmacyId][$imagePath] = true;
        }
    }
}

echo "Seed complete. Inserted {$inserted} medicine rows and skipped {$skipped} existing rows.\n";
echo "Images processed: " . count($files) . "\n";
echo "Active pharmacies seeded: " . count($activePharmacies) . "\n";
