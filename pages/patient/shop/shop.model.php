<?php
/**
 * Patient Shop Model
 * Java reference: /router/shop
 */
class ShopModel
{
    private static function currentPharmacyId(): int
    {
        return PharmacyContext::selectedPharmacyId();
    }

    private static function pharmacyCondition(string $alias, string $table): string
    {
        $pid = self::currentPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId($table)) {
            return '1=1';
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
        $hasPrice = self::columnExists('medicines', 'price');
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

        $where[] = self::pharmacyCondition('m', 'medicines');

        $whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

        $rs = Database::search("
            SELECT
                " . ($hasId ? "m.id" : "0") . " AS id,
                m.name,
                " . ($hasGeneric ? "m.generic_name" : "NULL") . " AS generic_name,
                $categoryLabelExpr AS category,
                " . ($hasDosage ? "m.dosage_form" : "NULL") . " AS dosage_form,
                " . ($hasStrength ? "m.strength" : "NULL") . " AS strength,
                " . ($hasQty ? "m.quantity_in_stock" : "NULL") . " AS quantity_in_stock,
                " . ($hasPrice ? "m.price" : "0") . " AS price,
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

        $rows = [];
        while ($row = $rs->fetch_assoc()) {
            $rows[] = $row;
        }
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
