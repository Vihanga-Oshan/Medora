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
    }

    public static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
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
        return Database::fetchAll("SELECT id, name, address_line1, city, latitude, longitude, is_demo, status FROM pharmacies WHERE status = 'active' ORDER BY name ASC");
    }

    public static function pharmacyById(int $id): ?array
    {
        if ($id <= 0) {
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
        if ($patientNic === '' || $pharmacyId <= 0) {
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

    public static function tableExists(string $table): bool
    {
        $table = trim($table);
        if ($table === '') {
            return false;
        }

        $row = Database::fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?",
            's',
            [$table]
        );

        return (int) ($row['cnt'] ?? 0) > 0;
    }

}
