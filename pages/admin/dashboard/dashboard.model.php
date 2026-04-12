<?php
/**
 * Admin Dashboard Model (Medora)
 */
require_once __DIR__ . '/../common/admin.activity.php';

class DashboardModel
{
    private static function safeTable(string $table): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }

    private static function tableExists(string $table): bool
    {
        Database::setUpConnection();
        $safe = Database::escape($table);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function columnExists(string $table, string $column): bool
    {
        Database::setUpConnection();
        $safeTable = self::safeTable($table);
        $safeCol = Database::escape($column);
        $rs = Database::search("SHOW COLUMNS FROM `$safeTable` LIKE '$safeCol'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function resolveTable(array $candidates): ?string
    {
        foreach ($candidates as $table) {
            if (self::tableExists($table)) {
                return $table;
            }
        }
        return null;
    }

    private static function safeCount(string $table, ?string $where = null): int
    {
        $safeTable = self::safeTable($table);
        $sql = "SELECT COUNT(*) AS cnt FROM `$safeTable`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $rs = Database::search($sql);
        if (!($rs instanceof mysqli_result)) {
            return 0;
        }
        $row = $rs->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    private static function safeCountToday(string $table, string $dateTimeColumn): int
    {
        $safeTable = self::safeTable($table);
        $safeCol = preg_replace('/[^a-zA-Z0-9_]/', '', $dateTimeColumn);
        if ($safeCol === '') {
            return 0;
        }

        $rs = Database::search("
            SELECT COUNT(*) AS cnt
            FROM `$safeTable`
            WHERE DATE(`$safeCol`) = CURDATE()
        ");
        if (!($rs instanceof mysqli_result)) {
            return 0;
        }
        $row = $rs->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public static function getSummary(): array
    {
        $pharmacistTable = self::resolveTable(['pharmacists', 'pharmacist']);
        $patientTable = self::resolveTable(['patients', 'patient']);
        $guardianTable = self::resolveTable(['guardians', 'guardian']);
        $prescriptionTable = self::resolveTable(['prescriptions', 'prescription']);

        // Pharmacist counts (if status exists, count ACTIVE only)
        $activePharmacists = 0;
        if ($pharmacistTable !== null) {
            $where = null;
            if (self::columnExists($pharmacistTable, 'status')) {
                $where = "LOWER(status) = 'active'";
            } elseif (self::columnExists($pharmacistTable, 'is_active')) {
                $where = "is_active = 1";
            }
            $activePharmacists = self::safeCount($pharmacistTable, $where);
        }

        // Patient counts
        $totalPatients = $patientTable ? self::safeCount($patientTable) : 0;
        $patientsToday = 0;
        if ($patientTable !== null && self::columnExists($patientTable, 'created_at')) {
            $patientsToday = self::safeCountToday($patientTable, 'created_at');
        }

        // Guardian counts
        $totalGuardians = $guardianTable ? self::safeCount($guardianTable) : 0;

        // Prescription Review Status
        $pendingReviews = 0;
        if ($prescriptionTable !== null) {
            $where = self::columnExists($prescriptionTable, 'status') ? "status = 'PENDING'" : null;
            $pendingReviews = self::safeCount($prescriptionTable, $where);
        }

        return [
            'activePharmacists' => $activePharmacists,
            'totalPatients'     => $totalPatients,
            'patientsToday'     => $patientsToday,
            'totalGuardians'    => $totalGuardians,
            'pendingReviews'    => $pendingReviews,
        ];
    }

    private static function formatRelativeTime(string $dateTime): string
    {
        $ts = strtotime($dateTime);
        if ($ts === false) {
            return 'just now';
        }

        $diff = time() - $ts;
        if ($diff <= 5) return 'just now';
        if ($diff < 60) return $diff . ' seconds ago';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return date('M d, Y', $ts);
    }

    private static function addEvent(array &$events, string $name, string $action, string $timestamp, string $tone): void
    {
        $ts = strtotime($timestamp);
        if ($ts === false) {
            return;
        }

        $events[] = [
            'name'      => $name,
            'action'    => $action,
            'time'      => self::formatRelativeTime($timestamp),
            'tone'      => $tone,
            '__sort_ts' => $ts,
        ];
    }

    public static function getRecentLogs(): array
    {
        $logged = AdminActivityLog::getRecent(60);
        if (!empty($logged)) {
            $events = [];
            foreach ($logged as $log) {
                $createdAt = (string)($log['created_at'] ?? '');
                if ($createdAt === '') {
                    continue;
                }
                $events[] = [
                    'name'   => (string)($log['name'] ?? 'Admin'),
                    'action' => (string)($log['action'] ?? ''),
                    'tone'   => (string)($log['tone'] ?? 'blue'),
                    'time'   => self::formatRelativeTime($createdAt),
                ];
            }
            if (!empty($events)) {
                return $events;
            }
        }

        Database::setUpConnection();
        $events = [];

        // 1) Pharmacist requests: submitted / approved / rejected
        if (self::tableExists('pharmacist_requests') && self::columnExists('pharmacist_requests', 'created_at')) {
            $hasName = self::columnExists('pharmacist_requests', 'full_name');
            $hasStatus = self::columnExists('pharmacist_requests', 'status');
            $hasReviewedAt = self::columnExists('pharmacist_requests', 'reviewed_at');

            $nameSelect = $hasName ? 'full_name' : "'Pharmacist' AS full_name";
            $statusSelect = $hasStatus ? 'status' : "'pending' AS status";
            $reviewedAtSelect = $hasReviewedAt ? 'reviewed_at' : 'NULL AS reviewed_at';
            $orderExpr = $hasReviewedAt ? 'COALESCE(reviewed_at, created_at)' : 'created_at';

            $rs = Database::search("
                SELECT $nameSelect, $statusSelect, created_at, $reviewedAtSelect
                FROM pharmacist_requests
                ORDER BY $orderExpr DESC
                LIMIT 20
            ");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $name = trim((string)($row['full_name'] ?? 'Pharmacist'));
                    $status = strtolower((string)($row['status'] ?? 'pending'));
                    $createdAt = (string)($row['created_at'] ?? '');
                    $reviewedAt = (string)($row['reviewed_at'] ?? '');

                    if ($status === 'approved' && $reviewedAt !== '') {
                        self::addEvent($events, $name, 'Pharmacist request approved', $reviewedAt, 'green');
                    } elseif ($status === 'rejected' && $reviewedAt !== '') {
                        self::addEvent($events, $name, 'Pharmacist request rejected', $reviewedAt, 'red');
                    } elseif ($createdAt !== '') {
                        self::addEvent($events, $name, 'Submitted pharmacist account request', $createdAt, 'blue');
                    }
                }
            }
        }

        // 2) New pharmacist accounts
        $pharmacistTable = self::resolveTable(['pharmacists', 'pharmacist']);
        if ($pharmacistTable !== null && self::columnExists($pharmacistTable, 'created_at')) {
            $safeTable = self::safeTable($pharmacistTable);
            $rs = Database::search("
                SELECT name, created_at
                FROM `$safeTable`
                ORDER BY created_at DESC
                LIMIT 12
            ");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $name = trim((string)($row['name'] ?? 'Pharmacist'));
                    $createdAt = (string)($row['created_at'] ?? '');
                    if ($createdAt !== '') {
                        self::addEvent($events, $name, 'Created pharmacist account', $createdAt, 'green');
                    }
                }
            }
        }

        // 3) Pharmacies created / updated
        if (self::tableExists('pharmacies') && self::columnExists('pharmacies', 'created_at')) {
            $hasUpdatedAt = self::columnExists('pharmacies', 'updated_at');
            $rs = Database::search("
                SELECT name, status, created_at" . ($hasUpdatedAt ? ", updated_at" : "") . "
                FROM pharmacies
                ORDER BY created_at DESC
                LIMIT 12
            ");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $name = trim((string)($row['name'] ?? 'Pharmacy'));
                    $createdAt = (string)($row['created_at'] ?? '');
                    if ($createdAt !== '') {
                        self::addEvent($events, $name, 'Registered pharmacy', $createdAt, 'blue');
                    }

                    if ($hasUpdatedAt) {
                        $updatedAt = (string)($row['updated_at'] ?? '');
                        $status = strtolower((string)($row['status'] ?? 'active'));
                        $createdTs = strtotime($createdAt);
                        $updatedTs = strtotime($updatedAt);
                        if ($updatedAt !== '' && $updatedTs !== false && $createdTs !== false && $updatedTs > ($createdTs + 2)) {
                            self::addEvent($events, $name, 'Updated pharmacy status to ' . strtoupper($status), $updatedAt, 'blue');
                        }
                    }
                }
            }
        }

        // 4) Pharmacy assignments
        if (self::tableExists('pharmacy_users') && self::columnExists('pharmacy_users', 'created_at')) {
            $joinPharmacy = self::tableExists('pharmacies')
                ? "LEFT JOIN pharmacies ph ON ph.id = pu.pharmacy_id"
                : "";

            $pharmacistTable = self::resolveTable(['pharmacists', 'pharmacist']);
            $joinPharmacist = $pharmacistTable !== null
                ? "LEFT JOIN `" . self::safeTable($pharmacistTable) . "` pr ON pr.id = pu.pharmacist_id"
                : "";

            $rs = Database::search("
                SELECT pu.created_at, COALESCE(pr.name, 'Pharmacist') AS pharmacist_name, COALESCE(ph.name, 'Pharmacy') AS pharmacy_name
                FROM pharmacy_users pu
                $joinPharmacy
                $joinPharmacist
                ORDER BY pu.created_at DESC
                LIMIT 12
            ");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $pharmacistName = trim((string)($row['pharmacist_name'] ?? 'Pharmacist'));
                    $pharmacyName = trim((string)($row['pharmacy_name'] ?? 'Pharmacy'));
                    $createdAt = (string)($row['created_at'] ?? '');
                    if ($createdAt !== '') {
                        self::addEvent($events, $pharmacistName, 'Assigned to ' . $pharmacyName, $createdAt, 'blue');
                    }
                }
            }
        }

        // 5) New patient registrations
        $patientTable = self::resolveTable(['patients', 'patient']);
        if ($patientTable !== null && self::columnExists($patientTable, 'created_at') && self::columnExists($patientTable, 'name')) {
            $safeTable = self::safeTable($patientTable);
            $rs = Database::search("
                SELECT name, created_at
                FROM `$safeTable`
                ORDER BY created_at DESC
                LIMIT 12
            ");
            if ($rs instanceof mysqli_result) {
                while ($row = $rs->fetch_assoc()) {
                    $name = trim((string)($row['name'] ?? 'Patient'));
                    $createdAt = (string)($row['created_at'] ?? '');
                    if ($createdAt !== '') {
                        self::addEvent($events, $name, 'Registered as patient', $createdAt, 'green');
                    }
                }
            }
        }

        usort($events, static function (array $a, array $b): int {
            return (int)$b['__sort_ts'] <=> (int)$a['__sort_ts'];
        });

        $events = array_slice($events, 0, 10);
        foreach ($events as &$event) {
            unset($event['__sort_ts']);
        }
        unset($event);

        return $events;
    }
}
