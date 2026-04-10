<?php
/**
 * Patient Shop Model
 * Java reference: /router/shop
 */
class ShopModel
{
    private static function medicinePriceExpr(string $alias = 'm'): string
    {
        if (self::columnExists('medicines', 'pricing')) {
            return "$alias.pricing";
        }
        if (self::columnExists('medicines', 'price')) {
            return "$alias.price";
        }
        return "0";
    }

    private static function pharmacyIndex(): array
    {
        $rows = PharmacyContext::getPharmacies();
        $out = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $out[$id] = [
                'name' => (string)($row['name'] ?? ''),
                'lat' => (float)($row['latitude'] ?? 0),
                'lng' => (float)($row['longitude'] ?? 0),
            ];
        }
        return $out;
    }

    private static function nearestBranchNameWithStock(int $selectedPharmacyId, array $branchIds, array $pharmacyIndex): string
    {
        if (empty($branchIds)) {
            return '';
        }

        $selected = $pharmacyIndex[$selectedPharmacyId] ?? null;
        $bestId = 0;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($branchIds as $pid) {
            $pid = (int)$pid;
            if ($pid <= 0 || $pid === $selectedPharmacyId) {
                continue;
            }
            if (!isset($pharmacyIndex[$pid])) {
                continue;
            }

            if ($selected) {
                $dLat = ((float)$pharmacyIndex[$pid]['lat']) - ((float)$selected['lat']);
                $dLng = ((float)$pharmacyIndex[$pid]['lng']) - ((float)$selected['lng']);
                $distance = ($dLat * $dLat) + ($dLng * $dLng);
            } else {
                $distance = 0.0;
            }

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestId = $pid;
            }
        }

        if ($bestId > 0 && isset($pharmacyIndex[$bestId])) {
            return (string)($pharmacyIndex[$bestId]['name'] ?? '');
        }

        $firstId = (int)($branchIds[0] ?? 0);
        if ($firstId > 0 && isset($pharmacyIndex[$firstId])) {
            return (string)($pharmacyIndex[$firstId]['name'] ?? '');
        }

        return '';
    }

    private static function currentPharmacyId(): int
    {
        $pid = PharmacyContext::selectedPharmacyId();
        if ($pid > 0) {
            return $pid;
        }

        $auth = Auth::getUser();
        $patientNic = (string)($auth['nic'] ?? '');
        if ($patientNic !== '' && PharmacyContext::patientHasSelection($patientNic)) {
            return PharmacyContext::selectedPharmacyId();
        }

        return 0;
    }

    private static function pharmacyCondition(string $alias, string $table): string
    {
        $pid = self::currentPharmacyId();
        if (!PharmacyContext::tableHasPharmacyId($table)) {
            return '1=1';
        }
        if ($pid <= 0) {
            return '0=1';
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    private static function tableExists(string $table): bool
    {
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        $safeTable = Database::escape($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs && $rs->num_rows > 0;
    }

    private static function findCategoryTable(): ?array
    {
        $candidates = [
            ['table' => 'categories', 'id' => 'id', 'name' => 'name'],
            ['table' => 'categories', 'id' => 'category_id', 'name' => 'category_name'],
            ['table' => 'category', 'id' => 'id', 'name' => 'name'],
            ['table' => 'category', 'id' => 'category_id', 'name' => 'category_name'],
        ];

        foreach ($candidates as $c) {
            if (
                self::tableExists($c['table']) &&
                self::columnExists($c['table'], $c['id']) &&
                self::columnExists($c['table'], $c['name'])
            ) {
                return $c;
            }
        }

        return null;
    }

    public static function getCategories(): array
    {
        $catTable = self::findCategoryTable();
        if ($catTable) {
            $table = $catTable['table'];
            $idCol = $catTable['id'];
            $nameCol = $catTable['name'];

            $rs = Database::search("
                SELECT DISTINCT `$nameCol` AS category
                FROM `$table`
                WHERE `$nameCol` IS NOT NULL AND `$nameCol` <> ''
                ORDER BY `$nameCol` ASC
            ");

            if ($rs) {
                $rows = [];
                while ($row = $rs->fetch_assoc()) {
                    $rows[] = (string)$row['category'];
                }
                return $rows;
            }
        }

        if (!self::tableExists('medicines')) {
            return [];
        }

        if (!self::columnExists('medicines', 'category')) {
            return [];
        }

        $rs = Database::search("
            SELECT DISTINCT category
            FROM medicines
            WHERE category IS NOT NULL AND category <> ''
            ORDER BY category ASC
        ");
        if (!$rs) {
            return [];
        }

        $rows = [];
        while ($row = $rs->fetch_assoc()) {
            $rows[] = $row['category'];
        }
        return $rows;
    }

    public static function getMedicines(string $category = '', string $q = ''): array
    {
        if (!self::tableExists('medicines')) {
            return [];
        }

        $hasId = self::columnExists('medicines', 'id');
        $hasName = self::columnExists('medicines', 'name');
        if (!$hasName) {
            return [];
        }

        $hasCategoryText = self::columnExists('medicines', 'category');
        $hasCategoryId = self::columnExists('medicines', 'category_id');
        $hasGeneric = self::columnExists('medicines', 'generic_name');
        $hasDosage = self::columnExists('medicines', 'dosage_form');
        $hasStrength = self::columnExists('medicines', 'strength');
        $hasQty = self::columnExists('medicines', 'quantity_in_stock');
        $priceExpr = self::medicinePriceExpr('m');
        $imageExpr = "NULL";
        foreach (['image_path', 'image', 'image_url', 'medicine_image', 'photo'] as $imageCol) {
            if (self::columnExists('medicines', $imageCol)) {
                $imageExpr = "m.$imageCol";
                break;
            }
        }
        $hasDescription = self::columnExists('medicines', 'description');
        $hasManufacturer = self::columnExists('medicines', 'manufacturer');
        $hasSellingUnit = self::columnExists('medicines', 'selling_unit');
        $hasUnitQty = self::columnExists('medicines', 'unit_quantity');

        $catTable = self::findCategoryTable();
        $joinCategory = '';
        $categoryLabelExpr = "'General'";
        if ($hasCategoryId && $catTable) {
            $table = $catTable['table'];
            $idCol = $catTable['id'];
            $nameCol = $catTable['name'];
            $joinCategory = "LEFT JOIN `$table` c ON m.category_id = c.`$idCol`";
            if ($hasCategoryText) {
                $categoryLabelExpr = "COALESCE(NULLIF(m.category, ''), c.`$nameCol`, 'General')";
            } else {
                $categoryLabelExpr = "COALESCE(c.`$nameCol`, 'General')";
            }
        } elseif ($hasCategoryText) {
            $categoryLabelExpr = "COALESCE(NULLIF(m.category, ''), 'General')";
        }

        $where = [];
        if ($category !== '') {
            $safeCategory = Database::escape($category);
            if ($hasCategoryText && $joinCategory !== '') {
                $where[] = "(m.category = '$safeCategory' OR c.`{$catTable['name']}` = '$safeCategory')";
            } elseif ($hasCategoryText) {
                $where[] = "m.category = '$safeCategory'";
            } elseif ($joinCategory !== '') {
                $where[] = "c.`{$catTable['name']}` = '$safeCategory'";
            }
        }

        if ($q !== '') {
            $safeQ = Database::escape($q);
            $parts = [];
            $parts[] = "m.name LIKE '%$safeQ%'";
            if ($hasGeneric) $parts[] = "m.generic_name LIKE '%$safeQ%'";
            if ($hasCategoryText) $parts[] = "m.category LIKE '%$safeQ%'";
            if ($joinCategory !== '') $parts[] = "c.`{$catTable['name']}` LIKE '%$safeQ%'";
            if (!empty($parts)) {
                $where[] = '(' . implode(' OR ', $parts) . ')';
            }
        }

        $whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

        $selectedPharmacyId = self::currentPharmacyId();
        $pharmacyIndex = self::pharmacyIndex();
        $selectedPharmacyName = (string)($pharmacyIndex[$selectedPharmacyId]['name'] ?? '');

        $rs = Database::search("
            SELECT
                " . ($hasId ? "m.id" : "0") . " AS id,
                " . (self::columnExists('medicines', 'pharmacy_id') ? "m.pharmacy_id" : "0") . " AS pharmacy_id,
                m.name,
                " . ($hasGeneric ? "m.generic_name" : "NULL") . " AS generic_name,
                $categoryLabelExpr AS category,
                " . ($hasDosage ? "m.dosage_form" : "NULL") . " AS dosage_form,
                " . ($hasStrength ? "m.strength" : "NULL") . " AS strength,
                " . ($hasQty ? "m.quantity_in_stock" : "NULL") . " AS quantity_in_stock,
                $priceExpr AS price,
                $imageExpr AS image_path,
                " . ($hasDescription ? "m.description" : "NULL") . " AS description,
                " . ($hasManufacturer ? "m.manufacturer" : "NULL") . " AS manufacturer,
                " . ($hasSellingUnit ? "m.selling_unit" : "'Item'") . " AS selling_unit,
                " . ($hasUnitQty ? "m.unit_quantity" : "1") . " AS unit_quantity
            FROM medicines m
            $joinCategory
            $whereSql
            ORDER BY m.name ASC
        ");

        if (!$rs) {
            return [];
        }

        $groups = [];
        while ($row = $rs->fetch_assoc()) {
            $name = trim((string)($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $generic = trim((string)($row['generic_name'] ?? ''));
            $dosage = trim((string)($row['dosage_form'] ?? ''));
            $strength = trim((string)($row['strength'] ?? ''));
            $key = strtolower($name . '|' . $generic . '|' . $dosage . '|' . $strength);

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'first' => $row,
                    'selected' => null,
                    'in_stock_branch_ids' => [],
                    'image_row' => null,
                ];
            }

            $pid = (int)($row['pharmacy_id'] ?? 0);
            $stock = max(0, (int)($row['quantity_in_stock'] ?? 0));

            if ($selectedPharmacyId > 0 && $pid === $selectedPharmacyId) {
                $groups[$key]['selected'] = $row;
            }

            if ($stock > 0 && $pid > 0) {
                $groups[$key]['in_stock_branch_ids'][$pid] = true;
            }

            $img = trim((string)($row['image_path'] ?? ''));
            if ($img !== '' && $groups[$key]['image_row'] === null) {
                $groups[$key]['image_row'] = $row;
            }
        }

        $rows = [];
        foreach ($groups as $g) {
            $first = $g['first'];
            $selected = $g['selected'];
            $display = $selected ?: $first;

            $displayImg = trim((string)($display['image_path'] ?? ''));
            if ($displayImg === '' && is_array($g['image_row'])) {
                $display['image_path'] = (string)($g['image_row']['image_path'] ?? '');
            }

            $selectedStock = $selected ? max(0, (int)($selected['quantity_in_stock'] ?? 0)) : 0;
            $selectedMedicineId = $selected ? (int)($selected['id'] ?? 0) : 0;

            $branchIds = array_map('intval', array_keys($g['in_stock_branch_ids']));
            $nearestBranchName = '';
            if ($selectedStock <= 0) {
                $nearestBranchName = self::nearestBranchNameWithStock($selectedPharmacyId, $branchIds, $pharmacyIndex);
            }

            $display['id'] = (int)($display['id'] ?? 0); // representative id for view/recent
            $display['cart_id'] = $selectedMedicineId;   // add-to-cart id for selected branch
            $display['quantity_in_stock'] = $selectedStock;
            $display['selected_branch_name'] = $selectedPharmacyName;
            $display['available_branch_name'] = $nearestBranchName;

            $rows[] = $display;
        }

        usort($rows, static function (array $a, array $b): int {
            return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
        });

        return $rows;
    }

    public static function getMedicineById(int $id): ?array
    {
        $id = (int)$id;
        if ($id <= 0) {
            return null;
        }
        $rows = self::getMedicines('', '');
        foreach ($rows as $row) {
            if ((int)($row['id'] ?? 0) === $id) {
                return $row;
            }
        }
        return null;
    }

    public static function getSelectedBranchMedicineById(int $id): ?array
    {
        $id = (int)$id;
        if ($id <= 0 || !self::tableExists('medicines')) {
            return null;
        }

        $selectedPharmacyId = self::currentPharmacyId();
        if ($selectedPharmacyId <= 0 || !self::columnExists('medicines', 'pharmacy_id')) {
            return null;
        }

        $priceExpr = self::columnExists('medicines', 'pricing')
            ? 'pricing AS price'
            : (self::columnExists('medicines', 'price') ? 'price' : '0 AS price');
        $rs = Database::search("
            SELECT id, name, quantity_in_stock, $priceExpr
            FROM medicines
            WHERE id = $id
              AND pharmacy_id = $selectedPharmacyId
            LIMIT 1
        ");

        if (!$rs instanceof mysqli_result) {
            return null;
        }

        $row = $rs->fetch_assoc();
        return is_array($row) ? $row : null;
    }

    public static function getMedicinesByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $need = [];
        foreach ($ids as $id) {
            $num = (int)$id;
            if ($num > 0) {
                $need[$num] = true;
            }
        }
        if (empty($need)) {
            return [];
        }

        $all = self::getMedicines('', '');
        $indexed = [];
        foreach ($all as $row) {
            $mid = (int)($row['id'] ?? 0);
            if ($mid > 0 && isset($need[$mid])) {
                $indexed[$mid] = $row;
            }
        }

        $ordered = [];
        foreach ($ids as $id) {
            $num = (int)$id;
            if ($num > 0 && isset($indexed[$num])) {
                $ordered[] = $indexed[$num];
            }
        }
        return $ordered;
    }

    public static function getSuggestions(int $limit = 6, array $excludeIds = []): array
    {
        $limit = max(1, min(20, (int)$limit));
        $exclude = [];
        foreach ($excludeIds as $id) {
            $num = (int)$id;
            if ($num > 0) {
                $exclude[$num] = true;
            }
        }

        $all = self::getMedicines('', '');
        $out = [];
        foreach ($all as $row) {
            $mid = (int)($row['id'] ?? 0);
            if ($mid > 0 && isset($exclude[$mid])) {
                continue;
            }
            $out[] = $row;
            if (count($out) >= $limit) {
                break;
            }
        }
        return $out;
    }

    public static function getOrderHistory(string $patientNic): array
    {
        if (!self::tableExists('prescriptions')) {
            return [];
        }

        $nic = Database::escape($patientNic);
        $rs = Database::search("
            SELECT id, file_name, status, uploaded_at
            FROM prescriptions
            WHERE patient_nic = '$nic'
              AND " . self::pharmacyCondition('prescriptions', 'prescriptions') . "
            ORDER BY uploaded_at DESC
        ");

        if (!$rs) {
            return [];
        }

        $rows = [];
        while ($row = $rs->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
