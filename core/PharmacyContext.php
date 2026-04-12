<?php

/**
 * PharmacyContext
 * Central helper for multi-pharmacy schema, session context, and data isolation checks.
 */
class PharmacyContext
{
    public static function boot(): void
    {
        self::ensureSession();
        self::ensureSchema();
    }

    public static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function tableExists(string $table): bool
    {
        Database::setUpConnection();
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    public static function columnExists(string $table, string $column): bool
    {
        Database::setUpConnection();
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    public static function pharmaciesEnabled(): bool
    {
        return self::tableExists('pharmacies');
    }

    public static function selectedPharmacyId(): int
    {
        self::ensureSession();
        return (int)($_SESSION['selected_pharmacy_id'] ?? 0);
    }

    public static function setSelectedPharmacyId(int $pharmacyId): void
    {
        self::ensureSession();
        $_SESSION['selected_pharmacy_id'] = max(0, $pharmacyId);
    }

    public static function clearSelectedPharmacy(): void
    {
        self::ensureSession();
        unset($_SESSION['selected_pharmacy_id']);
    }

    public static function getPharmacies(): array
    {
        if (!self::pharmaciesEnabled()) {
            return [];
        }

        $rs = Database::search("SELECT id, name, address_line1, city, latitude, longitude, is_demo, status FROM pharmacies WHERE status = 'active' ORDER BY name ASC");
        $rows = [];
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc()) {
                $rows[] = $r;
            }
        }
        return $rows;
    }

    public static function pharmacyById(int $id): ?array
    {
        if ($id <= 0 || !self::pharmaciesEnabled()) {
            return null;
        }

        $id = (int)$id;
        $rs = Database::search("SELECT * FROM pharmacies WHERE id = $id LIMIT 1");
        if (!($rs instanceof mysqli_result)) {
            return null;
        }
        $row = $rs->fetch_assoc();
        return $row ?: null;
    }

    public static function resolvePharmacistPharmacyId(int $pharmacistId): int
    {
        if ($pharmacistId <= 0 || !self::tableExists('pharmacy_users')) {
            return 0;
        }

        $safeId = (int)$pharmacistId;

        $whereUser = [];
        if (self::columnExists('pharmacy_users', 'pharmacist_id')) {
            $whereUser[] = "pharmacist_id = $safeId";
        }
        if (self::columnExists('pharmacy_users', 'user_id')) {
            $whereUser[] = "user_id = $safeId";
        }

        if (empty($whereUser)) {
            return 0;
        }

        $statusFilter = self::columnExists('pharmacy_users', 'status') ? "AND status = 'active'" : '';
        $rs = Database::search(
            "SELECT pharmacy_id
             FROM pharmacy_users
             WHERE (" . implode(' OR ', $whereUser) . ")
             $statusFilter
             ORDER BY is_primary DESC, id ASC
             LIMIT 1"
        );

        if (!($rs instanceof mysqli_result)) {
            return self::assignDefaultPharmacyToPharmacist($safeId);
        }

        $row = $rs->fetch_assoc();
        $resolved = (int)($row['pharmacy_id'] ?? 0);
        if ($resolved > 0) {
            return $resolved;
        }
        return self::assignDefaultPharmacyToPharmacist($safeId);
    }

    private static function assignDefaultPharmacyToPharmacist(int $pharmacistId): int
    {
        if ($pharmacistId <= 0 || !self::tableExists('pharmacy_users') || !self::tableExists('pharmacies')) {
            return 0;
        }

        $rs = Database::search("SELECT id FROM pharmacies WHERE status = 'active' ORDER BY id ASC LIMIT 1");
        if (!($rs instanceof mysqli_result)) {
            return 0;
        }
        $row = $rs->fetch_assoc();
        $pharmacyId = (int)($row['id'] ?? 0);
        if ($pharmacyId <= 0) {
            return 0;
        }

        $exists = Database::search("SELECT id FROM pharmacy_users WHERE pharmacy_id = $pharmacyId AND pharmacist_id = $pharmacistId LIMIT 1");
        if (!($exists instanceof mysqli_result) || $exists->num_rows === 0) {
            Database::iud("INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status, created_at)
                           VALUES ($pharmacyId, $pharmacistId, $pharmacistId, 'pharmacist', 1, 'active', NOW())");
        } else {
            Database::iud("UPDATE pharmacy_users SET status='active', is_primary=1 WHERE pharmacy_id = $pharmacyId AND pharmacist_id = $pharmacistId");
        }

        return $pharmacyId;
    }

    public static function patientHasSelection(string $patientNic): bool
    {
        if ($patientNic === '') {
            self::clearSelectedPharmacy();
            return false;
        }

        if (!self::tableExists('patient_pharmacy_selection')) {
            return self::selectedPharmacyId() > 0;
        }

        $safeNic = Database::escape($patientNic);
        $rs = Database::search("SELECT pharmacy_id FROM patient_pharmacy_selection WHERE patient_nic = '$safeNic' AND is_active = 1 ORDER BY id DESC LIMIT 1");
        if ($rs instanceof mysqli_result && $rs->num_rows > 0) {
            $row = $rs->fetch_assoc();
            $id = (int)($row['pharmacy_id'] ?? 0);
            if ($id > 0) {
                self::setSelectedPharmacyId($id);
                return true;
            }
        }

        self::clearSelectedPharmacy();
        return false;
    }

    public static function assignPatientSelection(string $patientNic, int $pharmacyId): bool
    {
        if ($patientNic === '' || $pharmacyId <= 0 || !self::pharmaciesEnabled()) {
            return false;
        }

        $safeNic = Database::escape($patientNic);
        $pharmacyId = (int)$pharmacyId;

        if (self::tableExists('patient_pharmacy_selection')) {
            Database::iud("UPDATE patient_pharmacy_selection SET is_active = 0 WHERE patient_nic = '$safeNic'");
            $ok = Database::iud("INSERT INTO patient_pharmacy_selection (patient_nic, pharmacy_id, selected_at, is_active) VALUES ('$safeNic', $pharmacyId, NOW(), 1)");
            if (!$ok) {
                return false;
            }
        }

        self::setSelectedPharmacyId($pharmacyId);
        return true;
    }

    public static function sqlFilter(string $tableOrAlias, int $pharmacyId): string
    {
        if ($pharmacyId <= 0) {
            return '1=1';
        }
        return "$tableOrAlias.pharmacy_id = " . (int)$pharmacyId;
    }

    public static function tableHasPharmacyId(string $table): bool
    {
        return self::tableExists($table) && self::columnExists($table, 'pharmacy_id');
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        Database::setUpConnection();

        Database::iud("CREATE TABLE IF NOT EXISTS pharmacies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            address_line1 VARCHAR(255) NOT NULL,
            address_line2 VARCHAR(255) NULL,
            city VARCHAR(120) NOT NULL,
            district VARCHAR(120) NULL,
            postal_code VARCHAR(20) NULL,
            latitude DECIMAL(10,8) NOT NULL,
            longitude DECIMAL(11,8) NOT NULL,
            phone VARCHAR(40) NULL,
            email VARCHAR(150) NULL,
            is_demo TINYINT(1) NOT NULL DEFAULT 0,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if (self::tableExists('pharmacies') && !self::columnExists('pharmacies', 'is_demo')) {
            Database::iud("ALTER TABLE pharmacies ADD COLUMN is_demo TINYINT(1) NOT NULL DEFAULT 0");
        }

        Database::iud("CREATE TABLE IF NOT EXISTS pharmacy_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pharmacy_id INT NOT NULL,
            pharmacist_id INT NULL,
            user_id INT NULL,
            role ENUM('pharmacist','pharmacy_admin') NOT NULL DEFAULT 'pharmacist',
            is_primary TINYINT(1) NOT NULL DEFAULT 1,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pharmacy (pharmacy_id),
            INDEX idx_pharmacist (pharmacist_id),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        Database::iud("CREATE TABLE IF NOT EXISTS pharmacist_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(40) NULL,
            license_no VARCHAR(64) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            requested_pharmacy_id INT NULL,
            status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            note VARCHAR(255) NULL,
            reviewed_by INT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            reviewed_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_status (status),
            INDEX idx_email (email),
            INDEX idx_license (license_no)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        Database::iud("CREATE TABLE IF NOT EXISTS patient_pharmacy_selection (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_nic VARCHAR(32) NOT NULL,
            pharmacy_id INT NOT NULL,
            selected_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            INDEX idx_patient (patient_nic),
            INDEX idx_pharmacy (pharmacy_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        Database::iud("CREATE TABLE IF NOT EXISTS medication_reminder_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_nic VARCHAR(50) NOT NULL,
            source_type VARCHAR(20) NOT NULL,
            source_schedule_id INT NOT NULL,
            dose_date DATE NOT NULL,
            time_slot VARCHAR(20) NOT NULL,
            scheduled_at DATETIME NOT NULL,
            message TEXT NOT NULL,
            status ENUM('PENDING','TAKEN','MISSED') NOT NULL DEFAULT 'PENDING',
            delivered_at DATETIME NULL,
            delivered_notification_id INT NULL,
            pharmacy_id INT NULL,
            taken_at DATETIME NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_patient_due (patient_nic, scheduled_at, status),
            INDEX idx_source (source_type, source_schedule_id, dose_date),
            INDEX idx_notification (delivered_notification_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pharmacyDataTables = [
            'medicines',
            'prescriptions',
            'medication_schedules',
            'medication_schedule',
            'schedule_master',
            'medication_log',
            'chat_messages',
            'notifications'
        ];

        foreach ($pharmacyDataTables as $table) {
            if (self::tableExists($table) && !self::columnExists($table, 'pharmacy_id')) {
                Database::iud("ALTER TABLE `$table` ADD COLUMN pharmacy_id INT NULL");
                Database::iud("UPDATE `$table` SET pharmacy_id = 1 WHERE pharmacy_id IS NULL");
            }
        }

        $seed = Database::search("SELECT id FROM pharmacies ORDER BY id ASC LIMIT 1");
        if (!($seed instanceof mysqli_result) || $seed->num_rows === 0) {
            Database::iud("INSERT INTO pharmacies (name, address_line1, city, district, latitude, longitude, status)
                           VALUES ('Medora Main Pharmacy', 'Default Address', 'Colombo', 'Colombo', 6.927079, 79.861244, 'active')");
        }

        self::seedDemoPharmacies();

        $firstPharmacyRs = Database::search("SELECT id FROM pharmacies ORDER BY id ASC LIMIT 1");
        $firstPharmacyId = 1;
        if ($firstPharmacyRs instanceof mysqli_result) {
            $f = $firstPharmacyRs->fetch_assoc();
            $firstPharmacyId = (int)($f['id'] ?? 1);
        }

        foreach ($pharmacyDataTables as $table) {
            if (self::tableHasPharmacyId($table)) {
                Database::iud("UPDATE `$table` SET pharmacy_id = $firstPharmacyId WHERE pharmacy_id IS NULL OR pharmacy_id = 0");
            }
        }

        if (self::tableExists('pharmacists')) {
            $pr = Database::search("SELECT id FROM pharmacists ORDER BY id ASC LIMIT 1");
            if ($pr instanceof mysqli_result && $pr->num_rows > 0) {
                $p = $pr->fetch_assoc();
                $pid = (int)($p['id'] ?? 0);
                if ($pid > 0) {
                    $exists = Database::search("SELECT id FROM pharmacy_users WHERE pharmacy_id = $firstPharmacyId AND pharmacist_id = $pid LIMIT 1");
                    if (!($exists instanceof mysqli_result) || $exists->num_rows === 0) {
                        Database::iud("INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status) VALUES ($firstPharmacyId, $pid, $pid, 'pharmacist', 1, 'active')");
                    }
                }
            }
        }
    }

    private static function seedDemoPharmacies(): void
    {
        if (!self::tableExists('pharmacies')) {
            return;
        }

        $demoCountRs = Database::search("SELECT COUNT(*) AS cnt FROM pharmacies WHERE is_demo = 1");
        $demoCount = 0;
        if ($demoCountRs instanceof mysqli_result) {
            $row = $demoCountRs->fetch_assoc();
            $demoCount = (int)($row['cnt'] ?? 0);
        }
        if ($demoCount > 0) {
            return;
        }

        $demoRows = [
            ['Medora City Care - Colombo Fort', 'No 14, York Street', 'Colombo', 'Colombo', 6.93520000, 79.84280000],
            ['Medora WellLife - Bambalapitiya', 'Galle Road, Bambalapitiya', 'Colombo', 'Colombo', 6.89160000, 79.85600000],
            ['Medora Community - Kandy Central', 'Dalada Veediya', 'Kandy', 'Kandy', 7.29360000, 80.64130000],
            ['Medora HealthPoint - Galle Town', 'Wakwella Road', 'Galle', 'Galle', 6.05350000, 80.22100000],
            ['Medora FamilyCare - Kurunegala', 'Colombo Road', 'Kurunegala', 'Kurunegala', 7.48630000, 80.36230000],
            ['Medora GreenCross - Jaffna', 'Hospital Road', 'Jaffna', 'Jaffna', 9.66150000, 80.02550000],
        ];

        foreach ($demoRows as $r) {
            [$name, $address, $city, $district, $lat, $lng] = $r;
            $safeName = Database::escape($name);
            $safeAddress = Database::escape($address);
            $safeCity = Database::escape($city);
            $safeDistrict = Database::escape($district);

            $exists = Database::search("SELECT id FROM pharmacies WHERE name = '$safeName' LIMIT 1");
            if ($exists instanceof mysqli_result && $exists->num_rows > 0) {
                continue;
            }

            Database::iud("INSERT INTO pharmacies
                (name, address_line1, city, district, latitude, longitude, is_demo, status, created_at, updated_at)
                VALUES
                ('$safeName', '$safeAddress', '$safeCity', '$safeDistrict', $lat, $lng, 1, 'active', NOW(), NOW())");
        }
    }
}
