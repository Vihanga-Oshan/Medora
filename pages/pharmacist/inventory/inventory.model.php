<?php
/**
 * Medicine Inventory Model
 */
class InventoryModel
{
    private static ?array $medicineColumnsCache = null;
    private static ?int $pharmacyIdCache = null;

    private const ALLOWED_SORTS = [
        'name',
        'stock',
        'value',
        'expiry',
        'updated',
        'supplier',
    ];

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

    private static function medicineColumns(): array
    {
        if (self::$medicineColumnsCache !== null) {
            return self::$medicineColumnsCache;
        }

        $cols = array_fill_keys([
            'id',
            'name',
            'med_name',
            'generic_name',
            'category',
            'category_id',
            'description',
            'dosage_form',
            'strength',
            'quantity_in_stock',
            'low_stock_threshold',
            'reorder_quantity',
            'pricing',
            'price',
            'unit_cost',
            'manufacturer',
            'supplier_id',
            'batch_number',
            'expiry_date',
            'last_restocked_at',
            'added_by',
            'created_at',
            'updated_at',
            'pharmacy_id',
            'selling_unit',
            'unit_quantity',
        ], true);

        self::$medicineColumnsCache = $cols;
        return $cols;
    }

    private static function hasMedicineColumn(string $column): bool
    {
        return isset(self::medicineColumns()[strtolower($column)]);
    }

    private static function currentPharmacyId(): int
    {
        if (self::$pharmacyIdCache !== null) {
            return self::$pharmacyIdCache;
        }

        $auth = Auth::getUser();
        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            self::$pharmacyIdCache = $fromToken;
            return $fromToken;
        }

        self::$pharmacyIdCache = PharmacyContext::resolvePharmacistPharmacyId((int) ($auth['id'] ?? 0));
        return self::$pharmacyIdCache;
    }

    private static function currentUserId(): int
    {
        $auth = Auth::getUser();
        return (int) ($auth['id'] ?? 0);
    }

    private static function getMedicineImageColumn(): string
    {
        return '';
    }

    private static function extractImagePath(array $row): string
    {
        foreach (['image_path', 'image', 'image_url', 'medicine_image', 'photo'] as $col) {
            $value = trim((string) ($row[$col] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }
        return '';
    }

    private static function saveUploadedImage(?array $file): string
    {
        if (!$file || !isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            return '';
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
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
        if (!@move_uploaded_file((string) $file['tmp_name'], $dest)) {
            return '';
        }

        return 'uploads/medicines/' . $name;
    }

    private static function normalizeOption(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private static function resolveSelectableValue(array $input, string $existingKey, string $newKey, string $fallback = ''): string
    {
        $existing = self::normalizeOption((string) ($input[$existingKey] ?? ''));
        $new = self::normalizeOption((string) ($input[$newKey] ?? ''));
        $fallback = self::normalizeOption($fallback);
        if ($new !== '') {
            return $new;
        }
        if ($existing !== '') {
            return $existing;
        }
        return $fallback;
    }

    private static function upsertLookupValue(string $table, string $name): void
    {
        $name = self::normalizeOption($name);
        if ($name === '') {
            return;
        }
        Database::execute("INSERT IGNORE INTO `$table` (`name`) VALUES (?)", 's', [$name]);
    }

    private static function getLookupValues(string $table): array
    {
        $rows = Database::fetchAll("SELECT name FROM `$table` WHERE is_active = ? ORDER BY name ASC", 'i', [1]);
        $values = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row['name'] ?? ''));
            if ($value !== '') {
                $values[] = $value;
            }
        }
        return $values;
    }

    private static function resolveCategoryName(int $categoryId): string
    {
        if ($categoryId <= 0) {
            return '';
        }
        $row = Database::fetchOne("SELECT name AS n FROM categories WHERE id = ? LIMIT 1", 'i', [$categoryId]);
        return (string) ($row['n'] ?? '');
    }

    private static function resolveSupplierId(array $input, ?array $existingMedicine = null): int
    {
        $existingId = (int) ($input['supplier_existing'] ?? 0);
        if ($existingId > 0) {
            return $existingId;
        }

        $supplierName = self::normalizeOption((string) ($input['supplier_new'] ?? ''));
        if ($supplierName === '' && $existingMedicine) {
            return (int) ($existingMedicine['supplier_id'] ?? 0);
        }
        if ($supplierName === '') {
            return 0;
        }

        $contact = self::normalizeOption((string) ($input['supplier_contact_person'] ?? ''));
        $phone = self::normalizeOption((string) ($input['supplier_phone'] ?? ''));
        $email = self::normalizeOption((string) ($input['supplier_email'] ?? ''));
        $address = self::normalizeOption((string) ($input['supplier_address'] ?? ''));
        $leadTime = max(0, (int) ($input['supplier_lead_time_days'] ?? 0));

        $row = Database::fetchOne("SELECT id FROM medicine_suppliers WHERE LOWER(name) = LOWER(?) LIMIT 1", 's', [$supplierName]);
        if ($row) {
            $supplierId = (int) ($row['id'] ?? 0);
            Database::execute(
                "UPDATE medicine_suppliers
                 SET contact_person = CASE WHEN ? <> '' THEN ? ELSE contact_person END,
                     phone = CASE WHEN ? <> '' THEN ? ELSE phone END,
                     email = CASE WHEN ? <> '' THEN ? ELSE email END,
                     address = CASE WHEN ? <> '' THEN ? ELSE address END,
                     lead_time_days = CASE WHEN ? > 0 THEN ? ELSE lead_time_days END,
                     is_active = 1,
                     updated_at = NOW()
                 WHERE id = ?",
                'ssssssssiii',
                [$contact, $contact, $phone, $phone, $email, $email, $address, $address, $leadTime, $leadTime, $supplierId]
            );
            return $supplierId;
        }
        $ok = Database::execute(
            "INSERT INTO medicine_suppliers (name, contact_person, phone, email, address, lead_time_days, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
            'sssssi',
            [$supplierName, $contact, $phone, $email, $address, $leadTime]
        );
        if (!$ok) {
            return 0;
        }

        $inserted = Database::fetchOne("SELECT id FROM medicine_suppliers WHERE LOWER(name) = LOWER(?) LIMIT 1", 's', [$supplierName]);
        return (int) ($inserted['id'] ?? 0);
    }

    private static function classifyMedicine(array $medicine): array
    {
        $qty = (int) ($medicine['quantity_in_stock'] ?? 0);
        $threshold = max(0, (int) ($medicine['low_stock_threshold'] ?? 0));
        $expiry = trim((string) ($medicine['expiry_date'] ?? ''));

        $statusKey = 'healthy';
        $statusLabel = 'Healthy';
        if ($qty <= 0) {
            $statusKey = 'out';
            $statusLabel = 'Out of stock';
        } elseif ($threshold > 0 && $qty <= $threshold) {
            $statusKey = 'low';
            $statusLabel = 'Low stock';
        }

        $daysToExpiry = null;
        if ($expiry !== '') {
            $expTs = strtotime($expiry);
            if ($expTs !== false) {
                $daysToExpiry = (int) floor(($expTs - strtotime(date('Y-m-d'))) / 86400);
            }
        }

        $medicine['stock_status_key'] = $statusKey;
        $medicine['stock_status_label'] = $statusLabel;
        $medicine['is_low_stock'] = $statusKey === 'low';
        $medicine['is_out_of_stock'] = $statusKey === 'out';
        $medicine['days_to_expiry'] = $daysToExpiry;
        $medicine['is_expiring_soon'] = $daysToExpiry !== null && $daysToExpiry >= 0 && $daysToExpiry <= 30;
        $medicine['total_stock_value'] = (float) ($medicine['unit_cost'] ?? 0) * $qty;
        return $medicine;
    }

    private static function decorateMedicineRows(array $rows): array
    {
        $decorated = [];
        foreach ($rows as $row) {
            $decorated[] = self::classifyMedicine($row);
        }
        return $decorated;
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
        $brands = self::getLookupValues('medicine_brands');
        $pid = self::currentPharmacyId();
        if (self::hasMedicineColumn('pharmacy_id') && $pid <= 0) {
            return [];
        }

        $sql = "SELECT DISTINCT name FROM medicines WHERE TRIM(COALESCE(name, '')) <> ''";
        $types = '';
        $params = [];
        if (self::hasMedicineColumn('pharmacy_id')) {
            $sql .= " AND pharmacy_id = ?";
            $types = 'i';
            $params[] = $pid;
        }
        $sql .= " ORDER BY name ASC";
        $rows = Database::fetchAll($sql, $types, $params);
        foreach ($rows as $row) {
            $name = self::normalizeOption((string) ($row['name'] ?? ''));
            if ($name !== '' && !in_array($name, $brands, true)) {
                $brands[] = $name;
            }
        }
        sort($brands);
        return $brands;
    }

    public static function getManufacturers(): array
    {
        $manufacturers = self::getLookupValues('medicine_manufacturers');
        $pid = self::currentPharmacyId();
        if (self::hasMedicineColumn('pharmacy_id') && $pid <= 0) {
            return [];
        }

        $sql = "SELECT DISTINCT manufacturer FROM medicines WHERE TRIM(COALESCE(manufacturer, '')) <> ''";
        $types = '';
        $params = [];
        if (self::hasMedicineColumn('pharmacy_id')) {
            $sql .= " AND pharmacy_id = ?";
            $types = 'i';
            $params[] = $pid;
        }
        $sql .= " ORDER BY manufacturer ASC";
        $rows = Database::fetchAll($sql, $types, $params);
        foreach ($rows as $row) {
            $name = self::normalizeOption((string) ($row['manufacturer'] ?? ''));
            if ($name !== '' && !in_array($name, $manufacturers, true)) {
                $manufacturers[] = $name;
            }
        }
        sort($manufacturers);
        return $manufacturers;
    }

    public static function getSuppliers(): array
    {
        return Database::fetchAll(
            "SELECT id, name, contact_person, phone, email, address, lead_time_days
             FROM medicine_suppliers
             WHERE is_active = 1
             ORDER BY name ASC"
        );
    }

    public static function getCategories(): array
    {
        return Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");
    }

    private static function sanitizeListOptions(array $options): array
    {
        $search = trim((string) ($options['search'] ?? ''));
        $status = trim((string) ($options['status'] ?? 'all'));
        if (!in_array($status, ['all', 'low', 'out', 'expiring', 'healthy'], true)) {
            $status = 'all';
        }

        $supplierId = max(0, (int) ($options['supplier_id'] ?? 0));
        $categoryId = max(0, (int) ($options['category_id'] ?? 0));

        $sortBy = strtolower(trim((string) ($options['sort_by'] ?? 'stock')));
        if (!in_array($sortBy, self::ALLOWED_SORTS, true)) {
            $sortBy = 'stock';
        }

        $sortDir = strtolower(trim((string) ($options['sort_dir'] ?? 'asc')));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'asc';
        }

        $page = max(1, (int) ($options['page'] ?? 1));
        $perPage = (int) ($options['per_page'] ?? 15);
        if (!in_array($perPage, [10, 15, 25, 50], true)) {
            $perPage = 15;
        }

        return [
            'search' => $search,
            'status' => $status,
            'supplier_id' => $supplierId,
            'category_id' => $categoryId,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    private static function buildInventoryWhere(array $options, string &$types, array &$params): array
    {
        $where = [];
        $pharmacyId = self::currentPharmacyId();

        if (self::hasMedicineColumn('pharmacy_id')) {
            if ($pharmacyId <= 0) {
                $where[] = '1 = 0';
                return $where;
            }
            $where[] = 'm.pharmacy_id = ?';
            $types .= 'i';
            $params[] = $pharmacyId;
        }

        if ($options['search'] !== '') {
            $like = '%' . $options['search'] . '%';
            $where[] = "(m.name LIKE ? OR m.med_name LIKE ? OR m.generic_name LIKE ? OR m.category LIKE ? OR COALESCE(s.name, '') LIKE ?)";
            $types .= 'sssss';
            array_push($params, $like, $like, $like, $like, $like);
        }

        if ((int) $options['supplier_id'] > 0 && self::hasMedicineColumn('supplier_id')) {
            $where[] = 'm.supplier_id = ?';
            $types .= 'i';
            $params[] = (int) $options['supplier_id'];
        }

        if ((int) $options['category_id'] > 0) {
            if (self::hasMedicineColumn('category_id')) {
                $where[] = 'm.category_id = ?';
                $types .= 'i';
                $params[] = (int) $options['category_id'];
            } elseif (self::hasMedicineColumn('category')) {
                $categoryName = self::resolveCategoryName((int) $options['category_id']);
                if ($categoryName !== '') {
                    $where[] = 'm.category = ?';
                    $types .= 's';
                    $params[] = $categoryName;
                }
            }
        }

        $status = (string) ($options['status'] ?? 'all');
        if ($status === 'low') {
            $where[] = 'm.quantity_in_stock > 0 AND m.quantity_in_stock <= COALESCE(m.low_stock_threshold, 0)';
        } elseif ($status === 'out') {
            $where[] = 'm.quantity_in_stock <= 0';
        } elseif ($status === 'expiring') {
            $where[] = "m.expiry_date IS NOT NULL AND m.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        } elseif ($status === 'healthy') {
            $where[] = 'm.quantity_in_stock > COALESCE(m.low_stock_threshold, 0)';
        }

        return $where;
    }

    private static function buildInventoryOrderClause(string $sortBy, string $sortDir): string
    {
        $dir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        if ($sortBy === 'name') {
            return "ORDER BY COALESCE(NULLIF(m.med_name, ''), m.name) $dir, m.id DESC";
        }
        if ($sortBy === 'value') {
            return "ORDER BY (COALESCE(m.unit_cost, 0) * COALESCE(m.quantity_in_stock, 0)) $dir, COALESCE(NULLIF(m.med_name, ''), m.name) ASC";
        }
        if ($sortBy === 'expiry') {
            return "ORDER BY (m.expiry_date IS NULL) ASC, m.expiry_date $dir, COALESCE(NULLIF(m.med_name, ''), m.name) ASC";
        }
        if ($sortBy === 'updated') {
            return "ORDER BY COALESCE(m.updated_at, m.created_at) $dir, m.id DESC";
        }
        if ($sortBy === 'supplier') {
            return "ORDER BY COALESCE(s.name, '') $dir, COALESCE(NULLIF(m.med_name, ''), m.name) ASC";
        }

        return "ORDER BY m.quantity_in_stock $dir, COALESCE(NULLIF(m.med_name, ''), m.name) ASC";
    }

    public static function getInventoryList(array $options = []): array
    {
        $opts = self::sanitizeListOptions($options);

        $priceCol = self::medicinePriceColumn();
        $types = '';
        $params = [];
        $where = self::buildInventoryWhere($opts, $types, $params);

        $fromSql = "FROM medicines m
                    LEFT JOIN medicine_suppliers s ON s.id = m.supplier_id";
        if (!empty($where)) {
            $fromSql .= ' WHERE ' . implode(' AND ', $where);
        }

        $countRow = Database::fetchOne("SELECT COUNT(*) AS c $fromSql", $types, $params);
        $total = (int) ($countRow['c'] ?? 0);

        $perPage = (int) $opts['per_page'];
        $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
        $page = min((int) $opts['page'], $totalPages);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT
                    m.*,
                    " . ($priceCol !== '' ? "m.$priceCol AS price" : "0 AS price") . ",
                    '' AS image_path,
                    COALESCE(m.unit_cost, 0) AS unit_cost,
                    COALESCE(m.low_stock_threshold, 0) AS low_stock_threshold,
                    COALESCE(m.reorder_quantity, 0) AS reorder_quantity,
                    s.name AS supplier_name,
                    s.contact_person AS supplier_contact_person,
                    s.phone AS supplier_phone,
                    s.email AS supplier_email,
                    s.address AS supplier_address,
                    s.lead_time_days AS supplier_lead_time_days
                $fromSql "
            . self::buildInventoryOrderClause((string) $opts['sort_by'], (string) $opts['sort_dir'])
            . ' LIMIT ' . max(1, $perPage) . ' OFFSET ' . max(0, $offset);

        $rows = self::decorateMedicineRows(Database::fetchAll($sql, $types, $params));

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'from' => $total > 0 ? ($offset + 1) : 0,
            'to' => $total > 0 ? min($offset + $perPage, $total) : 0,
            'options' => $opts,
        ];
    }

    public static function getAll(string $search = '', string $status = 'all'): array
    {
        $result = self::getInventoryList([
            'search' => $search,
            'status' => $status,
            'page' => 1,
            'per_page' => 1000,
            'sort_by' => 'stock',
            'sort_dir' => 'asc',
        ]);
        return $result['rows'] ?? [];
    }

    public static function getById(int $id): ?array
    {
        $pharmacyId = self::currentPharmacyId();
        if (self::hasMedicineColumn('pharmacy_id') && $pharmacyId <= 0) {
            return null;
        }

        $priceCol = self::medicinePriceColumn();
        $sql = "SELECT
                    m.*,
                    " . ($priceCol !== '' ? "m.$priceCol AS price" : "0 AS price") . ",
                    s.name AS supplier_name,
                    s.contact_person AS supplier_contact_person,
                    s.phone AS supplier_phone,
                    s.email AS supplier_email,
                    s.address AS supplier_address,
                    s.lead_time_days AS supplier_lead_time_days
                FROM medicines m
                LEFT JOIN medicine_suppliers s ON s.id = m.supplier_id
                WHERE m.id = ?";
        $types = 'i';
        $params = [$id];
        if (self::hasMedicineColumn('pharmacy_id')) {
            $sql .= " AND m.pharmacy_id = ?";
            $types .= 'i';
            $params[] = $pharmacyId;
        }

        $row = Database::fetchOne($sql . " LIMIT 1", $types, $params);
        if (!$row) {
            return null;
        }

        $row['image_path'] = self::extractImagePath($row);
        return self::classifyMedicine($row);
    }

    public static function getSummary(array $options = []): array
    {
        $opts = self::sanitizeListOptions($options);
        $types = '';
        $params = [];
        $where = self::buildInventoryWhere($opts, $types, $params);

        $sql = "SELECT
                    COUNT(*) AS total_items,
                    COALESCE(SUM(m.quantity_in_stock), 0) AS total_units,
                    COALESCE(SUM(CASE WHEN m.quantity_in_stock > 0 AND m.quantity_in_stock <= COALESCE(m.low_stock_threshold, 0) THEN 1 ELSE 0 END), 0) AS low_stock_count,
                    COALESCE(SUM(CASE WHEN m.quantity_in_stock <= 0 THEN 1 ELSE 0 END), 0) AS out_of_stock_count,
                    COALESCE(SUM(CASE WHEN m.expiry_date IS NOT NULL AND m.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END), 0) AS expiring_soon_count,
                    COALESCE(SUM(COALESCE(m.unit_cost, 0) * COALESCE(m.quantity_in_stock, 0)), 0) AS total_stock_value,
                    COUNT(DISTINCT CASE WHEN s.is_active = 1 THEN s.id END) AS supplier_count
                FROM medicines m
                LEFT JOIN medicine_suppliers s ON s.id = m.supplier_id";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $row = Database::fetchOne($sql, $types, $params) ?? [];
        return [
            'total_items' => (int) ($row['total_items'] ?? 0),
            'total_units' => (int) ($row['total_units'] ?? 0),
            'low_stock_count' => (int) ($row['low_stock_count'] ?? 0),
            'out_of_stock_count' => (int) ($row['out_of_stock_count'] ?? 0),
            'expiring_soon_count' => (int) ($row['expiring_soon_count'] ?? 0),
            'total_stock_value' => (float) ($row['total_stock_value'] ?? 0),
            'supplier_count' => (int) ($row['supplier_count'] ?? 0),
        ];
    }

    public static function getReorderRecommendations(int $limit = 8, array $options = []): array
    {
        $opts = self::sanitizeListOptions($options);
        $opts['status'] = 'all';

        $types = '';
        $params = [];
        $where = self::buildInventoryWhere($opts, $types, $params);
        $where[] = 'm.quantity_in_stock <= COALESCE(m.low_stock_threshold, 0)';

        $priceCol = self::medicinePriceColumn();
        $sql = "SELECT
                    m.id,
                    m.name,
                    m.med_name,
                    COALESCE(m.quantity_in_stock, 0) AS quantity_in_stock,
                    COALESCE(m.low_stock_threshold, 0) AS low_stock_threshold,
                    COALESCE(m.reorder_quantity, 0) AS reorder_quantity,
                    COALESCE(m.unit_cost, 0) AS unit_cost,
                    " . ($priceCol !== '' ? "m.$priceCol" : '0') . " AS price,
                    COALESCE(s.name, '') AS supplier_name,
                    COALESCE(s.phone, '') AS supplier_phone,
                    COALESCE(s.lead_time_days, 0) AS supplier_lead_time_days
                FROM medicines m
                LEFT JOIN medicine_suppliers s ON s.id = m.supplier_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY (COALESCE(m.low_stock_threshold, 0) - COALESCE(m.quantity_in_stock, 0)) DESC,
                         COALESCE(m.quantity_in_stock, 0) ASC,
                         COALESCE(NULLIF(m.med_name, ''), m.name) ASC
                LIMIT " . max(1, $limit);

        $rows = Database::fetchAll($sql, $types, $params);
        foreach ($rows as &$row) {
            $qty = (int) ($row['quantity_in_stock'] ?? 0);
            $threshold = (int) ($row['low_stock_threshold'] ?? 0);
            $reorderQty = max((int) ($row['reorder_quantity'] ?? 0), max(0, $threshold - $qty));
            $unitCost = (float) ($row['unit_cost'] ?? 0);
            $row['recommended_reorder_quantity'] = $reorderQty;
            $row['estimated_reorder_cost'] = $reorderQty * $unitCost;
        }
        unset($row);

        return $rows;
    }

    public static function getSupplierOverview(int $limit = 6): array
    {
        $pharmacyId = self::currentPharmacyId();
        $sql = "SELECT
                    s.id,
                    s.name,
                    s.contact_person,
                    s.phone,
                    s.email,
                    s.lead_time_days,
                    COUNT(m.id) AS medicine_count,
                    COALESCE(SUM(m.quantity_in_stock), 0) AS stocked_units
                FROM medicine_suppliers s
                LEFT JOIN medicines m ON m.supplier_id = s.id";
        $types = '';
        $params = [];
        if (self::hasMedicineColumn('pharmacy_id')) {
            $sql .= " AND m.pharmacy_id = ?";
            $types = 'i';
            $params[] = $pharmacyId;
        }
        $sql .= " WHERE s.is_active = 1
                  GROUP BY s.id, s.name, s.contact_person, s.phone, s.email, s.lead_time_days
                  ORDER BY stocked_units DESC, s.name ASC
                  LIMIT " . max(1, $limit);
        return Database::fetchAll($sql, $types, $params);
    }

    public static function getRecentMovements(int $limit = 8): array
    {
        $pharmacyId = self::currentPharmacyId();
        $sql = "SELECT
                    sm.id,
                    sm.movement_type,
                    sm.quantity_change,
                    sm.quantity_before,
                    sm.quantity_after,
                    sm.note,
                    sm.reference_no,
                    sm.created_at,
                    m.id AS medicine_id,
                    m.name,
                    m.med_name,
                    s.name AS supplier_name
                FROM medicine_stock_movements sm
                INNER JOIN medicines m ON m.id = sm.medicine_id
                LEFT JOIN medicine_suppliers s ON s.id = sm.supplier_id";
        $types = '';
        $params = [];
        if (self::hasMedicineColumn('pharmacy_id')) {
            $sql .= " WHERE sm.pharmacy_id = ?";
            $types = 'i';
            $params[] = $pharmacyId;
        }
        $sql .= " ORDER BY sm.created_at DESC, sm.id DESC LIMIT " . max(1, $limit);
        return Database::fetchAll($sql, $types, $params);
    }

    public static function create(array $input, ?array $file = null): bool
    {
        $pharmacyId = self::currentPharmacyId();
        if (self::hasMedicineColumn('pharmacy_id') && $pharmacyId <= 0) {
            return false;
        }

        $name = self::resolveSelectableValue($input, 'brand_existing', 'brand_new', trim((string) ($input['name'] ?? '')));
        $medName = trim((string) ($input['med_name'] ?? ''));
        $supplierId = self::resolveSupplierId($input);
        if ($name === '' || $medName === '' || $supplierId <= 0) {
            return false;
        }

        $imagePath = self::saveUploadedImage($file);
        $generic = trim((string) ($input['generic_name'] ?? ''));
        $categoryId = (int) ($input['category_id'] ?? 0);
        $category = trim((string) ($input['category'] ?? ''));
        if ($category === '' && $categoryId > 0) {
            $category = self::resolveCategoryName($categoryId);
        }

        $description = trim((string) ($input['description'] ?? ''));
        $dosageForm = self::resolveSelectableValue($input, 'dosage_form_existing', 'dosage_form_new', trim((string) ($input['dosage_form'] ?? '')));
        $strength = trim((string) ($input['strength'] ?? ''));
        $manufacturer = self::resolveSelectableValue($input, 'manufacturer_existing', 'manufacturer_new', trim((string) ($input['manufacturer'] ?? '')));
        $expiryDate = trim((string) ($input['expiry_date'] ?? ''));
        $qty = max(0, (int) ($input['quantity_in_stock'] ?? 0));
        $threshold = max(0, (int) ($input['low_stock_threshold'] ?? 10));
        $reorderQty = max(0, (int) ($input['reorder_quantity'] ?? 25));
        $price = max(0, (float) ($input['price'] ?? 0));
        $unitCost = max(0, (float) ($input['unit_cost'] ?? $price));
        $sellingUnit = self::resolveSelectableValue($input, 'selling_unit_existing', 'selling_unit_new', trim((string) ($input['selling_unit'] ?? '')));
        $unitQuantity = max(1, (int) ($input['unit_quantity'] ?? 1));
        $addedBy = (int) ($input['added_by'] ?? self::currentUserId());
        $batchNumber = trim((string) ($input['batch_number'] ?? ''));

        $cols = [];
        $vals = [];
        $types = '';
        $params = [];

        $addStr = function (string $col, string $val) use (&$cols, &$vals, &$types, &$params) {
            $cols[] = $col;
            $vals[] = '?';
            $types .= 's';
            $params[] = $val;
        };
        $addInt = function (string $col, int $val) use (&$cols, &$vals, &$types, &$params) {
            $cols[] = $col;
            $vals[] = '?';
            $types .= 'i';
            $params[] = $val;
        };
        $addNum = function (string $col, float $val) use (&$cols, &$vals, &$types, &$params) {
            $cols[] = $col;
            $vals[] = '?';
            $types .= 'd';
            $params[] = $val;
        };

        if (self::hasMedicineColumn('name')) {
            $addStr('name', $name);
        }
        if (self::hasMedicineColumn('med_name')) {
            $addStr('med_name', $medName);
        }
        if (self::hasMedicineColumn('generic_name')) {
            $addStr('generic_name', $generic);
        }
        if (self::hasMedicineColumn('category')) {
            $addStr('category', $category);
        }
        if (self::hasMedicineColumn('category_id')) {
            $addInt('category_id', max(0, $categoryId));
        }
        if (self::hasMedicineColumn('description')) {
            $addStr('description', $description);
        }
        if (self::hasMedicineColumn('dosage_form')) {
            $addStr('dosage_form', $dosageForm);
        }
        if (self::hasMedicineColumn('strength')) {
            $addStr('strength', $strength);
        }
        if (self::hasMedicineColumn('quantity_in_stock')) {
            $addInt('quantity_in_stock', $qty);
        }
        if (self::hasMedicineColumn('low_stock_threshold')) {
            $addInt('low_stock_threshold', $threshold);
        }
        if (self::hasMedicineColumn('reorder_quantity')) {
            $addInt('reorder_quantity', $reorderQty);
        }
        $priceCol = self::medicinePriceColumn();
        if ($priceCol !== '') {
            $addNum($priceCol, $price);
        }
        if (self::hasMedicineColumn('unit_cost')) {
            $addNum('unit_cost', $unitCost);
        }
        $imageColumn = self::getMedicineImageColumn();
        if ($imageColumn !== '' && $imagePath !== '') {
            $addStr($imageColumn, $imagePath);
        }
        if (self::hasMedicineColumn('manufacturer')) {
            $addStr('manufacturer', $manufacturer);
        }
        if (self::hasMedicineColumn('supplier_id')) {
            $addInt('supplier_id', $supplierId);
        }
        if (self::hasMedicineColumn('batch_number')) {
            $addStr('batch_number', $batchNumber);
        }
        if (self::hasMedicineColumn('expiry_date')) {
            $cols[] = 'expiry_date';
            if ($expiryDate !== '') {
                $vals[] = '?';
                $types .= 's';
                $params[] = $expiryDate;
            } else {
                $vals[] = 'NULL';
            }
        }
        if (self::hasMedicineColumn('last_restocked_at')) {
            $cols[] = 'last_restocked_at';
            $vals[] = $qty > 0 ? 'NOW()' : 'NULL';
        }
        if (self::hasMedicineColumn('selling_unit')) {
            $addStr('selling_unit', $sellingUnit);
        }
        if (self::hasMedicineColumn('unit_quantity')) {
            $addInt('unit_quantity', $unitQuantity);
        }
        if (self::hasMedicineColumn('added_by')) {
            $addInt('added_by', max(0, $addedBy));
        }
        if (self::hasMedicineColumn('pharmacy_id')) {
            $addInt('pharmacy_id', $pharmacyId);
        }
        if (self::hasMedicineColumn('created_at')) {
            $cols[] = 'created_at';
            $vals[] = 'NOW()';
        }
        if (self::hasMedicineColumn('updated_at')) {
            $cols[] = 'updated_at';
            $vals[] = 'NOW()';
        }

        if (empty($cols)) {
            return false;
        }

        $ok = Database::execute(
            "INSERT INTO medicines (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")",
            $types,
            $params
        );
        if (!$ok) {
            return false;
        }

        $inserted = Database::fetchOne(
            "SELECT id FROM medicines WHERE name = ? AND med_name = ? " . (self::hasMedicineColumn('pharmacy_id') ? "AND pharmacy_id = ? " : '') . "ORDER BY id DESC LIMIT 1",
            self::hasMedicineColumn('pharmacy_id') ? 'ssi' : 'ss',
            self::hasMedicineColumn('pharmacy_id') ? [$name, $medName, $pharmacyId] : [$name, $medName]
        );
        $medicineId = (int) ($inserted['id'] ?? 0);
        if ($medicineId > 0) {
            self::logStockMovement($medicineId, $supplierId, 'initial', $qty, 0, $qty, 'Initial stock entry', (string) ($input['reference_no'] ?? ''));
        }

        self::upsertLookupValue('medicine_brands', $name);
        self::upsertLookupValue('dosage_forms', $dosageForm);
        self::upsertLookupValue('selling_units', $sellingUnit);
        self::upsertLookupValue('medicine_manufacturers', $manufacturer);
        return true;
    }

    public static function update(int $id, array $input, ?array $file = null): bool
    {
        $pharmacyId = self::currentPharmacyId();
        if (self::hasMedicineColumn('pharmacy_id') && $pharmacyId <= 0) {
            return false;
        }

        $existing = self::getById((int) $id);
        if (!$existing) {
            return false;
        }

        $newImagePath = self::saveUploadedImage($file);
        $imagePath = $newImagePath !== '' ? $newImagePath : self::extractImagePath($existing);
        $name = self::resolveSelectableValue($input, 'brand_existing', 'brand_new', trim((string) ($input['name'] ?? ($existing['name'] ?? ''))));
        $medName = trim((string) ($input['med_name'] ?? ($existing['med_name'] ?? '')));
        $supplierId = self::resolveSupplierId($input, $existing);
        if ($name === '' || $medName === '' || $supplierId <= 0) {
            return false;
        }

        $generic = trim((string) ($input['generic_name'] ?? ($existing['generic_name'] ?? '')));
        $categoryId = (int) ($input['category_id'] ?? ($existing['category_id'] ?? 0));
        $category = trim((string) ($input['category'] ?? ($existing['category'] ?? '')));
        if ($category === '' && $categoryId > 0) {
            $category = self::resolveCategoryName($categoryId);
        }

        $description = trim((string) ($input['description'] ?? ($existing['description'] ?? '')));
        $dosageForm = self::resolveSelectableValue($input, 'dosage_form_existing', 'dosage_form_new', trim((string) ($input['dosage_form'] ?? ($existing['dosage_form'] ?? ''))));
        $strength = trim((string) ($input['strength'] ?? ($existing['strength'] ?? '')));
        $manufacturer = self::resolveSelectableValue($input, 'manufacturer_existing', 'manufacturer_new', trim((string) ($input['manufacturer'] ?? ($existing['manufacturer'] ?? ''))));
        $expiryDate = trim((string) ($input['expiry_date'] ?? ($existing['expiry_date'] ?? '')));
        $qty = max(0, (int) ($input['quantity_in_stock'] ?? ($existing['quantity_in_stock'] ?? 0)));
        $threshold = max(0, (int) ($input['low_stock_threshold'] ?? ($existing['low_stock_threshold'] ?? 10)));
        $reorderQty = max(0, (int) ($input['reorder_quantity'] ?? ($existing['reorder_quantity'] ?? 25)));
        $existingPrice = (float) ($existing['price'] ?? ($existing['pricing'] ?? 0));
        $price = max(0, (float) ($input['price'] ?? $existingPrice));
        $unitCost = max(0, (float) ($input['unit_cost'] ?? ($existing['unit_cost'] ?? $price)));
        $sellingUnit = self::resolveSelectableValue($input, 'selling_unit_existing', 'selling_unit_new', trim((string) ($input['selling_unit'] ?? ($existing['selling_unit'] ?? ''))));
        $unitQuantity = max(1, (int) ($input['unit_quantity'] ?? ($existing['unit_quantity'] ?? 1)));
        $batchNumber = trim((string) ($input['batch_number'] ?? ($existing['batch_number'] ?? '')));

        $sets = [];
        $types = '';
        $params = [];
        $setStr = function (string $col, string $val) use (&$sets, &$types, &$params) {
            $sets[] = "$col = ?";
            $types .= 's';
            $params[] = $val;
        };
        $setInt = function (string $col, int $val) use (&$sets, &$types, &$params) {
            $sets[] = "$col = ?";
            $types .= 'i';
            $params[] = $val;
        };
        $setNum = function (string $col, float $val) use (&$sets, &$types, &$params) {
            $sets[] = "$col = ?";
            $types .= 'd';
            $params[] = $val;
        };

        if (self::hasMedicineColumn('name')) {
            $setStr('name', $name);
        }
        if (self::hasMedicineColumn('med_name')) {
            $setStr('med_name', $medName);
        }
        if (self::hasMedicineColumn('generic_name')) {
            $setStr('generic_name', $generic);
        }
        if (self::hasMedicineColumn('category')) {
            $setStr('category', $category);
        }
        if (self::hasMedicineColumn('category_id')) {
            $setInt('category_id', max(0, $categoryId));
        }
        if (self::hasMedicineColumn('description')) {
            $setStr('description', $description);
        }
        if (self::hasMedicineColumn('dosage_form')) {
            $setStr('dosage_form', $dosageForm);
        }
        if (self::hasMedicineColumn('strength')) {
            $setStr('strength', $strength);
        }
        if (self::hasMedicineColumn('quantity_in_stock')) {
            $setInt('quantity_in_stock', $qty);
        }
        if (self::hasMedicineColumn('low_stock_threshold')) {
            $setInt('low_stock_threshold', $threshold);
        }
        if (self::hasMedicineColumn('reorder_quantity')) {
            $setInt('reorder_quantity', $reorderQty);
        }
        $priceCol = self::medicinePriceColumn();
        if ($priceCol !== '') {
            $setNum($priceCol, $price);
        }
        if (self::hasMedicineColumn('unit_cost')) {
            $setNum('unit_cost', $unitCost);
        }
        $imageColumn = self::getMedicineImageColumn();
        if ($imageColumn !== '' && $imagePath !== '') {
            $setStr($imageColumn, $imagePath);
        }
        if (self::hasMedicineColumn('manufacturer')) {
            $setStr('manufacturer', $manufacturer);
        }
        if (self::hasMedicineColumn('supplier_id')) {
            $setInt('supplier_id', $supplierId);
        }
        if (self::hasMedicineColumn('batch_number')) {
            $setStr('batch_number', $batchNumber);
        }
        if (self::hasMedicineColumn('expiry_date')) {
            if ($expiryDate !== '') {
                $sets[] = "expiry_date = ?";
                $types .= 's';
                $params[] = $expiryDate;
            } else {
                $sets[] = 'expiry_date = NULL';
            }
        }
        if (self::hasMedicineColumn('selling_unit')) {
            $setStr('selling_unit', $sellingUnit);
        }
        if (self::hasMedicineColumn('unit_quantity')) {
            $setInt('unit_quantity', $unitQuantity);
        }
        if (self::hasMedicineColumn('last_restocked_at') && $qty > (int) ($existing['quantity_in_stock'] ?? 0)) {
            $sets[] = 'last_restocked_at = NOW()';
        }
        if (self::hasMedicineColumn('updated_at')) {
            $sets[] = 'updated_at = NOW()';
        }
        if (empty($sets)) {
            return false;
        }

        $where = "id = ?";
        $types .= 'i';
        $params[] = (int) $id;
        if (self::hasMedicineColumn('pharmacy_id')) {
            $where .= " AND pharmacy_id = ?";
            $types .= 'i';
            $params[] = $pharmacyId;
        }
        $ok = Database::execute("UPDATE medicines SET " . implode(', ', $sets) . " WHERE $where", $types, $params);
        if (!$ok) {
            return false;
        }

        self::upsertLookupValue('medicine_brands', $name);
        self::upsertLookupValue('dosage_forms', $dosageForm);
        self::upsertLookupValue('selling_units', $sellingUnit);
        self::upsertLookupValue('medicine_manufacturers', $manufacturer);
        return true;
    }

    public static function adjustStock(int $medicineId, string $mode, int $quantity, string $note = '', string $referenceNo = ''): bool
    {
        $medicine = self::getById($medicineId);
        if (!$medicine || $quantity < 0) {
            return false;
        }

        $before = (int) ($medicine['quantity_in_stock'] ?? 0);
        $after = $before;
        $movementType = 'adjustment';
        $change = $quantity;

        if ($mode === 'add') {
            $after = $before + $quantity;
            $movementType = 'restock';
            $change = $quantity;
        } elseif ($mode === 'remove') {
            $after = max(0, $before - $quantity);
            $movementType = 'dispense';
            $change = -1 * min($before, $quantity);
        } elseif ($mode === 'set') {
            $after = max(0, $quantity);
            $movementType = 'set';
            $change = $after - $before;
        } else {
            return false;
        }

        if ($after === $before) {
            return true;
        }

        $where = "id = ?";
        $types = 'i';
        $params = [$medicineId];
        if (self::hasMedicineColumn('pharmacy_id')) {
            $where .= " AND pharmacy_id = ?";
            $types .= 'i';
            $params[] = self::currentPharmacyId();
        }

        $sql = "UPDATE medicines SET quantity_in_stock = ?, updated_at = NOW()";
        $updateTypes = 'i' . $types;
        $updateParams = array_merge([$after], $params);
        if (self::hasMedicineColumn('last_restocked_at') && $after > $before) {
            $sql .= ", last_restocked_at = NOW()";
        }
        $sql .= " WHERE $where";
        $ok = Database::execute($sql, $updateTypes, $updateParams);
        if (!$ok) {
            return false;
        }

        return self::logStockMovement(
            $medicineId,
            (int) ($medicine['supplier_id'] ?? 0),
            $movementType,
            $change,
            $before,
            $after,
            $note,
            $referenceNo
        );
    }

    private static function logStockMovement(
        int $medicineId,
        int $supplierId,
        string $movementType,
        int $quantityChange,
        int $before,
        int $after,
        string $note = '',
        string $referenceNo = ''
    ): bool {
        return Database::execute(
            "INSERT INTO medicine_stock_movements
             (medicine_id, supplier_id, pharmacy_id, movement_type, quantity_change, quantity_before, quantity_after, note, reference_no, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            'iiisiiissi',
            [
                $medicineId,
                $supplierId,
                self::currentPharmacyId(),
                $movementType,
                $quantityChange,
                $before,
                $after,
                trim($note),
                trim($referenceNo),
                self::currentUserId(),
            ]
        );
    }

    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM medicines WHERE id = ?";
        $types = 'i';
        $params = [$id];
        if (self::hasMedicineColumn('pharmacy_id')) {
            $sql .= " AND pharmacy_id = ?";
            $types .= 'i';
            $params[] = self::currentPharmacyId();
        }
        return Database::execute($sql, $types, $params);
    }
}
