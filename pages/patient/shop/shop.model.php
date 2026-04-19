<?php
/**
 * Patient Shop Model
 * Java reference: /router/shop
 */
require_once ROOT . '/core/PharmacyOrderSupport.php';

class ShopModel
{
    private static function medicinePriceExpr(string $alias = 'm'): string
    {
        return "$alias.pricing";
    }

    private static function pharmacyIndex(): array
    {
        $rows = PharmacyContext::getPharmacies();
        $out = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $out[$id] = [
                'name' => (string) ($row['name'] ?? ''),
                'lat' => (float) ($row['latitude'] ?? 0),
                'lng' => (float) ($row['longitude'] ?? 0),
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
            $pid = (int) $pid;
            if ($pid <= 0 || $pid === $selectedPharmacyId) {
                continue;
            }
            if (!isset($pharmacyIndex[$pid])) {
                continue;
            }

            if ($selected) {
                $dLat = ((float) $pharmacyIndex[$pid]['lat']) - ((float) $selected['lat']);
                $dLng = ((float) $pharmacyIndex[$pid]['lng']) - ((float) $selected['lng']);
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
            return (string) ($pharmacyIndex[$bestId]['name'] ?? '');
        }

        $firstId = (int) ($branchIds[0] ?? 0);
        if ($firstId > 0 && isset($pharmacyIndex[$firstId])) {
            return (string) ($pharmacyIndex[$firstId]['name'] ?? '');
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
        $patientNic = (string) ($auth['nic'] ?? '');
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

    public static function getCategories(): array
    {
        $rows = Database::fetchAll("
            SELECT name
            FROM categories
            WHERE is_active = 1
            ORDER BY name ASC
        ");
        if (empty($rows)) {
            return [];
        }

        $categories = [];
        foreach ($rows as $row) {
            $categories[] = (string) ($row['name'] ?? '');
        }
        return $categories;
    }

    public static function getMedicines(string $category = '', string $q = ''): array
    {
        $priceExpr = self::medicinePriceExpr('m');

        $where = [];
        $types = '';
        $params = [];
        if ($category !== '') {
            $where[] = "m.category = ?";
            $types .= 's';
            $params[] = $category;
        }

        if ($q !== '') {
            $like = '%' . $q . '%';
            $where[] = "(m.name LIKE ? OR m.med_name LIKE ? OR m.generic_name LIKE ? OR m.category LIKE ?)";
            $types .= 'ssss';
            array_push($params, $like, $like, $like, $like);
        }

        $whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

        $selectedPharmacyId = self::currentPharmacyId();
        $pharmacyIndex = self::pharmacyIndex();
        $selectedPharmacyName = (string) ($pharmacyIndex[$selectedPharmacyId]['name'] ?? '');

        $rs = Database::fetchAll("
            SELECT
                m.id AS id,
                m.pharmacy_id AS pharmacy_id,
                m.name,
                m.med_name AS med_name,
                m.generic_name AS generic_name,
                COALESCE(NULLIF(c.name, ''), NULLIF(m.category, ''), 'General') AS category,
                m.dosage_form AS dosage_form,
                m.strength AS strength,
                m.quantity_in_stock AS quantity_in_stock,
                $priceExpr AS price,
                COALESCE(m.image_path, '') AS image_path,
                m.description AS description,
                m.manufacturer AS manufacturer,
                'Item' AS selling_unit,
                1 AS unit_quantity
            FROM medicines m
            LEFT JOIN categories c ON c.id = m.category_id
            $whereSql
            ORDER BY m.name ASC
        ", $types, $params);

        if (empty($rs)) {
            return [];
        }

        $groups = [];
        foreach ($rs as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $medName = trim((string) ($row['med_name'] ?? ''));
            if ($name === '' && $medName === '') {
                continue;
            }

            $generic = trim((string) ($row['generic_name'] ?? ''));
            $dosage = trim((string) ($row['dosage_form'] ?? ''));
            $strength = trim((string) ($row['strength'] ?? ''));
            $key = strtolower($medName . '|' . $name . '|' . $generic . '|' . $dosage . '|' . $strength);

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'first' => $row,
                    'selected' => null,
                    'in_stock_branch_ids' => [],
                    'image_row' => null,
                ];
            }

            $pid = (int) ($row['pharmacy_id'] ?? 0);
            $stock = max(0, (int) ($row['quantity_in_stock'] ?? 0));

            if ($selectedPharmacyId > 0 && $pid === $selectedPharmacyId) {
                $groups[$key]['selected'] = $row;
            }

            if ($stock > 0 && $pid > 0) {
                $groups[$key]['in_stock_branch_ids'][$pid] = true;
            }

            $img = trim((string) ($row['image_path'] ?? ''));
            if ($img !== '' && $groups[$key]['image_row'] === null) {
                $groups[$key]['image_row'] = $row;
            }
        }

        $rows = [];
        foreach ($groups as $g) {
            $first = $g['first'];
            $selected = $g['selected'];
            $display = $selected ?: $first;

            $displayImg = trim((string) ($display['image_path'] ?? ''));
            if ($displayImg === '' && is_array($g['image_row'])) {
                $display['image_path'] = (string) ($g['image_row']['image_path'] ?? '');
            }

            $selectedStock = $selected ? max(0, (int) ($selected['quantity_in_stock'] ?? 0)) : 0;
            $selectedMedicineId = $selected ? (int) ($selected['id'] ?? 0) : 0;

            $branchIds = array_map('intval', array_keys($g['in_stock_branch_ids']));
            $nearestBranchName = '';
            if ($selectedStock <= 0) {
                $nearestBranchName = self::nearestBranchNameWithStock($selectedPharmacyId, $branchIds, $pharmacyIndex);
            }

            $display['id'] = (int) ($display['id'] ?? 0); 
            $display['cart_id'] = $selectedMedicineId; 
            $display['quantity_in_stock'] = $selectedStock;
            $display['selected_branch_name'] = $selectedPharmacyName;
            $display['available_branch_name'] = $nearestBranchName;

            $rows[] = $display;
        }

        usort($rows, static function (array $a, array $b): int {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $rows;
    }

    public static function getMedicineById(int $id): ?array
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }
        $rows = self::getMedicines('', '');
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }
        return null;
    }

    public static function getSelectedBranchMedicineById(int $id): ?array
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        $selectedPharmacyId = self::currentPharmacyId();
        if ($selectedPharmacyId <= 0) {
            return null;
        }

        return Database::fetchOne("
            SELECT id, name, quantity_in_stock, pricing AS price
            FROM medicines
            WHERE id = ?
              AND pharmacy_id = ?
            LIMIT 1
        ", 'ii', [$id, $selectedPharmacyId]);
    }

    public static function getMedicinesByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $need = [];
        foreach ($ids as $id) {
            $num = (int) $id;
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
            $mid = (int) ($row['id'] ?? 0);
            if ($mid > 0 && isset($need[$mid])) {
                $indexed[$mid] = $row;
            }
        }

        $ordered = [];
        foreach ($ids as $id) {
            $num = (int) $id;
            if ($num > 0 && isset($indexed[$num])) {
                $ordered[] = $indexed[$num];
            }
        }
        return $ordered;
    }

    public static function getSuggestions(int $limit = 4, array $excludeIds = []): array
    {
        $limit = max(1, min(20, (int) $limit));
        $exclude = [];
        foreach ($excludeIds as $id) {
            $num = (int) $id;
            if ($num > 0) {
                $exclude[$num] = true;
            }
        }

        $all = self::getMedicines('', '');
        $out = [];
        foreach ($all as $row) {
            $mid = (int) ($row['id'] ?? 0);
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

    public static function getSuggestionsByViewedCategories(array $recentIds, int $limit = 4): array
    {
        $limit = max(1, min(4, (int) $limit));
        $exclude = [];
        foreach ($recentIds as $id) {
            $num = (int) $id;
            if ($num > 0) {
                $exclude[$num] = true;
            }
        }

        if (empty($recentIds)) {
            return array_slice(self::getSuggestions($limit, []), 0, $limit);
        }

        $recentRows = self::getMedicinesByIds($recentIds);
        $categoryOrder = [];
        foreach ($recentRows as $row) {
            $category = trim((string) ($row['category'] ?? ''));
            if ($category !== '' && !in_array($category, $categoryOrder, true)) {
                $categoryOrder[] = $category;
            }
        }

        if (empty($categoryOrder)) {
            return array_slice(self::getSuggestions($limit, $recentIds), 0, $limit);
        }

        $all = self::getMedicines('', '');
        $matches = [];
        foreach ($all as $row) {
            $mid = (int) ($row['id'] ?? 0);
            if ($mid > 0 && isset($exclude[$mid])) {
                continue;
            }

            $category = trim((string) ($row['category'] ?? ''));
            if ($category === '' || !in_array($category, $categoryOrder, true)) {
                continue;
            }

            $matches[] = $row;
        }

        usort($matches, static function (array $a, array $b) use ($categoryOrder): int {
            $aCategory = trim((string) ($a['category'] ?? ''));
            $bCategory = trim((string) ($b['category'] ?? ''));
            $aCategoryRank = array_search($aCategory, $categoryOrder, true);
            $bCategoryRank = array_search($bCategory, $categoryOrder, true);
            if ($aCategoryRank === false) {
                $aCategoryRank = PHP_INT_MAX;
            }
            if ($bCategoryRank === false) {
                $bCategoryRank = PHP_INT_MAX;
            }

            if ($aCategoryRank !== $bCategoryRank) {
                return $aCategoryRank <=> $bCategoryRank;
            }

            $aStock = (int) ($a['quantity_in_stock'] ?? 0);
            $bStock = (int) ($b['quantity_in_stock'] ?? 0);
            if ($aStock !== $bStock) {
                return $bStock <=> $aStock;
            }

            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return array_slice($matches, 0, $limit);
    }

    public static function getOrderHistory(string $patientNic): array
    {
        return PharmacyOrderSupport::getPatientOrders($patientNic);
    }
}
