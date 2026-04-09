<?php
/**
 * Medicine Inventory Model
 */
class InventoryModel
{
    private static ?array $medicineColumnsCache = null;
    private static ?int $pharmacyIdCache = null;

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

        if (!self::tableExists('medicines')) {
            return [];
        }

        $safeSearch = Database::escape($search);
        $hasGenericName = self::columnExists('medicines', 'generic_name');
        $where = '';
        if ($safeSearch !== '') {
            $where = $hasGenericName
                ? "WHERE name LIKE '%$safeSearch%' OR generic_name LIKE '%$safeSearch%'"
                : "WHERE name LIKE '%$safeSearch%'";
        }
        if (self::hasMedicineColumn('pharmacy_id') && self::currentPharmacyId() > 0) {
            $where = $where === ''
                ? ("WHERE pharmacy_id = " . self::currentPharmacyId())
                : ($where . " AND pharmacy_id = " . self::currentPharmacyId());
        }

        $select = implode(",\n                   ", [
            self::selectExpr('medicines', 'id', '0'),
            self::selectExpr('medicines', 'name'),
            self::selectExpr('medicines', 'generic_name'),
            self::selectExpr('medicines', 'category'),
            self::selectExpr('medicines', 'strength'),
            self::selectExpr('medicines', 'dosage_form'),
            self::selectExpr('medicines', 'quantity_in_stock', '0'),
            self::selectExpr('medicines', 'price', '0'),
            self::selectExpr('medicines', 'image_path'),
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
        if (!self::tableExists('medicines')) {
            return null;
        }

        $id = (int)$id;
        $where = "id = $id";
        if (self::hasMedicineColumn('pharmacy_id') && self::currentPharmacyId() > 0) {
            $where .= " AND pharmacy_id = " . self::currentPharmacyId();
        }
        $rs = Database::search("SELECT * FROM medicines WHERE $where LIMIT 1");
        return ($rs instanceof mysqli_result) ? $rs->fetch_assoc() : null;
    }

    public static function create(array $input, ?array $file = null): bool
    {
        Database::setUpConnection();
        if (!self::tableExists('medicines')) {
            return false;
        }

        $name = trim((string)($input['name'] ?? ''));
        if ($name === '') {
            return false;
        }

        $imagePath = self::saveUploadedImage($file);

        $generic = trim((string)($input['generic_name'] ?? ''));
        $categoryId = (int)($input['category_id'] ?? 0);
        $category = trim((string)($input['category'] ?? ''));
        if ($category === '' && $categoryId > 0) {
            $category = self::resolveCategoryName($categoryId);
        }

        $description = trim((string)($input['description'] ?? ''));
        $dosageForm = trim((string)($input['dosage_form'] ?? ''));
        $strength = trim((string)($input['strength'] ?? ''));
        $manufacturer = trim((string)($input['manufacturer'] ?? ''));
        $expiryDate = trim((string)($input['expiry_date'] ?? ''));
        $qty = (int)($input['quantity_in_stock'] ?? 0);
        $price = (float)($input['price'] ?? 0);
        $sellingUnit = trim((string)($input['selling_unit'] ?? ''));
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
        if (self::hasMedicineColumn('generic_name')) $addStr('generic_name', $generic);
        if (self::hasMedicineColumn('category')) $addStr('category', $category);
        if (self::hasMedicineColumn('category_id')) $addInt('category_id', max(0, $categoryId));
        if (self::hasMedicineColumn('description')) $addStr('description', $description);
        if (self::hasMedicineColumn('dosage_form')) $addStr('dosage_form', $dosageForm);
        if (self::hasMedicineColumn('strength')) $addStr('strength', $strength);
        if (self::hasMedicineColumn('quantity_in_stock')) $addInt('quantity_in_stock', max(0, $qty));
        if (self::hasMedicineColumn('price')) $addNum('price', max(0, $price));
        if (self::hasMedicineColumn('image_path')) $addStr('image_path', $imagePath);
        if (self::hasMedicineColumn('manufacturer')) $addStr('manufacturer', $manufacturer);
        if (self::hasMedicineColumn('expiry_date')) {
            $cols[] = 'expiry_date';
            $vals[] = $expiryDate !== '' ? "'" . Database::escape($expiryDate) . "'" : 'NULL';
        }
        if (self::hasMedicineColumn('selling_unit')) $addStr('selling_unit', $sellingUnit);
        if (self::hasMedicineColumn('unit_quantity')) $addInt('unit_quantity', max(1, $unitQuantity));
        if (self::hasMedicineColumn('added_by')) $addInt('added_by', max(0, $addedBy));
        if (self::hasMedicineColumn('pharmacy_id') && self::currentPharmacyId() > 0) $addInt('pharmacy_id', self::currentPharmacyId());
        if (self::hasMedicineColumn('created_at')) {
            $cols[] = 'created_at';
            $vals[] = 'NOW()';
        }

        if (empty($cols)) {
            return false;
        }

        $sql = "INSERT INTO medicines (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";
        return Database::iud($sql);
    }

    public static function update(int $id, array $input, ?array $file = null): bool
    {
        Database::setUpConnection();
        if (!self::tableExists('medicines')) {
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
        $imagePath = $newImagePath !== '' ? $newImagePath : (string)($existing['image_path'] ?? '');

        $name = trim((string)($input['name'] ?? ($existing['name'] ?? '')));
        if ($name === '') {
            return false;
        }

        $generic = trim((string)($input['generic_name'] ?? ($existing['generic_name'] ?? '')));
        $categoryId = (int)($input['category_id'] ?? ($existing['category_id'] ?? 0));
        $category = trim((string)($input['category'] ?? ($existing['category'] ?? '')));
        if ($category === '' && $categoryId > 0) {
            $category = self::resolveCategoryName($categoryId);
        }

        $description = trim((string)($input['description'] ?? ($existing['description'] ?? '')));
        $dosageForm = trim((string)($input['dosage_form'] ?? ($existing['dosage_form'] ?? '')));
        $strength = trim((string)($input['strength'] ?? ($existing['strength'] ?? '')));
        $manufacturer = trim((string)($input['manufacturer'] ?? ($existing['manufacturer'] ?? '')));
        $expiryDate = trim((string)($input['expiry_date'] ?? ($existing['expiry_date'] ?? '')));
        $qty = (int)($input['quantity_in_stock'] ?? ($existing['quantity_in_stock'] ?? 0));
        $price = (float)($input['price'] ?? ($existing['price'] ?? 0));
        $sellingUnit = trim((string)($input['selling_unit'] ?? ($existing['selling_unit'] ?? '')));
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
        if (self::hasMedicineColumn('generic_name')) $setStr('generic_name', $generic);
        if (self::hasMedicineColumn('category')) $setStr('category', $category);
        if (self::hasMedicineColumn('category_id')) $setInt('category_id', max(0, $categoryId));
        if (self::hasMedicineColumn('description')) $setStr('description', $description);
        if (self::hasMedicineColumn('dosage_form')) $setStr('dosage_form', $dosageForm);
        if (self::hasMedicineColumn('strength')) $setStr('strength', $strength);
        if (self::hasMedicineColumn('quantity_in_stock')) $setInt('quantity_in_stock', max(0, $qty));
        if (self::hasMedicineColumn('price')) $setNum('price', max(0, $price));
        if (self::hasMedicineColumn('image_path')) $setStr('image_path', $imagePath);
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
        if (self::hasMedicineColumn('pharmacy_id') && self::currentPharmacyId() > 0) {
            $where .= " AND pharmacy_id = " . self::currentPharmacyId();
        }
        $sql = "UPDATE medicines SET " . implode(', ', $sets) . " WHERE $where";
        return Database::iud($sql);
    }

    public static function delete(int $id): bool
    {
        Database::setUpConnection();
        if (!self::tableExists('medicines')) {
            return false;
        }

        $id = (int)$id;
        $where = "id = $id";
        if (self::hasMedicineColumn('pharmacy_id') && self::currentPharmacyId() > 0) {
            $where .= " AND pharmacy_id = " . self::currentPharmacyId();
        }
        return Database::iud("DELETE FROM medicines WHERE $where");
    }
}
