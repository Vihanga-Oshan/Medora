<?php

require_once ROOT . '/core/AppLogger.php';

class PatientsClientsModel
{
    private const NIC_COLLATION = 'utf8mb4_unicode_ci';

    private static function logDebug(string $message, array $context = []): void
    {
        AppLogger::write('pharmacist-patients-debug.log', 'DEBUG', $message, $context);
    }

    private static function backfillPatientSelections(int $currentPharmacyId): void
    {
        $currentPharmacyId = max(0, $currentPharmacyId);
        if ($currentPharmacyId <= 0) {
            return;
        }

        $rows = Database::fetchAll("
            SELECT DISTINCT src.patient_nic
            FROM (
                SELECT p.patient_nic COLLATE " . self::NIC_COLLATION . " AS patient_nic
                FROM prescriptions p
                WHERE p.pharmacy_id = ?

                UNION

                SELECT sm.patient_nic COLLATE " . self::NIC_COLLATION . " AS patient_nic
                FROM schedule_master sm
                WHERE sm.pharmacy_id = ?
            ) src
            LEFT JOIN patient_pharmacy_selection pps
              ON pps.patient_nic COLLATE " . self::NIC_COLLATION . " = src.patient_nic COLLATE " . self::NIC_COLLATION . "
             AND pps.is_active = 1
            WHERE pps.id IS NULL
        ", 'ii', [$currentPharmacyId, $currentPharmacyId]);

        self::logDebug('Backfill candidates loaded.', [
            'pharmacy_id' => $currentPharmacyId,
            'candidate_count' => count($rows),
            'db_error' => Database::$connection->error ?? '',
        ]);

        foreach ($rows as $row) {
            $patientNic = trim((string) ($row['patient_nic'] ?? ''));
            if ($patientNic === '') {
                continue;
            }

            $assigned = PharmacyContext::assignPatientSelection($patientNic, $currentPharmacyId);
            self::logDebug('Backfill assignment attempted.', [
                'pharmacy_id' => $currentPharmacyId,
                'patient_nic' => $patientNic,
                'assigned' => $assigned ? 1 : 0,
                'db_error' => Database::$connection->error ?? '',
            ]);
        }
    }

    public static function getAll(string $search = '', int $currentPharmacyId = 0): array
    {
        $search = trim($search);
        $currentPharmacyId = max(0, $currentPharmacyId);

        if ($currentPharmacyId > 0) {
            self::backfillPatientSelections($currentPharmacyId);
        }

        $sql = "
            SELECT p.nic, p.name, p.email, p.emergency_contact
            FROM patient p
        ";
        $types = '';
        $params = [];
        $where = [];

        if ($currentPharmacyId > 0) {
            $sql .= "
                INNER JOIN patient_pharmacy_selection pps
                    ON pps.patient_nic COLLATE " . self::NIC_COLLATION . " = p.nic COLLATE " . self::NIC_COLLATION . "
                   AND pps.is_active = 1
                   AND pps.pharmacy_id = ?
            ";
            $types .= 'i';
            $params[] = $currentPharmacyId;
        }

        if ($search !== '') {
            $where[] = '(p.nic LIKE ? OR p.name LIKE ? OR p.email LIKE ?)';
            $like = '%' . $search . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.name ASC, p.nic ASC';
        $rows = Database::fetchAll($sql, $types, $params);
        self::logDebug('Patient list query completed.', [
            'pharmacy_id' => $currentPharmacyId,
            'search' => $search,
            'row_count' => count($rows),
            'db_error' => Database::$connection->error ?? '',
        ]);

        return $rows;
    }
}
