<?php
/**
 * Admin Dashboard Model (Medora)
 */
require_once __DIR__ . '/../common/admin.activity.php';

class DashboardModel
{
    private static function countRows(string $table, ?string $where = null): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $row = Database::fetchOne($sql);
        return (int) ($row['cnt'] ?? 0);
    }

    private static function countRowsCreatedToday(string $table, string $dateTimeColumn): int
    {
        $row = Database::fetchOne("
            SELECT COUNT(*) AS cnt
            FROM `$table`
            WHERE DATE(`$dateTimeColumn`) = CURDATE()
        ");
        return (int) ($row['cnt'] ?? 0);
    }

    public static function getSummary(): array
    {
        $activePharmacists = self::countRows('pharmacist');

        // Patient counts
        $totalPatients = self::countRows('patient');
        $patientsToday = self::countRowsCreatedToday('patient', 'created_at');

        // Guardian counts
        $totalGuardians = self::countRows('guardian');

        // Prescription Review Status
        $pendingReviews = self::countRows('prescriptions', "status = 'PENDING'");

        return [
            'activePharmacists' => $activePharmacists,
            'totalPatients' => $totalPatients,
            'patientsToday' => $patientsToday,
            'totalGuardians' => $totalGuardians,
            'pendingReviews' => $pendingReviews,
        ];
    }

    private static function formatRelativeTime(string $dateTime): string
    {
        $ts = strtotime($dateTime);
        if ($ts === false) {
            return 'just now';
        }

        $diff = time() - $ts;
        if ($diff <= 5)
            return 'just now';
        if ($diff < 60)
            return $diff . ' seconds ago';
        if ($diff < 3600)
            return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400)
            return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800)
            return floor($diff / 86400) . ' days ago';
        return date('M d, Y', $ts);
    }

    private static function addEvent(array &$events, string $name, string $action, string $timestamp, string $tone): void
    {
        $ts = strtotime($timestamp);
        if ($ts === false) {
            return;
        }

        $events[] = [
            'name' => $name,
            'action' => $action,
            'time' => self::formatRelativeTime($timestamp),
            'tone' => $tone,
            '__sort_ts' => $ts,
        ];
    }

    public static function getRecentLogs(): array
    {
        $logged = AdminActivityLog::getRecent(60);
        if (!empty($logged)) {
            $events = [];
            foreach ($logged as $log) {
                $createdAt = (string) ($log['created_at'] ?? '');
                if ($createdAt === '') {
                    continue;
                }
                $events[] = [
                    'name' => (string) ($log['name'] ?? 'Admin'),
                    'action' => (string) ($log['action'] ?? ''),
                    'tone' => (string) ($log['tone'] ?? 'blue'),
                    'time' => self::formatRelativeTime($createdAt),
                ];
            }
            if (!empty($events)) {
                return $events;
            }
        }

        $events = [];

        // 1) Pharmacist requests: submitted / approved / rejected
        foreach (Database::fetchAll("
            SELECT full_name, status, created_at, reviewed_at
            FROM pharmacist_requests
            ORDER BY COALESCE(reviewed_at, created_at) DESC
            LIMIT 20
        ") as $row) {
            $name = trim((string) ($row['full_name'] ?? 'Pharmacist'));
            $status = strtolower((string) ($row['status'] ?? 'pending'));
            $createdAt = (string) ($row['created_at'] ?? '');
            $reviewedAt = (string) ($row['reviewed_at'] ?? '');

            if ($status === 'approved' && $reviewedAt !== '') {
                self::addEvent($events, $name, 'Pharmacist request approved', $reviewedAt, 'green');
            } elseif ($status === 'rejected' && $reviewedAt !== '') {
                self::addEvent($events, $name, 'Pharmacist request rejected', $reviewedAt, 'red');
            } elseif ($createdAt !== '') {
                self::addEvent($events, $name, 'Submitted pharmacist account request', $createdAt, 'blue');
            }
        }

        // 2) New pharmacist accounts
        foreach (Database::fetchAll("
            SELECT name, created_at
            FROM `pharmacist`
            ORDER BY created_at DESC
            LIMIT 12
        ") as $row) {
            $name = trim((string) ($row['name'] ?? 'Pharmacist'));
            $createdAt = (string) ($row['created_at'] ?? '');
            if ($createdAt !== '') {
                self::addEvent($events, $name, 'Created pharmacist account', $createdAt, 'green');
            }
        }

        // 3) Pharmacies created / updated
        foreach (Database::fetchAll("
            SELECT name, status, created_at, updated_at
            FROM pharmacies
            ORDER BY created_at DESC
            LIMIT 12
        ") as $row) {
            $name = trim((string) ($row['name'] ?? 'Pharmacy'));
            $createdAt = (string) ($row['created_at'] ?? '');
            if ($createdAt !== '') {
                self::addEvent($events, $name, 'Registered pharmacy', $createdAt, 'blue');
            }

            $updatedAt = (string) ($row['updated_at'] ?? '');
            $status = strtolower((string) ($row['status'] ?? 'active'));
            $createdTs = strtotime($createdAt);
            $updatedTs = strtotime($updatedAt);
            if ($updatedAt !== '' && $updatedTs !== false && $createdTs !== false && $updatedTs > ($createdTs + 2)) {
                self::addEvent($events, $name, 'Updated pharmacy status to ' . strtoupper($status), $updatedAt, 'blue');
            }
        }

        // 4) Pharmacy assignments
        foreach (Database::fetchAll("
            SELECT pu.created_at, COALESCE(pr.name, 'Pharmacist') AS pharmacist_name, COALESCE(ph.name, 'Pharmacy') AS pharmacy_name
            FROM pharmacy_users pu
            LEFT JOIN pharmacies ph ON ph.id = pu.pharmacy_id
            LEFT JOIN pharmacist pr ON pr.id = pu.pharmacist_id
            ORDER BY pu.created_at DESC
            LIMIT 12
        ") as $row) {
            $pharmacistName = trim((string) ($row['pharmacist_name'] ?? 'Pharmacist'));
            $pharmacyName = trim((string) ($row['pharmacy_name'] ?? 'Pharmacy'));
            $createdAt = (string) ($row['created_at'] ?? '');
            if ($createdAt !== '') {
                self::addEvent($events, $pharmacistName, 'Assigned to ' . $pharmacyName, $createdAt, 'blue');
            }
        }

        // 5) New patient registrations
        foreach (Database::fetchAll("
            SELECT name, created_at
            FROM `patient`
            ORDER BY created_at DESC
            LIMIT 12
        ") as $row) {
            $name = trim((string) ($row['name'] ?? 'Patient'));
            $createdAt = (string) ($row['created_at'] ?? '');
            if ($createdAt !== '') {
                self::addEvent($events, $name, 'Registered as patient', $createdAt, 'green');
            }
        }

        usort($events, static function (array $a, array $b): int {
            return (int) $b['__sort_ts'] <=> (int) $a['__sort_ts'];
        });

        $events = array_slice($events, 0, 10);
        foreach ($events as &$event) {
            unset($event['__sort_ts']);
        }
        unset($event);

        return $events;
    }
}
