<?php
/**
 * Medicine Inventory Model
 */
class InventoryModel
{
    private static ?array $medicineColumnsCache = null;
    private static ?int $pharmacyIdCache = null;

    private static function ensureMedicineColumns(): void
    {
        if (!self::tableExists('medicines')) {
            return;
        }

        if (!self::columnExists('medicines', 'pricing')) {
            Database::iud("ALTER TABLE medicines ADD COLUMN pricing DECIMAL(10,2) NOT NULL DEFAULT 0");
            self::$medicineColumnsCache = null;
        }

        if (self::columnExists('medicines', 'price') && self::columnExists('medicines', 'pricing')) {
            Database::iud("UPDATE medicines SET pricing = price WHERE (pricing IS NULL OR pricing = 0) AND price IS NOT NULL");
            self::$medicineColumnsCache = null;
        }
    }

    private static function medicinePriceColumn(): string
    {
        if (self::hasMedicineColumn('pricing')) {
            return 'pricing';
        }
        if (self::hasMedicineColumn('price')) {
            return 'price';
        }
        return '';
    }

    private static function currentPharmacyId(): int
    {
        if (self::$pharmacyIdCache !== null) {
            return self::$pharmacyIdCache;
        }
        $auth = Auth::getUser();
        $fromToken = (int)($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            self::$pharmacyIdCache = $fromToken;
            return $fromToken;
        }
        self::$pharmacyIdCache = PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
        return self::$pharmacyIdCache;
    }

    private static function tableExists(string $name): bool
    {
        $safe = Database::escape($name);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::escape($table);
        $safeColumn = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeColumn'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function selectExpr(string $table, string $column, string $fallback = "''"): string
    {
        return self::columnExists($table, $column) ? $column : "$fallback AS $column";
    }

    private static function medicineColumns(): array
    {
        if (self::$medicineColumnsCache !== null) {
            return self::$medicineColumnsCache;
        }

        $cols = [];
        if (!self::tableExists('medicines')) {
            self::$medicineColumnsCache = $cols;
            return $cols;
        }

        $rs = Database::search("SHOW COLUMNS FROM medicines");
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $field = strtolower((string)($row['Field'] ?? ''));
                if ($field !== '') {
                    $cols[$field] = true;
                }
            }
        }

        self::$medicineColumnsCache = $cols;
        return $cols;
    }

    private static function hasMedicineColumn(string $column): bool
    {
        $cols = self::medicineColumns();
        return isset($cols[strtolower($column)]);
    }

    private static function getMedicineImageColumn(): string
    {
        $candidates = ['image_path', 'image', 'image_url', 'medicine_image', 'photo'];
        foreach ($candidates as $col) {
            if (self::hasMedicineColumn($col)) {
                return $col;
            }
        }

        if (self::tableExists('medicines')) {
            Database::iud("ALTER TABLE medicines ADD COLUMN image_path VARCHAR(255) NULL");
            self::$medicineColumnsCache = null;
            if (self::hasMedicineColumn('image_path')) {
                return 'image_path';
            }
        }

        return '';
    }

    private static function extractImagePath(array $row): string
    {
        foreach (['image_path', 'image', 'image_url', 'medicine_image', 'photo'] as $col) {
            $value = trim((string)($row[$col] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }
        return '';
    }

    private static function saveUploadedImage(?array $file): string
    {
        if (!$file || !isset($file['error']) || (int)$file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            return '';
        }

        $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            return '';
        }

        $uploadDir = ROOT . '/public/uploads/medicines';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        if (!is_dir($uploadDir)) {
            return '';
        }

        $name = 'medicine_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $uploadDir . '/' . $name;

        if (!@move_uploaded_file((string)$file['tmp_name'], $dest)) {
            return '';
        }

        return 'uploads/medicines/' . $name;
    }

    private static function resolveCategoryName(int $categoryId): string
    {
        if ($categoryId <= 0) {
            return '';
        }

        $tables = ['categories', 'category'];
        foreach ($tables as $table) {
            if (!self::tableExists($table)) {
                continue;
            }

            $idCol = self::columnExists($table, 'id') ? 'id' : (self::columnExists($table, 'category_id') ? 'category_id' : '');
            $nameCol = self::columnExists($table, 'name') ? 'name' : (self::columnExists($table, 'category_name') ? 'category_name' : '');
            if ($idCol === '' || $nameCol === '') {
                continue;
            }

            $rs = Database::search("SELECT `$nameCol` AS n FROM `$table` WHERE `$idCol` = $categoryId LIMIT 1");
            if ($rs instanceof mysqli_result) {
                $row = $rs->fetch_assoc();
                if (!empty($row['n'])) {
                    return (string)$row['n'];
                }
            }
        }

        return '';
    }

    private static function ensureLookupTables(): void
    {
        Database::setUpConnection();

        Database::iud("CREATE TABLE IF NOT EXISTS dosage_forms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        )");

        Database::iud("CREATE TABLE IF NOT EXISTS selling_units (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        )");

        Database::iud("CREATE TABLE IF NOT EXISTS medicine_brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL UNIQUE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        )");

        Database::iud("CREATE TABLE IF NOT EXISTS medicine_manufacturers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL UNIQUE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        )");

        Database::iud("INSERT IGNORE INTO dosage_forms(name) VALUES
            ('Tablet'), ('Capsule'), ('Syrup'), ('Suspension'), ('Injection'), ('Cream'),
            ('Ointment'), ('Drops'), ('Inhaler'), ('Powder')");
        Database::iud("INSERT IGNORE INTO selling_units(name) VALUES
            ('Item'), ('Strip'), ('Bottle'), ('Box'), ('Tube'), ('Vial'), ('Sachet'), ('Pack')");
    }

    private static function normalizeOption(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private static function resolveSelectableValue(array $input, string $existingKey, string $newKey, string $fallback = ''): string
    {
        $existing = self::normalizeOption((string)($input[$existingKey] ?? ''));
        $new = self::normalizeOption((string)($input[$newKey] ?? ''));
        $fallback = self::normalizeOption($fallback);
        if ($new !== '') return $new;
        if ($existing !== '') return $existing;
        return $fallback;
    }

    private static function upsertLookupValue(string $table, string $name): void
    {
        $name = self::normalizeOption($name);
        if ($name === '' || !self::tableExists($table)) {
            return;
        }
        $safe = Database::escape($name);
        Database::iud("INSERT IGNORE INTO `$table` (`name`) VALUES ('$safe')");
    }

    private static function getLookupValues(string $table): array
    {
        Database::setUpConnection();
        self::ensureLookupTables();
        if (!self::tableExists($table)) {
            return [];
        }
        $rs = Database::search("SELECT name FROM `$table` WHERE is_active = 1 ORDER BY name ASC");
        $rows = [];
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $val = trim((string)($row['name'] ?? ''));
                if ($val !== '') {
                    $rows[] = $val;
                }
            }
        }
        return $rows;
    }

    public static function getDosageForms(): array
    {
        return self::getLookupValues('dosage_forms');
    }

    public static function getSellingUnits(): array
    {
        return self::getLookupValues('selling_units');
    }

    public static function getBrands(): array
    {
        Database::setUpConnection();
        self::ensureLookupTables();

        $brands = self::getLookupValues('medicine_brands');
        if (self::tableExists('medicines') && self::hasMedicineColumn('name')) {
            $where = "TRIM(COALESCE(name,'')) <> ''";
            if (self::hasMedicineColumn('pharmacy_id')) {
                $pid = self::currentPharmacyId();
                if ($pid <= 0) {
                    return [];
                }
                $where .= " AND pharmacy_id = $pid";
            }
            $rs = Database::search("SELECT DISTINCT name FROM medicines WHERE $where ORDER BY name ASC");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $name = self::normalizeOption((string)($row['name'] ?? ''));
                    if ($name !== '' && !in_array($name, $brands, true)) {
                        $brands[] = $name;
                    }
                }
            }
            sort($brands);
        }
        return $brands;
    }

    public static function getManufacturers(): array
    {
        Database::setUpConnection();
        self::ensureLookupTables();

        $makers = self::getLookupValues('medicine_manufacturers');
        if (self::tableExists('medicines') && self::hasMedicineColumn('manufacturer')) {
            $where = "TRIM(COALESCE(manufacturer,'')) <> ''";
            if (self::hasMedicineColumn('pharmacy_id')) {
                $pid = self::currentPharmacyId();
                if ($pid <= 0) {
                    return [];
                }
                $where .= " AND pharmacy_id = $pid";
            }
            $rs = Database::search("SELECT DISTINCT manufacturer FROM medicines WHERE $where ORDER BY manufacturer ASC");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $name = self::normalizeOption((string)($row['manufacturer'] ?? ''));
                    if ($name !== '' && !in_array($name, $makers, true)) {
                        $makers[] = $name;
                    }
                }
            }
            sort($makers);
        }
        return $makers;
    }

    public static function getCategories(): array
    {
        Database::setUpConnection();
        $tables = ['categories', 'category'];

        foreach ($tables as $table) {
            if (!self::tableExists($table)) {
                continue;
            }

            $idCol = self::columnExists($table, 'id') ? 'id' : (self::columnExists($table, 'category_id') ? 'category_id' : '');
            $nameCol = self::columnExists($table, 'name') ? 'name' : (self::columnExists($table, 'category_name') ? 'category_name' : '');
            if ($idCol === '' || $nameCol === '') {
                continue;
            }

            $rs = Database::search("SELECT `$idCol` AS id, `$nameCol` AS name FROM `$table` ORDER BY `$nameCol` ASC");
            $rows = [];
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
            return $rows;
        }

        return [];
    }

    public static function getAll(string $search = ''): array
    {
        Database::setUpConnection();
        self::ensureMedicineColumns();

        if (!self::tableExists('medicines')) {
            return [];
        }

        $hasPharmacyId = self::hasMedicineColumn('pharmacy_id');
        $currentPharmacyId = self::currentPharmacyId();
        if ($hasPharmacyId && $currentPharmacyId <= 0) {
            return [];
        }

        $safeSearch = Database::escape($search);
        $hasGenericName = self::columnExists('medicines', 'generic_name');
        $hasMedicineName = self::columnExists('medicines', 'med_name');
        $where = '';
        if ($safeSearch !== '') {
            $parts = ["name LIKE '%$safeSearch%'"];
            if ($hasMedicineName) {
                $parts[] = "med_name LIKE '%$safeSearch%'";
            }
            if ($hasGenericName) {
                $parts[] = "generic_name LIKE '%$safeSearch%'";
            }
            $where = 'WHERE ' . implode(' OR ', $parts);
        }
        if ($hasPharmacyId) {
            $where = $where === ''
                ? ("WHERE pharmacy_id = " . $currentPharmacyId)
                : ($where . " AND pharmacy_id = " . $currentPharmacyId);
        }

        $select = implode(",\n                   ", [
            self::selectExpr('medicines', 'id', '0'),
            self::selectExpr('medicines', 'name'),
            self::selectExpr('medicines', 'med_name'),
            self::selectExpr('medicines', 'generic_name'),
            self::selectExpr('medicines', 'category'),
            self::selectExpr('medicines', 'strength'),
            self::selectExpr('medicines', 'dosage_form'),
            self::selectExpr('medicines', 'quantity_in_stock', '0'),
            ($priceCol = self::medicinePriceColumn()) !== '' ? "$priceCol AS price" : "0 AS price",
            ($imgCol = self::getMedicineImageColumn()) !== '' ? "$imgCol AS image_path" : "'' AS image_path",
            self::selectExpr('medicines', 'manufacturer'),
            self::selectExpr('medicines', 'expiry_date'),
        ]);

        $rs = Database::search("\n            SELECT $select\n            FROM medicines\n            $where\n            ORDER BY name ASC\n        ");

        $rows = [];
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function getById(int $id): ?array
    {
        Database::setUpConnection();
        self::ensureMedicineColumns();
        if (!self::tableExists('medicines')) {
            return null;
        }

        $hasPharmacyId = self::hasMedicineColumn('pharmacy_id');
        $currentPharmacyId = self::currentPharmacyId();
        if ($hasPharmacyId && $currentPharmacyId <= 0) {
            return null;
        }

        $id = (int)$id;
        $where = "id = $id";
        if ($hasPharmacyId) {
            $where .= " AND pharmacy_id = " . $currentPharmacyId;
        }
        $rs = Database::search("SELECT * FROM medicines WHERE $where LIMIT 1");
        if (!($rs instanceof mysqli_result)) {
            return null;
        }
        $row = $rs->fetch_assoc();
        if (!is_array($row)) {
            return null;
        }
        if (!isset($row['price'])) {
            $priceCol = self::medicinePriceColumn();
            $row['price'] = $priceCol !== '' ? (float)($row[$priceCol] ?? 0) : 0;
        }
        return $row;
    }

    public static function create(array $input, ?array $file = null): bool
    {
        Database::setUpConnection();
        self::ensureLookupTables();
        self::ensureMedicineColumns();
        if (!self::tableExists('medicines')) {
            return false;
        }

        $hasPharmacyId = self::hasMedicineColumn('pharmacy_id');
        $currentPharmacyId = self::currentPharmacyId();
        if ($hasPharmacyId && $currentPharmacyId <= 0) {
            return false;
        }

        $name = self::resolveSelectableValue(
            $input,
            'brand_existing',
            'brand_new',
            trim((string)($input['name'] ?? ''))
        );
        if ($name === '') {
            return false;
        }
        $medName = trim((string)($input['med_name'] ?? ''));

        $imagePath = self::saveUploadedImage($file);

        $generic = trim((string)($input['generic_name'] ?? ''));
        $categoryId = (int)($input['category_id'] ?? 0);
        $category = trim((string)($input['category'] ?? ''));
        if ($category === '' && $categoryId > 0) {
            $category = self::resolveCategoryName($categoryId);
        }

        $description = trim((string)($input['description'] ?? ''));
        $dosageForm = self::resolveSelectableValue(
            $input,
            'dosage_form_existing',
            'dosage_form_new',
            trim((string)($input['dosage_form'] ?? ''))
        );
        $strength = trim((string)($input['strength'] ?? ''));
        $manufacturer = self::resolveSelectableValue(
            $input,
            'manufacturer_existing',
            'manufacturer_new',
            trim((string)($input['manufacturer'] ?? ''))
        );
        $expiryDate = trim((string)($input['expiry_date'] ?? ''));
        $qty = (int)($input['quantity_in_stock'] ?? 0);
        $price = (float)($input['price'] ?? 0);
        $sellingUnit = self::resolveSelectableValue(
            $input,
            'selling_unit_existing',
            'selling_unit_new',
            trim((string)($input['selling_unit'] ?? ''))
        );
        $unitQuantity = (int)($input['unit_quantity'] ?? 1);
        $addedBy = (int)($input['added_by'] ?? 0);

        $cols = [];
        $vals = [];

        $addStr = function(string $col, string $val) use (&$cols, &$vals) {
            $cols[] = $col;
            $vals[] = "'" . Database::escape($val) . "'";
        };
        $addInt = function(string $col, int $val) use (&$cols, &$vals) {
            $cols[] = $col;
            $vals[] = (string)$val;
        };
        $addNum = function(string $col, float $val) use (&$cols, &$vals) {
            $cols[] = $col;
            $vals[] = (string)$val;
        };

        if (self::hasMedicineColumn('name')) $addStr('name', $name);
        if (self::hasMedicineColumn('med_name')) $addStr('med_name', $medName);
        if (self::hasMedicineColumn('generic_name')) $addStr('generic_name', $generic);
        if (self::hasMedicineColumn('category')) $addStr('category', $category);
        if (self::hasMedicineColumn('category_id')) $addInt('category_id', max(0, $categoryId));
        if (self::hasMedicineColumn('description')) $addStr('description', $description);
        if (self::hasMedicineColumn('dosage_form')) $addStr('dosage_form', $dosageForm);
        if (self::hasMedicineColumn('strength')) $addStr('strength', $strength);
        if (self::hasMedicineColumn('quantity_in_stock')) $addInt('quantity_in_stock', max(0, $qty));
        $priceCol = self::medicinePriceColumn();
        if ($priceCol !== '') $addNum($priceCol, max(0, $price));
        $imageColumn = self::getMedicineImageColumn();
        if ($imageColumn !== '') $addStr($imageColumn, $imagePath);
        if (self::hasMedicineColumn('manufacturer')) $addStr('manufacturer', $manufacturer);
        if (self::hasMedicineColumn('expiry_date')) {
            $cols[] = 'expiry_date';
            $vals[] = $expiryDate !== '' ? "'" . Database::escape($expiryDate) . "'" : 'NULL';
        }
        if (self::hasMedicineColumn('selling_unit')) $addStr('selling_unit', $sellingUnit);
        if (self::hasMedicineColumn('unit_quantity')) $addInt('unit_quantity', max(1, $unitQuantity));
        if (self::hasMedicineColumn('added_by')) $addInt('added_by', max(0, $addedBy));
        if ($hasPharmacyId) $addInt('pharmacy_id', $currentPharmacyId);
        if (self::hasMedicineColumn('created_at')) {
            $cols[] = 'created_at';
            $vals[] = 'NOW()';
        }

        if (empty($cols)) {
            return false;
        }

        $sql = "INSERT INTO medicines (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";
        $ok = Database::iud($sql);
        if ($ok) {
            self::upsertLookupValue('medicine_brands', $name);
            self::upsertLookupValue('dosage_forms', $dosageForm);
            self::upsertLookupValue('selling_units', $sellingUnit);
            self::upsertLookupValue('medicine_manufacturers', $manufacturer);
        }
        return $ok;
    }

    public static function update(int $id, array $input, ?array $file = null): bool
    {
        Database::setUpConnection();
        self::ensureLookupTables();
        self::ensureMedicineColumns();
        if (!self::tableExists('medicines')) {
            return false;
        }

        $hasPharmacyId = self::hasMedicineColumn('pharmacy_id');
        $currentPharmacyId = self::currentPharmacyId();
        if ($hasPharmacyId && $currentPharmacyId <= 0) {
            return false;
        }

        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        $existing = self::getById($id);
        if (!$existing) {
            return false;
        }

        $newImagePath = self::saveUploadedImage($file);
        $imagePath = $newImagePath !== '' ? $newImagePath : self::extractImagePath($existing);

        $name = self::resolveSelectableValue(
            $input,
            'brand_existing',
            'brand_new',
            trim((string)($input['name'] ?? ($existing['name'] ?? '')))
        );
        if ($name === '') {
            return false;
        }
        $medName = trim((string)($input['med_name'] ?? ($existing['med_name'] ?? '')));

        $generic = trim((string)($input['generic_name'] ?? ($existing['generic_name'] ?? '')));
        $categoryId = (int)($input['category_id'] ?? ($existing['category_id'] ?? 0));
        $category = trim((string)($input['category'] ?? ($existing['category'] ?? '')));
        if ($category === '' && $categoryId > 0) {
            $category = self::resolveCategoryName($categoryId);
        }

        $description = trim((string)($input['description'] ?? ($existing['description'] ?? '')));
        $dosageForm = self::resolveSelectableValue(
            $input,
            'dosage_form_existing',
            'dosage_form_new',
            trim((string)($input['dosage_form'] ?? ($existing['dosage_form'] ?? '')))
        );
        $strength = trim((string)($input['strength'] ?? ($existing['strength'] ?? '')));
        $manufacturer = self::resolveSelectableValue(
            $input,
            'manufacturer_existing',
            'manufacturer_new',
            trim((string)($input['manufacturer'] ?? ($existing['manufacturer'] ?? '')))
        );
        $expiryDate = trim((string)($input['expiry_date'] ?? ($existing['expiry_date'] ?? '')));
        $qty = (int)($input['quantity_in_stock'] ?? ($existing['quantity_in_stock'] ?? 0));
        $existingPrice = (float)($existing['price'] ?? ($existing['pricing'] ?? 0));
        $price = (float)($input['price'] ?? $existingPrice);
        $sellingUnit = self::resolveSelectableValue(
            $input,
            'selling_unit_existing',
            'selling_unit_new',
            trim((string)($input['selling_unit'] ?? ($existing['selling_unit'] ?? '')))
        );
        $unitQuantity = (int)($input['unit_quantity'] ?? ($existing['unit_quantity'] ?? 1));

        $sets = [];
        $setStr = function(string $col, string $val) use (&$sets) {
            $sets[] = "$col = '" . Database::escape($val) . "'";
        };
        $setInt = function(string $col, int $val) use (&$sets) {
            $sets[] = "$col = $val";
        };
        $setNum = function(string $col, float $val) use (&$sets) {
            $sets[] = "$col = $val";
        };

        if (self::hasMedicineColumn('name')) $setStr('name', $name);
        if (self::hasMedicineColumn('med_name')) $setStr('med_name', $medName);
        if (self::hasMedicineColumn('generic_name')) $setStr('generic_name', $generic);
        if (self::hasMedicineColumn('category')) $setStr('category', $category);
        if (self::hasMedicineColumn('category_id')) $setInt('category_id', max(0, $categoryId));
        if (self::hasMedicineColumn('description')) $setStr('description', $description);
        if (self::hasMedicineColumn('dosage_form')) $setStr('dosage_form', $dosageForm);
        if (self::hasMedicineColumn('strength')) $setStr('strength', $strength);
        if (self::hasMedicineColumn('quantity_in_stock')) $setInt('quantity_in_stock', max(0, $qty));
        $priceCol = self::medicinePriceColumn();
        if ($priceCol !== '') $setNum($priceCol, max(0, $price));
        $imageColumn = self::getMedicineImageColumn();
        if ($imageColumn !== '') $setStr($imageColumn, $imagePath);
        if (self::hasMedicineColumn('manufacturer')) $setStr('manufacturer', $manufacturer);
        if (self::hasMedicineColumn('expiry_date')) {
            $sets[] = $expiryDate !== ''
                ? "expiry_date = '" . Database::escape($expiryDate) . "'"
                : 'expiry_date = NULL';
        }
        if (self::hasMedicineColumn('selling_unit')) $setStr('selling_unit', $sellingUnit);
        if (self::hasMedicineColumn('unit_quantity')) $setInt('unit_quantity', max(1, $unitQuantity));
        if (self::hasMedicineColumn('updated_at')) $sets[] = 'updated_at = NOW()';

        if (empty($sets)) {
            return false;
        }

        $where = "id = $id";
        if ($hasPharmacyId) {
            $where .= " AND pharmacy_id = " . $currentPharmacyId;
        }
        $sql = "UPDATE medicines SET " . implode(', ', $sets) . " WHERE $where";
        $ok = Database::iud($sql);
        if ($ok) {
            self::upsertLookupValue('medicine_brands', $name);
            self::upsertLookupValue('dosage_forms', $dosageForm);
            self::upsertLookupValue('selling_units', $sellingUnit);
            self::upsertLookupValue('medicine_manufacturers', $manufacturer);
        }
        return $ok;
    }

    public static function delete(int $id): bool
    {
        Database::setUpConnection();
        if (!self::tableExists('medicines')) {
            return false;
        }

        $hasPharmacyId = self::hasMedicineColumn('pharmacy_id');
        $currentPharmacyId = self::currentPharmacyId();
        if ($hasPharmacyId && $currentPharmacyId <= 0) {
            return false;
        }

        $id = (int)$id;
        $where = "id = $id";
        if ($hasPharmacyId) {
            $where .= " AND pharmacy_id = " . $currentPharmacyId;
        }
        return Database::iud("DELETE FROM medicines WHERE $where");
    }
}
