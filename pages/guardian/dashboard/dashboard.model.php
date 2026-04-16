<?php
/**
 * Guardian Dashboard Model
 */
require_once ROOT . '/core/GuardianLinkRequestSupport.php';

class DashboardModel
{
    private const PATIENT_TABLE = 'patient';

    private static function normalizeNic(string $nic): string
    {
        return GuardianLinkRequestSupport::normalizeNic($nic);
    }

    private static function guardianMatchExpr(string $patientAlias = 'p'): string
    {
        return $patientAlias . ".guardian_nic = ?";
    }

    public static function getPatientsByGuardian(string $guardianNic): array
    {
        $normalizedNic = self::normalizeNic($guardianNic);
        if ($normalizedNic === '') {
            return [];
        }

        return Database::fetchAll(
            "SELECT nic, name, gender, chronic_issues
             FROM `" . self::PATIENT_TABLE . "` p
             WHERE " . self::guardianMatchExpr('p') . "
             ORDER BY name ASC",
            's',
            [$normalizedNic]
        );
    }

    public static function getRecentAlertsByGuardian(string $guardianNic, int $limit = 5): array
    {
        $normalizedNic = self::normalizeNic($guardianNic);
        if ($normalizedNic === '') {
            return [];
        }
        GuardianLinkRequestSupport::ensureTable();

        $limit = max(1, (int)$limit);

        $doseAlerts = Database::fetchAll(
            "SELECT n.id,
                    n.message,
                    n.type,
                    n.created_at,
                    COALESCE(n.is_read, 0) AS is_read,
                    p.name AS patient_name
             FROM notifications n
             JOIN `" . self::PATIENT_TABLE . "` p ON n.patient_nic = p.nic
             WHERE " . self::guardianMatchExpr('p') . "
               AND (
                   UPPER(COALESCE(n.type, '')) = 'DOSE_MISSED'
                   OR UPPER(COALESCE(n.message, '')) LIKE '%MISSED%'
               )
             ORDER BY n.created_at DESC
             LIMIT " . $limit,
            's',
            [$normalizedNic]
        );

        $requestOutcomeAlerts = Database::fetchAll(
            "SELECT r.id,
                    CASE
                        WHEN UPPER(r.status) = 'ACCEPTED' THEN CONCAT(COALESCE(p.name, 'Patient'), ' accepted your guardian link request.')
                        WHEN UPPER(r.status) = 'DECLINED' THEN CONCAT(COALESCE(p.name, 'Patient'), ' declined your guardian link request.')
                        ELSE CONCAT(COALESCE(p.name, 'Patient'), ' updated guardian request status.')
                    END AS message,
                    'GUARDIAN_LINK' AS type,
                    COALESCE(r.responded_at, r.updated_at, r.created_at) AS created_at,
                    COALESCE(r.guardian_seen, 0) AS is_read,
                    COALESCE(p.name, 'Patient') AS patient_name
             FROM `" . GuardianLinkRequestSupport::TABLE . "` r
             LEFT JOIN `" . self::PATIENT_TABLE . "` p ON p.nic = r.patient_nic
             WHERE r.guardian_nic = ?
               AND r.status IN ('ACCEPTED', 'DECLINED')
             ORDER BY COALESCE(r.responded_at, r.updated_at, r.created_at) DESC
             LIMIT " . $limit,
            's',
            [$normalizedNic]
        );

        $all = array_merge($doseAlerts, $requestOutcomeAlerts);
        usort($all, static function (array $a, array $b): int {
            $ta = strtotime((string)($a['created_at'] ?? '1970-01-01 00:00:00')) ?: 0;
            $tb = strtotime((string)($b['created_at'] ?? '1970-01-01 00:00:00')) ?: 0;
            return $tb <=> $ta;
        });

        return array_slice($all, 0, $limit);
    }

    public static function getUnreadAlertsCount(string $guardianNic): int
    {
        $normalizedNic = self::normalizeNic($guardianNic);
        if ($normalizedNic === '') {
            return 0;
        }
        GuardianLinkRequestSupport::ensureTable();

        $doseRow = Database::fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM notifications n
             JOIN `" . self::PATIENT_TABLE . "` p ON n.patient_nic = p.nic
             WHERE " . self::guardianMatchExpr('p') . "
               AND COALESCE(n.is_read, 0) = 0
               AND (
                   UPPER(COALESCE(n.type, '')) = 'DOSE_MISSED'
                   OR UPPER(COALESCE(n.message, '')) LIKE '%MISSED%'
               )",
            's',
            [$normalizedNic]
        );

        $requestRow = Database::fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM `" . GuardianLinkRequestSupport::TABLE . "` r
             WHERE r.guardian_nic = ?
               AND r.status IN ('ACCEPTED', 'DECLINED')
               AND COALESCE(r.guardian_seen, 0) = 0",
            's',
            [$normalizedNic]
        );

        return (int)($doseRow['cnt'] ?? 0) + (int)($requestRow['cnt'] ?? 0);
    }

    public static function getAverageAdherence(string $guardianNic): int
    {
        $normalizedNic = self::normalizeNic($guardianNic);
        if ($normalizedNic === '') {
            return 0;
        }

        if (PharmacyContext::tableExists('medication_schedules')) {
            $row = Database::fetchOne(
                "SELECT
                    SUM(CASE WHEN UPPER(COALESCE(s.status, '')) = 'TAKEN' THEN 1 ELSE 0 END) AS taken_count,
                    SUM(CASE WHEN UPPER(COALESCE(s.status, '')) IN ('TAKEN','MISSED') THEN 1 ELSE 0 END) AS tracked_count
                 FROM medication_schedules s
                 JOIN `" . self::PATIENT_TABLE . "` p ON s.patient_nic = p.nic
                 WHERE " . self::guardianMatchExpr('p'),
                's',
                [$normalizedNic]
            );

            if (is_array($row)) {
                $tracked = (int)($row['tracked_count'] ?? 0);
                $taken = (int)($row['taken_count'] ?? 0);
                if ($tracked > 0) {
                    return (int) round(($taken / $tracked) * 100);
                }
            }
        }

        if (PharmacyContext::tableExists('medication_log')) {
            $row = Database::fetchOne(
                "SELECT
                    SUM(CASE WHEN UPPER(COALESCE(ml.status, '')) = 'TAKEN' THEN 1 ELSE 0 END) AS taken_count,
                    SUM(CASE WHEN UPPER(COALESCE(ml.status, '')) IN ('TAKEN','MISSED') THEN 1 ELSE 0 END) AS tracked_count
                 FROM medication_log ml
                 JOIN `" . self::PATIENT_TABLE . "` p ON ml.patient_nic = p.nic
                 WHERE " . self::guardianMatchExpr('p'),
                's',
                [$normalizedNic]
            );

            if (is_array($row)) {
                $tracked = (int)($row['tracked_count'] ?? 0);
                $taken = (int)($row['taken_count'] ?? 0);
                if ($tracked > 0) {
                    return (int) round(($taken / $tracked) * 100);
                }
            }
        }

        return 0;
    }
}
