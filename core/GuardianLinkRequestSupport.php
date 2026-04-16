<?php

class GuardianLinkRequestSupport
{
    public const TABLE = 'guardian_link_requests';

    public static function normalizeNic(string $nic): string
    {
        $nic = strtoupper(trim($nic));
        return preg_replace('/[\s\-]+/', '', $nic) ?? $nic;
    }

    public static function ensureTable(): void
    {
        Database::execute("
            CREATE TABLE IF NOT EXISTS `" . self::TABLE . "` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                guardian_nic VARCHAR(20) NOT NULL,
                patient_nic VARCHAR(20) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
                guardian_seen TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                responded_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_guardian_status (guardian_nic, status, responded_at),
                INDEX idx_patient_status (patient_nic, status, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public static function latestPendingForPatient(string $patientNic): ?array
    {
        self::ensureTable();
        $patientNic = self::normalizeNic($patientNic);
        if ($patientNic === '') {
            return null;
        }

        return Database::fetchOne(
            "SELECT *
             FROM `" . self::TABLE . "`
             WHERE patient_nic = ?
               AND status = 'PENDING'
             ORDER BY id DESC
             LIMIT 1",
            's',
            [$patientNic]
        );
    }

    public static function latestPendingForPair(string $patientNic, string $guardianNic): ?array
    {
        self::ensureTable();
        $patientNic = self::normalizeNic($patientNic);
        $guardianNic = self::normalizeNic($guardianNic);
        if ($patientNic === '' || $guardianNic === '') {
            return null;
        }

        return Database::fetchOne(
            "SELECT *
             FROM `" . self::TABLE . "`
             WHERE patient_nic = ?
               AND guardian_nic = ?
               AND status = 'PENDING'
             ORDER BY id DESC
             LIMIT 1",
            'ss',
            [$patientNic, $guardianNic]
        );
    }

    public static function cancelPendingForPair(string $patientNic, string $guardianNic): bool
    {
        self::ensureTable();
        $patientNic = self::normalizeNic($patientNic);
        $guardianNic = self::normalizeNic($guardianNic);
        if ($patientNic === '' || $guardianNic === '') {
            return false;
        }

        return Database::execute(
            "UPDATE `" . self::TABLE . "`
             SET status = 'CANCELLED', guardian_seen = 1, updated_at = NOW()
             WHERE patient_nic = ?
               AND guardian_nic = ?
               AND status = 'PENDING'",
            'ss',
            [$patientNic, $guardianNic]
        );
    }

    public static function createPending(string $patientNic, string $guardianNic): bool
    {
        self::ensureTable();
        $patientNic = self::normalizeNic($patientNic);
        $guardianNic = self::normalizeNic($guardianNic);
        if ($patientNic === '' || $guardianNic === '') {
            return false;
        }

        self::cancelPendingForPair($patientNic, $guardianNic);

        return Database::execute(
            "INSERT INTO `" . self::TABLE . "` (guardian_nic, patient_nic, status, guardian_seen, created_at, updated_at)
             VALUES (?, ?, 'PENDING', 0, NOW(), NOW())",
            'ss',
            [$guardianNic, $patientNic]
        );
    }

    public static function updateStatus(int $requestId, string $status): bool
    {
        $status = strtoupper(trim($status));
        if ($requestId <= 0 || !in_array($status, ['ACCEPTED', 'DECLINED'], true)) {
            return false;
        }

        return Database::execute(
            "UPDATE `" . self::TABLE . "`
             SET status = ?, guardian_seen = 0, responded_at = NOW(), updated_at = NOW()
             WHERE id = ?",
            'si',
            [$status, $requestId]
        );
    }
}
