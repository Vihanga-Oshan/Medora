<?php
/**
 * Pharmacist Dashboard Model
 */
class DashboardModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int)($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            return $fromToken;
        }
        $pharmacistId = (int)($auth['id'] ?? 0);
        return PharmacyContext::resolvePharmacistPharmacyId($pharmacistId);
    }

    private static function prescriptionPharmacyWhere(string $alias = 'prescriptions'): string
    {
        $pid = self::currentPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId('prescriptions')) {
            return '1=1';
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    private static function countFromQuery(string $sql): int
    {
        $row = Database::fetchOne($sql);
        return (int)($row['cnt'] ?? 0);
    }

    private static function rowsFromQuery(string $sql): array
    {
        return Database::fetchAll($sql);
    }

    public static function getMetrics(): array
    {
        $pendingCount = self::countFromQuery(
            "SELECT COUNT(*) AS cnt FROM prescriptions WHERE UPPER(status) = 'PENDING' AND " . self::prescriptionPharmacyWhere('prescriptions')
        );
        $approvedCount = self::countFromQuery(
            "SELECT COUNT(*) AS cnt FROM prescriptions WHERE UPPER(status) = 'APPROVED' AND " . self::prescriptionPharmacyWhere('prescriptions')
        );
        $newPatientCount = self::countFromQuery(
            "SELECT COUNT(*) AS cnt FROM patient WHERE created_at >= NOW() - INTERVAL 1 DAY"
        );

        return [
            'pendingCount'    => (int)$pendingCount,
            'approvedCount'   => (int)$approvedCount,
            'newPatientCount' => (int)$newPatientCount,
        ];
    }

    public static function getPatientsNeedingCheck(int $limit = 5): array
    {
        return self::rowsFromQuery("
            SELECT DISTINCT p.name, p.chronic_issues AS condition_text, p.nic, pr.id AS prescription_id
            FROM patient p
            INNER JOIN prescriptions pr ON p.nic = pr.patient_nic
            WHERE UPPER(pr.status) = 'PENDING' AND " . self::prescriptionPharmacyWhere('pr') . "
            LIMIT $limit
        ");
    }

    public static function getPatientsNeedingSchedule(int $limit = 5): array
    {
        return self::rowsFromQuery("
            SELECT DISTINCT p.name, p.chronic_issues AS condition_text, p.nic, pr.id AS prescription_id
            FROM patient p
            INNER JOIN prescriptions pr ON p.nic = pr.patient_nic
            WHERE UPPER(pr.status) = 'APPROVED' AND " . self::prescriptionPharmacyWhere('pr') . "
            LIMIT $limit
        ");
    }
    public static function addDashboardComment(string $comment): bool
    {
        $comment = trim($comment);
        if ($comment === '') {
            return false;
        }

        $auth = Auth::getUser();
        $pharmacistId = (int) ($auth['id'] ?? 0);
        $pharmacyId = self::currentPharmacyId();

        if ($pharmacistId <= 0) {
            return false;
        }

      
        if (mb_strlen($comment) > 1000) {
            $comment = mb_substr($comment, 0, 1000);
        }

        $sql = "
        INSERT INTO pharmacist_dashboard_comments
            (pharmacist_id, pharmacy_id, comment_text, created_at)
        VALUES (?, ?, ?, NOW())
    ";

        return Database::execute($sql, 'iis', [$pharmacistId, $pharmacyId > 0 ? $pharmacyId : null, $comment]);
    }

    public static function getDashboardComments(int $limit = 10): array
    {
        $limit = max(1, min(50, (int) $limit));
        $pharmacyId = self::currentPharmacyId();

        $where = '';
        $types = '';
        $params = [];

        if ($pharmacyId > 0) {
            $where = "WHERE (c.pharmacy_id = ? OR c.pharmacy_id IS NULL)";
            $types = 'i';
            $params = [$pharmacyId];
        }

        $sql = "
        SELECT c.id, c.comment_text, c.created_at, p.name AS pharmacist_name
        FROM pharmacist_dashboard_comments c
        LEFT JOIN pharmacist p ON p.id = c.pharmacist_id
        $where
        ORDER BY c.id DESC
        LIMIT $limit
    ";

        return Database::fetchAll($sql, $types, $params);
    }
}
