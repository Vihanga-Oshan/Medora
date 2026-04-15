<?php

/**
 * PharmacyContext
 * Central helper for multi-pharmacy schema, session context, and data isolation checks.
 */
class PharmacyContext
{
    private const PHARMACY_SCOPED_TABLES = [
        'medicines',
        'prescriptions',
        'medication_schedule',
        'schedule_master',
        'medication_log',
        'chat_messages',
        'notifications',
        'medication_reminder_events',
        'patient_pharmacy_selection',
        'pharmacy_users',
        'pharmacies',
    ];

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
        return true;
    }

    public static function pharmaciesEnabled(): bool
    {
        return true;
    }

    public static function selectedPharmacyId(): int
    {
        self::ensureSession();
        return (int) ($_SESSION['selected_pharmacy_id'] ?? 0);
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

        return Database::fetchOne("SELECT * FROM pharmacies WHERE id = ? LIMIT 1", 'i', [$id]);
    }

    public static function resolvePharmacistPharmacyId(int $pharmacistId): int
    {
        if ($pharmacistId <= 0) {
            return 0;
        }

        $row = Database::fetchOne(
            "SELECT pharmacy_id
             FROM pharmacy_users
             WHERE (pharmacist_id = ? OR user_id = ?)
             AND status = 'active'
             ORDER BY is_primary DESC, id ASC
             LIMIT 1",
            'ii',
            [$pharmacistId, $pharmacistId]
        );
        $resolved = (int) ($row['pharmacy_id'] ?? 0);
        if ($resolved > 0) {
            return $resolved;
        }
        return self::assignDefaultPharmacyToPharmacist($pharmacistId);
    }

    private static function assignDefaultPharmacyToPharmacist(int $pharmacistId): int
    {
        if ($pharmacistId <= 0) {
            return 0;
        }

        $row = Database::fetchOne("SELECT id FROM pharmacies WHERE status = 'active' ORDER BY id ASC LIMIT 1");
        if (!$row) {
            return 0;
        }
        $pharmacyId = (int) ($row['id'] ?? 0);
        if ($pharmacyId <= 0) {
            return 0;
        }

        $exists = Database::fetchOne("SELECT id FROM pharmacy_users WHERE pharmacy_id = ? AND pharmacist_id = ? LIMIT 1", 'ii', [$pharmacyId, $pharmacistId]);
        if (!$exists) {
            Database::execute(
                "INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status, created_at)
                 VALUES (?, ?, ?, 'pharmacist', 1, 'active', NOW())",
                'iii',
                [$pharmacyId, $pharmacistId, $pharmacistId]
            );
        } else {
            Database::execute(
                "UPDATE pharmacy_users SET status='active', is_primary=1 WHERE pharmacy_id = ? AND pharmacist_id = ?",
                'ii',
                [$pharmacyId, $pharmacistId]
            );
        }

        return $pharmacyId;
    }

    public static function patientHasSelection(string $patientNic): bool
    {
        if ($patientNic === '') {
            self::clearSelectedPharmacy();
            return false;
        }

        $row = Database::fetchOne(
            "SELECT pharmacy_id FROM patient_pharmacy_selection WHERE patient_nic = ? AND is_active = 1 ORDER BY id DESC LIMIT 1",
            's',
            [$patientNic]
        );
        if ($row) {
            $id = (int) ($row['pharmacy_id'] ?? 0);
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

        $pharmacyId = (int) $pharmacyId;

        Database::execute("UPDATE patient_pharmacy_selection SET is_active = 0 WHERE patient_nic = ?", 's', [$patientNic]);
        $ok = Database::execute(
            "INSERT INTO patient_pharmacy_selection (patient_nic, pharmacy_id, selected_at, is_active) VALUES (?, ?, NOW(), 1)",
            'si',
            [$patientNic, $pharmacyId]
        );
        if (!$ok) {
            return false;
        }

        self::setSelectedPharmacyId($pharmacyId);
        return true;
    }

    public static function sqlFilter(string $tableOrAlias, int $pharmacyId): string
    {
        if ($pharmacyId <= 0) {
            return '1=1';
        }
        return "$tableOrAlias.pharmacy_id = " . (int) $pharmacyId;
    }

    public static function tableHasPharmacyId(string $table): bool
    {
        return in_array($table, self::PHARMACY_SCOPED_TABLES, true);
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

        Database::iud("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_nic VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'APP',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            pharmacy_id INT NULL,
            INDEX idx_patient_created (patient_nic, created_at),
            INDEX idx_is_read (is_read),
            INDEX idx_pharmacy (pharmacy_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        Database::iud("CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_type VARCHAR(32) NOT NULL,
            sender_id VARCHAR(64) NOT NULL,
            receiver_id VARCHAR(64) NOT NULL,
            message_text TEXT NOT NULL,
            sent_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            typing VARCHAR(32) NULL,
            type VARCHAR(32) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            pharmacy_id INT NULL,
            INDEX idx_sender_receiver (sender_id, receiver_id),
            INDEX idx_receiver_sent (receiver_id, sent_at),
            INDEX idx_is_read (is_read),
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
            $firstPharmacyId = (int) ($f['id'] ?? 1);
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
                $pid = (int) ($p['id'] ?? 0);
                if ($pid > 0) {
                    $exists = Database::fetchOne("SELECT id FROM pharmacy_users WHERE pharmacy_id = ? AND pharmacist_id = ? LIMIT 1", 'ii', [$firstPharmacyId, $pid]);
                    if (!$exists) {
                        Database::execute(
                            "INSERT INTO pharmacy_users (pharmacy_id, pharmacist_id, user_id, role, is_primary, status) VALUES (?, ?, ?, 'pharmacist', 1, 'active')",
                            'iii',
                            [$firstPharmacyId, $pid, $pid]
                        );
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
            $demoCount = (int) ($row['cnt'] ?? 0);
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
            $exists = Database::fetchOne("SELECT id FROM pharmacies WHERE name = ? LIMIT 1", 's', [$name]);
            if ($exists) {
                continue;
            }

            Database::execute(
                "INSERT INTO pharmacies
                (name, address_line1, city, district, latitude, longitude, is_demo, status, created_at, updated_at)
                VALUES
                (?, ?, ?, ?, ?, ?, 1, 'active', NOW(), NOW())",
                'ssssdd',
                [$name, $address, $city, $district, $lat, $lng]
            );
        }
    }
}
