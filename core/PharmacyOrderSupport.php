<?php

class PharmacyOrderSupport
{
    private const ORDER_TABLE = 'pharmacy_orders';
    private const ITEM_TABLE = 'pharmacy_order_items';

    private static bool $schemaReady = false;
    private static array $columnCache = [];
    private static array $tableCache = [];

    private static function logOrderError(string $context): void
    {
        $err = trim((string) (Database::$connection->error ?? ''));
        if ($err === '') {
            return;
        }

        $dir = ROOT . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $line = '[' . date('Y-m-d H:i:s') . "] [ORDER] $context - $err" . PHP_EOL;
        @file_put_contents($dir . '/pharmacy-order-error.log', $line, FILE_APPEND);
    }

    public static function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }

        self::ensurePrescriptionColumns();
        self::ensureOrderTables();
        self::$schemaReady = true;
    }

    private static function hasTable(string $table): bool
    {
        $key = strtolower($table);
        if (array_key_exists($key, self::$tableCache)) {
            return self::$tableCache[$key];
        }

        $row = Database::fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?",
            's',
            [$table]
        );
        self::$tableCache[$key] = (int) ($row['cnt'] ?? 0) > 0;
        return self::$tableCache[$key];
    }

    private static function hasColumn(string $table, string $column): bool
    {
        $key = strtolower($table . '.' . $column);
        if (array_key_exists($key, self::$columnCache)) {
            return self::$columnCache[$key];
        }

        $row = Database::fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?",
            'ss',
            [$table, $column]
        );
        self::$columnCache[$key] = (int) ($row['cnt'] ?? 0) > 0;
        return self::$columnCache[$key];
    }

    private static function ensurePrescriptionColumns(): void
    {
        if (!self::hasColumn('prescriptions', 'wants_medicine_order')) {
            Database::execute("ALTER TABLE prescriptions ADD COLUMN wants_medicine_order TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
            self::$columnCache['prescriptions.wants_medicine_order'] = true;
        }

        if (!self::hasColumn('prescriptions', 'wants_schedule')) {
            Database::execute("ALTER TABLE prescriptions ADD COLUMN wants_schedule TINYINT(1) NOT NULL DEFAULT 1 AFTER wants_medicine_order");
            self::$columnCache['prescriptions.wants_schedule'] = true;
        }
    }

    private static function ensureOrderTables(): void
    {
        $createdOrders = Database::execute("
            CREATE TABLE IF NOT EXISTS `" . self::ORDER_TABLE . "` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                pharmacy_id INT DEFAULT NULL,
                patient_nic VARCHAR(20) NOT NULL,
                prescription_id INT DEFAULT NULL,
                source VARCHAR(30) NOT NULL DEFAULT 'PRESCRIPTION',
                order_title VARCHAR(255) NOT NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
                wants_schedule TINYINT(1) NOT NULL DEFAULT 0,
                delivery_method VARCHAR(20) NOT NULL DEFAULT 'PICKUP',
                billing_name VARCHAR(150) NOT NULL DEFAULT '',
                billing_phone VARCHAR(50) NOT NULL DEFAULT '',
                billing_email VARCHAR(150) NOT NULL DEFAULT '',
                billing_address VARCHAR(255) NOT NULL DEFAULT '',
                billing_city VARCHAR(120) NOT NULL DEFAULT '',
                billing_notes TEXT DEFAULT NULL,
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                fulfillment_notes TEXT DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_pharmacy_orders_patient (patient_nic, created_at),
                INDEX idx_pharmacy_orders_pharmacy (pharmacy_id, status, created_at),
                INDEX idx_pharmacy_orders_prescription (prescription_id),
                CONSTRAINT fk_pharmacy_orders_patient FOREIGN KEY (patient_nic) REFERENCES patient (nic)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_pharmacy_orders_prescription FOREIGN KEY (prescription_id) REFERENCES prescriptions (id)
                    ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT fk_pharmacy_orders_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies (id)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        if (!$createdOrders || !self::hasTable(self::ORDER_TABLE)) {
            self::logOrderError('ensureOrderTables create pharmacy_orders with foreign keys failed');
            Database::execute("
                CREATE TABLE IF NOT EXISTS `" . self::ORDER_TABLE . "` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    pharmacy_id INT DEFAULT NULL,
                    patient_nic VARCHAR(20) NOT NULL,
                    prescription_id INT DEFAULT NULL,
                    source VARCHAR(30) NOT NULL DEFAULT 'PRESCRIPTION',
                    order_title VARCHAR(255) NOT NULL,
                    status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
                    wants_schedule TINYINT(1) NOT NULL DEFAULT 0,
                    delivery_method VARCHAR(20) NOT NULL DEFAULT 'PICKUP',
                    billing_name VARCHAR(150) NOT NULL DEFAULT '',
                    billing_phone VARCHAR(50) NOT NULL DEFAULT '',
                    billing_email VARCHAR(150) NOT NULL DEFAULT '',
                    billing_address VARCHAR(255) NOT NULL DEFAULT '',
                    billing_city VARCHAR(120) NOT NULL DEFAULT '',
                    billing_notes TEXT DEFAULT NULL,
                    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    fulfillment_notes TEXT DEFAULT NULL,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_pharmacy_orders_patient (patient_nic, created_at),
                    INDEX idx_pharmacy_orders_pharmacy (pharmacy_id, status, created_at),
                    INDEX idx_pharmacy_orders_prescription (prescription_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            if (!self::hasTable(self::ORDER_TABLE)) {
                self::logOrderError('ensureOrderTables create pharmacy_orders fallback failed');
                return;
            }
        }

        $createdItems = Database::execute("
            CREATE TABLE IF NOT EXISTS `" . self::ITEM_TABLE . "` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                medicine_id INT DEFAULT NULL,
                medicine_name VARCHAR(255) NOT NULL DEFAULT '',
                quantity INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order_items_order (order_id),
                INDEX idx_order_items_medicine (medicine_id),
                CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES `" . self::ORDER_TABLE . "` (id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_order_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines (id)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        if (!$createdItems || !self::hasTable(self::ITEM_TABLE)) {
            self::logOrderError('ensureOrderTables create pharmacy_order_items with foreign keys failed');
            Database::execute("
                CREATE TABLE IF NOT EXISTS `" . self::ITEM_TABLE . "` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    medicine_id INT DEFAULT NULL,
                    medicine_name VARCHAR(255) NOT NULL DEFAULT '',
                    quantity INT NOT NULL DEFAULT 1,
                    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_order_items_order (order_id),
                    INDEX idx_order_items_medicine (medicine_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            if (!self::hasTable(self::ITEM_TABLE)) {
                self::logOrderError('ensureOrderTables create pharmacy_order_items fallback failed');
                return;
            }
        }

        self::$tableCache[strtolower(self::ORDER_TABLE)] = true;
        self::$tableCache[strtolower(self::ITEM_TABLE)] = true;
    }

    public static function normalizeDeliveryMethod(string $method): string
    {
        $method = strtoupper(trim($method));
        return $method === 'DELIVERY' ? 'DELIVERY' : 'PICKUP';
    }

    public static function sanitizeBillingData(array $input): array
    {
        return [
            'billing_name' => trim((string) ($input['billing_name'] ?? '')),
            'billing_phone' => trim((string) ($input['billing_phone'] ?? '')),
            'billing_email' => trim((string) ($input['billing_email'] ?? '')),
            'billing_address' => trim((string) ($input['billing_address'] ?? '')),
            'billing_city' => trim((string) ($input['billing_city'] ?? '')),
            'billing_notes' => trim((string) ($input['billing_notes'] ?? '')),
            'delivery_method' => self::normalizeDeliveryMethod((string) ($input['delivery_method'] ?? 'PICKUP')),
        ];
    }

    public static function validateBillingData(array $billing, bool $required): ?string
    {
        if (!$required) {
            return null;
        }

        if (trim((string) ($billing['billing_name'] ?? '')) === '') {
            return 'Billing name is required.';
        }
        if (trim((string) ($billing['billing_phone'] ?? '')) === '') {
            return 'Billing phone number is required.';
        }
        if (trim((string) ($billing['billing_email'] ?? '')) === '') {
            return 'Billing email is required.';
        }
        if (($billing['delivery_method'] ?? 'PICKUP') === 'DELIVERY' && trim((string) ($billing['billing_address'] ?? '')) === '') {
            return 'Delivery address is required when delivery is selected.';
        }

        return null;
    }

    public static function createPrescriptionOrder(array $prescription, array $billing): bool
    {
        self::ensureSchema();

        $prescriptionId = (int) ($prescription['id'] ?? 0);
        $patientNic = trim((string) ($prescription['patient_nic'] ?? ''));
        $pharmacyId = (int) ($prescription['pharmacy_id'] ?? 0);

        if ($pharmacyId <= 0 && $prescriptionId > 0) {
            $prescriptionRow = Database::fetchOne(
                "SELECT pharmacy_id FROM prescriptions WHERE id = ? LIMIT 1",
                'i',
                [$prescriptionId]
            );
            $pharmacyId = (int) ($prescriptionRow['pharmacy_id'] ?? 0);
        }

        if ($prescriptionId <= 0 || $patientNic === '') {
            return false;
        }

        $existing = Database::fetchOne(
            "SELECT id FROM `" . self::ORDER_TABLE . "` WHERE prescription_id = ? LIMIT 1",
            'i',
            [$prescriptionId]
        );
        if ($existing) {
            return true;
        }

        $status = strtoupper(trim((string) ($prescription['status'] ?? 'PENDING'))) === 'APPROVED'
            ? 'PENDING_FULFILLMENT'
            : 'AWAITING_PRESCRIPTION_APPROVAL';

        if ($pharmacyId > 0) {
            $ok = Database::execute(
                "INSERT INTO `" . self::ORDER_TABLE . "`
                    (pharmacy_id, patient_nic, prescription_id, source, order_title, status, wants_schedule, delivery_method,
                     billing_name, billing_phone, billing_email, billing_address, billing_city, billing_notes,
                     subtotal, delivery_fee, total_amount, created_at, updated_at)
                 VALUES (?, ?, ?, 'PRESCRIPTION', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, 0.00, 0.00, NOW(), NOW())",
                'isississsssss',
                [
                    $pharmacyId,
                    $patientNic,
                    $prescriptionId,
                    (string) ($prescription['file_name'] ?? ('Prescription #' . $prescriptionId)),
                    $status,
                    !empty($prescription['wants_schedule']) ? 1 : 0,
                    (string) ($billing['delivery_method'] ?? 'PICKUP'),
                    (string) ($billing['billing_name'] ?? ''),
                    (string) ($billing['billing_phone'] ?? ''),
                    (string) ($billing['billing_email'] ?? ''),
                    (string) ($billing['billing_address'] ?? ''),
                    (string) ($billing['billing_city'] ?? ''),
                    (string) ($billing['billing_notes'] ?? ''),
                ]
            );
            if (!$ok) {
                self::logOrderError('createPrescriptionOrder with pharmacy failed');
            }
            return $ok;
        }

        $ok = Database::execute(
            "INSERT INTO `" . self::ORDER_TABLE . "`
                (pharmacy_id, patient_nic, prescription_id, source, order_title, status, wants_schedule, delivery_method,
                 billing_name, billing_phone, billing_email, billing_address, billing_city, billing_notes,
                 subtotal, delivery_fee, total_amount, created_at, updated_at)
             VALUES (NULL, ?, ?, 'PRESCRIPTION', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, 0.00, 0.00, NOW(), NOW())",
            'sississsssss',
            [
                $patientNic,
                $prescriptionId,
                (string) ($prescription['file_name'] ?? ('Prescription #' . $prescriptionId)),
                $status,
                !empty($prescription['wants_schedule']) ? 1 : 0,
                (string) ($billing['delivery_method'] ?? 'PICKUP'),
                (string) ($billing['billing_name'] ?? ''),
                (string) ($billing['billing_phone'] ?? ''),
                (string) ($billing['billing_email'] ?? ''),
                (string) ($billing['billing_address'] ?? ''),
                (string) ($billing['billing_city'] ?? ''),
                (string) ($billing['billing_notes'] ?? ''),
            ]
        );
        if (!$ok) {
            self::logOrderError('createPrescriptionOrder without pharmacy failed');
        }
        return $ok;
    }

    public static function ensurePrescriptionOrder(array $prescription, array $billing = []): ?array
    {
        self::ensureSchema();
        $prescriptionId = (int) ($prescription['id'] ?? 0);
        if ($prescriptionId <= 0) {
            return null;
        }

        $order = self::getPrescriptionOrder($prescriptionId);
        if ($order) {
            return $order;
        }

        $created = self::createPrescriptionOrder($prescription, $billing);
        if (!$created) {
            return null;
        }

        return self::getPrescriptionOrder($prescriptionId);
    }

    public static function syncPrescriptionOrderStatus(int $prescriptionId, string $prescriptionStatus): bool
    {
        self::ensureSchema();
        if ($prescriptionId <= 0) {
            return false;
        }

        $prescriptionStatus = strtoupper(trim($prescriptionStatus));
        $orderStatus = match ($prescriptionStatus) {
            'APPROVED' => 'PENDING_FULFILLMENT',
            'REJECTED' => 'CANCELLED',
            default => 'AWAITING_PRESCRIPTION_APPROVAL',
        };

        return Database::execute(
            "UPDATE `" . self::ORDER_TABLE . "` SET status = ?, updated_at = NOW() WHERE prescription_id = ?",
            'si',
            [$orderStatus, $prescriptionId]
        );
    }

    public static function createShopOrder(string $patientNic, int $pharmacyId, array $billing, array $items): bool
    {
        self::ensureSchema();
        $patientNic = trim($patientNic);
        if ($patientNic === '' || $pharmacyId <= 0 || empty($items)) {
            return false;
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) ($item['line_total'] ?? 0);
        }
        $deliveryFee = 0.0;
        $total = $subtotal + $deliveryFee;

        $ok = Database::execute(
            "INSERT INTO `" . self::ORDER_TABLE . "`
                (pharmacy_id, patient_nic, prescription_id, source, order_title, status, wants_schedule, delivery_method,
                 billing_name, billing_phone, billing_email, billing_address, billing_city, billing_notes,
                 subtotal, delivery_fee, total_amount, created_at, updated_at)
             VALUES (?, ?, NULL, 'ESHOP', ?, 'PENDING_FULFILLMENT', 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            'isssssssssddd',
            [
                $pharmacyId,
                $patientNic,
                'E-shop medicine order',
                (string) ($billing['delivery_method'] ?? 'PICKUP'),
                (string) ($billing['billing_name'] ?? ''),
                (string) ($billing['billing_phone'] ?? ''),
                (string) ($billing['billing_email'] ?? ''),
                (string) ($billing['billing_address'] ?? ''),
                (string) ($billing['billing_city'] ?? ''),
                (string) ($billing['billing_notes'] ?? ''),
                $subtotal,
                $deliveryFee,
                $total,
            ]
        );
        if (!$ok) {
            return false;
        }

        $orderId = (int) (Database::$connection->insert_id ?? 0);
        if ($orderId <= 0) {
            return false;
        }

        foreach ($items as $item) {
            $medicine = $item['medicine'] ?? [];
            $medicineId = (int) ($medicine['id'] ?? 0);
            $medicineName = trim((string) ($medicine['name'] ?? ($medicine['med_name'] ?? 'Medicine')));
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = (float) ($medicine['price'] ?? 0);
            $lineTotal = (float) ($item['line_total'] ?? ($qty * $unitPrice));

            Database::execute(
                $medicineId > 0
                ? "INSERT INTO `" . self::ITEM_TABLE . "` (order_id, medicine_id, medicine_name, quantity, unit_price, line_total, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, NOW())"
                : "INSERT INTO `" . self::ITEM_TABLE . "` (order_id, medicine_id, medicine_name, quantity, unit_price, line_total, created_at)
                       VALUES (?, NULL, ?, ?, ?, ?, NOW())",
                $medicineId > 0 ? 'iisidd' : 'isidd',
                $medicineId > 0
                ? [$orderId, $medicineId, $medicineName, $qty, $unitPrice, $lineTotal]
                : [$orderId, $medicineName, $qty, $unitPrice, $lineTotal]
            );
        }

        return true;
    }

    public static function getPatientOrders(string $patientNic): array
    {
        self::ensureSchema();
        $patientNic = trim($patientNic);
        if ($patientNic === '') {
            return [];
        }

        return Database::fetchAll(
            "SELECT id, source, order_title, status, delivery_method, wants_schedule, total_amount, created_at
             FROM `" . self::ORDER_TABLE . "`
             WHERE patient_nic = ?
             ORDER BY created_at DESC, id DESC",
            's',
            [$patientNic]
        );
    }

    public static function getPrescriptionOrder(int $prescriptionId): ?array
    {
        self::ensureSchema();
        if ($prescriptionId <= 0) {
            return null;
        }

        return Database::fetchOne(
            "SELECT *
             FROM `" . self::ORDER_TABLE . "`
             WHERE prescription_id = ?
             LIMIT 1",
            'i',
            [$prescriptionId]
        );
    }

    public static function getOrderItems(int $orderId): array
    {
        self::ensureSchema();
        if ($orderId <= 0) {
            return [];
        }

        return Database::fetchAll(
            "SELECT *
             FROM `" . self::ITEM_TABLE . "`
             WHERE order_id = ?
             ORDER BY id ASC",
            'i',
            [$orderId]
        );
    }

    public static function replaceOrderItems(int $orderId, int $pharmacyId, array $items, string $status = 'PREPARING'): bool
    {
        self::ensureSchema();
        if ($orderId <= 0) {
            return false;
        }

        $order = Database::fetchOne(
            "SELECT id, pharmacy_id
             FROM `" . self::ORDER_TABLE . "`
             WHERE id = ?
             LIMIT 1",
            'i',
            [$orderId]
        );
        if (!$order) {
            return false;
        }

        $existingPharmacyId = (int) ($order['pharmacy_id'] ?? 0);
        if ($existingPharmacyId > 0 && $pharmacyId > 0 && $existingPharmacyId !== $pharmacyId) {
            return false;
        }

        $resolvedPharmacyId = $pharmacyId > 0 ? $pharmacyId : $existingPharmacyId;
        if ($existingPharmacyId <= 0 && $resolvedPharmacyId > 0) {
            $updated = Database::execute(
                "UPDATE `" . self::ORDER_TABLE . "` SET pharmacy_id = ?, updated_at = NOW() WHERE id = ?",
                'ii',
                [$resolvedPharmacyId, $orderId]
            );
            if (!$updated) {
                self::logOrderError('replaceOrderItems pharmacy claim failed');
                return false;
            }
        }

        $deleted = Database::execute("DELETE FROM `" . self::ITEM_TABLE . "` WHERE order_id = ?", 'i', [$orderId]);
        if (!$deleted) {
            self::logOrderError('replaceOrderItems delete old items failed');
            return false;
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $medicineId = (int) ($item['medicine_id'] ?? 0);
            $medicineName = trim((string) ($item['medicine_name'] ?? 'Medicine'));
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = max(0, (float) ($item['unit_price'] ?? 0));
            $lineTotal = $quantity * $unitPrice;
            $subtotal += $lineTotal;

            if ($medicineId > 0) {
                $inserted = Database::execute(
                    "INSERT INTO `" . self::ITEM_TABLE . "` (order_id, medicine_id, medicine_name, quantity, unit_price, line_total, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    'iisidd',
                    [$orderId, $medicineId, $medicineName, $quantity, $unitPrice, $lineTotal]
                );
            } else {
                $inserted = Database::execute(
                    "INSERT INTO `" . self::ITEM_TABLE . "` (order_id, medicine_id, medicine_name, quantity, unit_price, line_total, created_at)
                     VALUES (?, NULL, ?, ?, ?, ?, NOW())",
                    'isidd',
                    [$orderId, $medicineName, $quantity, $unitPrice, $lineTotal]
                );
            }

            if (!$inserted) {
                self::logOrderError('replaceOrderItems insert item failed');
                return false;
            }
        }

        $updated = Database::execute(
            "UPDATE `" . self::ORDER_TABLE . "`
             SET subtotal = ?, total_amount = ?, status = ?, updated_at = NOW()
             WHERE id = ?",
            'ddsi',
            [$subtotal, $subtotal, $status, $orderId]
        );
        if (!$updated) {
            self::logOrderError('replaceOrderItems update totals failed');
        }
        return $updated;
    }

    public static function getPharmacyOrders(int $pharmacyId, int $limit = 50): array
    {
        self::ensureSchema();
        if ($pharmacyId <= 0) {
            return [];
        }

        return Database::fetchAll(
            "SELECT o.*, p.name AS patient_name, pr.file_name AS prescription_file,
                    (SELECT COUNT(*) FROM `" . self::ITEM_TABLE . "` oi WHERE oi.order_id = o.id) AS item_count
             FROM `" . self::ORDER_TABLE . "` o
                 LEFT JOIN patient p ON p.nic COLLATE utf8mb4_unicode_ci = o.patient_nic COLLATE utf8mb4_unicode_ci
             LEFT JOIN prescriptions pr ON pr.id = o.prescription_id
             WHERE (o.pharmacy_id = ?
                OR (o.pharmacy_id IS NULL AND pr.pharmacy_id = ?))
               AND o.status <> 'COMPLETED'
             ORDER BY o.created_at DESC, o.id DESC
             LIMIT " . max(1, $limit),
            'ii',
            [$pharmacyId, $pharmacyId]
        );
    }

    public static function getPharmacyCompletedOrders(int $pharmacyId, int $limit = 100): array
    {
        self::ensureSchema();
        if ($pharmacyId <= 0) {
            return [];
        }

        return Database::fetchAll(
            "SELECT o.*, p.name AS patient_name, pr.file_name AS prescription_file,
                    (SELECT COUNT(*) FROM `" . self::ITEM_TABLE . "` oi WHERE oi.order_id = o.id) AS item_count
             FROM `" . self::ORDER_TABLE . "` o
                 LEFT JOIN patient p ON p.nic COLLATE utf8mb4_unicode_ci = o.patient_nic COLLATE utf8mb4_unicode_ci
             LEFT JOIN prescriptions pr ON pr.id = o.prescription_id
             WHERE (o.pharmacy_id = ?
                OR (o.pharmacy_id IS NULL AND pr.pharmacy_id = ?))
               AND o.status = 'COMPLETED'
             ORDER BY o.updated_at DESC, o.id DESC
             LIMIT " . max(1, $limit),
            'ii',
            [$pharmacyId, $pharmacyId]
        );
    }

    public static function getRecentPharmacyOrders(int $pharmacyId, int $limit = 5): array
    {
        return self::getPharmacyOrders($pharmacyId, $limit);
    }

    public static function countActivePharmacyOrders(int $pharmacyId): int
    {
        self::ensureSchema();
        if ($pharmacyId <= 0) {
            return 0;
        }

        $row = Database::fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM `" . self::ORDER_TABLE . "` o
             LEFT JOIN prescriptions pr ON pr.id = o.prescription_id
             WHERE (o.pharmacy_id = ? OR (o.pharmacy_id IS NULL AND pr.pharmacy_id = ?))
               AND status NOT IN ('COMPLETED', 'CANCELLED')",
            'ii',
            [$pharmacyId, $pharmacyId]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public static function updateOrderStatus(int $orderId, int $pharmacyId, string $status, string $notes = ''): bool
    {
        self::ensureSchema();
        if ($orderId <= 0 || $pharmacyId <= 0) {
            return false;
        }

        $allowed = [
            'PREPARING',
            'READY_FOR_PICKUP',
            'COMPLETED',
        ];
        $status = strtoupper(trim($status));
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $updated = Database::execute(
            "UPDATE `" . self::ORDER_TABLE . "`
             SET status = ?, fulfillment_notes = ?, updated_at = NOW()
             WHERE id = ? AND pharmacy_id = ?",
            'ssii',
            [$status, trim($notes), $orderId, $pharmacyId]
        );

        if (!$updated) {
            return false;
        }

        if ($status === 'READY_FOR_PICKUP') {
            $order = Database::fetchOne(
                "SELECT patient_nic FROM `" . self::ORDER_TABLE . "` WHERE id = ? LIMIT 1",
                'i',
                [$orderId]
            );

            $patientNic = trim((string) ($order['patient_nic'] ?? ''));
            if ($patientNic !== '') {
                if (PharmacyContext::tableHasPharmacyId('notifications')) {
                    Database::execute(
                        "INSERT INTO notifications (patient_nic, message, type, is_read, created_at, pharmacy_id)
                         VALUES (?, ?, 'ORDER', 0, NOW(), ?)",
                        'ssi',
                        [$patientNic, 'Your medicine order is ready for pickup.', $pharmacyId]
                    );
                } else {
                    Database::execute(
                        "INSERT INTO notifications (patient_nic, message, type, is_read, created_at)
                         VALUES (?, ?, 'ORDER', 0, NOW())",
                        'ss',
                        [$patientNic, 'Your medicine order is ready for pickup.']
                    );
                }
            }
        }

        return true;
    }
}
