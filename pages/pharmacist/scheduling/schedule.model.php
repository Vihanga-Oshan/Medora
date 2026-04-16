<?php
/**
 * Medication Scheduling Model
 */
class ScheduleModel
{
    private static bool $lookupsEnsured = false;

    private static function ensureLookupData(): void
    {
        if (self::$lookupsEnsured) {
            return;
        }
        self::$lookupsEnsured = true;

        $counts = [
            'dosage_categories' => (int) (Database::fetchOne("SELECT COUNT(*) AS c FROM dosage_categories")['c'] ?? 0),
            'frequencies' => (int) (Database::fetchOne("SELECT COUNT(*) AS c FROM frequencies")['c'] ?? 0),
            'meal_timing' => (int) (Database::fetchOne("SELECT COUNT(*) AS c FROM meal_timing")['c'] ?? 0),
        ];

        if ($counts['dosage_categories'] === 0) {
            foreach (['1 tablet', '2 tablets', '5 ml', '10 ml', '1 capsule', '2 capsules'] as $label) {
                Database::execute(
                    "INSERT INTO dosage_categories (label) VALUES (?)",
                    's',
                    [$label]
                );
            }
        }

        if ($counts['frequencies'] === 0) {
            $rows = [
                ['Once Daily', '08:00'],
                ['Twice Daily', '08:00,20:00'],
                ['Three Times Daily', '08:00,14:00,20:00'],
                ['Every 6 Hours', '06:00,12:00,18:00,00:00'],
                ['Every 8 Hours', '06:00,14:00,22:00'],
                ['As Needed', ''],
            ];
            foreach ($rows as [$label, $timesOfDay]) {
                Database::execute(
                    "INSERT INTO frequencies (label, times_of_day) VALUES (?, ?)",
                    'ss',
                    [$label, $timesOfDay]
                );
            }
        }

        if ($counts['meal_timing'] === 0) {
            foreach (['Before Meals', 'After Meals', 'With Food', 'Before Bed', 'Any Time'] as $label) {
                Database::execute(
                    "INSERT INTO meal_timing (label) VALUES (?)",
                    's',
                    [$label]
                );
            }
        }
    }

    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0)
            return $fromToken;
        return PharmacyContext::resolvePharmacistPharmacyId((int) ($auth['id'] ?? 0));
    }

    private static function resolveSchedulePharmacyId(array $schedules): int
    {
        $pharmacyId = self::currentPharmacyId();
        if ($pharmacyId > 0) {
            return $pharmacyId;
        }

        $prescriptionId = (int) ($schedules[0]['prescription_id'] ?? 0);
        if ($prescriptionId <= 0) {
            return 0;
        }

        $row = Database::fetchOne("SELECT pharmacy_id FROM prescriptions WHERE id = ? LIMIT 1", 'i', [$prescriptionId]);
        return (int) ($row['pharmacy_id'] ?? 0);
    }

    private static function resolvePatientPharmacyId(string $nic): int
    {
        $pharmacyId = self::currentPharmacyId();
        if ($pharmacyId > 0) {
            return $pharmacyId;
        }

        $row = Database::fetchOne("SELECT pharmacy_id FROM patient_pharmacy_selection WHERE patient_nic = ? AND is_active = 1 ORDER BY id DESC LIMIT 1", 's', [$nic]);
        if ($row) {
            return (int) ($row['pharmacy_id'] ?? 0);
        }

        $row = Database::fetchOne("
            SELECT COALESCE(sm.pharmacy_id, p.pharmacy_id, 0) AS pharmacy_id
            FROM schedule_master sm
            LEFT JOIN prescriptions p ON p.id = sm.prescription_id
            WHERE sm.patient_nic = ?
            ORDER BY sm.id DESC
            LIMIT 1
        ", 's', [$nic]);
        return (int) ($row['pharmacy_id'] ?? 0);
    }

    private static function fetchRows(string $sql): array
    {
        return Database::fetchAll($sql);
    }

    private static function logSchedulingError(string $context): void
    {
        $err = trim((string) (Database::$connection->error ?? ''));
        if ($err === '') {
            return;
        }
        $dir = ROOT . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $line = '[' . date('Y-m-d H:i:s') . "] [ERROR] $context - $err" . PHP_EOL;
        @file_put_contents($dir . '/scheduling-error.log', $line, FILE_APPEND);
    }

    private static function buildReminderMessage(array $schedule): string
    {
        $medicineId = (int) ($schedule['medicine_id'] ?? 0);
        $medicineName = 'medication';
        if ($medicineId > 0) {
            $row = Database::fetchOne(
                "SELECT COALESCE(NULLIF(TRIM(med_name), ''), NULLIF(TRIM(name), '')) AS med_label
                 FROM medicines
                 WHERE id = ?
                 LIMIT 1",
                'i',
                [$medicineId]
            );
            $name = trim((string) ($row['med_label'] ?? ''));
            if ($name !== '') {
                $medicineName = $name;
            }
        }

        $dosage = trim((string) ($schedule['dosage'] ?? ''));
        $mealTiming = trim((string) ($schedule['meal_timing'] ?? ''));
        $parts = ["Time to take $medicineName"];
        if ($dosage !== '') {
            $parts[] = "($dosage)";
        }
        if ($mealTiming !== '') {
            $parts[] = "- $mealTiming";
        }
        return implode(' ', $parts) . '.';
    }

    public static function getMedicines(): array
    {
        $sql = "
            SELECT
                MIN(id) AS id,
                med_label AS name
            FROM (
                SELECT
                    id,
                    TRIM(COALESCE(NULLIF(med_name, ''), NULLIF(name, ''))) AS med_label
                FROM medicines
            ) m
            WHERE med_label <> ''
        ";

        $sql .= "
            GROUP BY LOWER(med_label), med_label
            ORDER BY med_label ASC
        ";

        return Database::fetchAll($sql);
    }

    public static function getDosages(): array
    {
        self::ensureLookupData();
        return self::fetchRows("SELECT id, label FROM dosage_categories ORDER BY id ASC");
    }

    public static function getFrequencies(): array
    {
        self::ensureLookupData();
        return self::fetchRows("SELECT id, label, times_of_day FROM frequencies ORDER BY id ASC");
    }

    public static function getMealTimings(): array
    {
        self::ensureLookupData();
        return self::fetchRows("SELECT id, label FROM meal_timing ORDER BY id ASC");
    }

    public static function getPrescription(int $id): ?array
    {
        $where = ['id = ?'];
        $params = [$id];
        $types = 'i';
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $where[] = 'pharmacy_id = ?';
            $params[] = self::currentPharmacyId();
            $types .= 'i';
        }
        return Database::fetchOne("SELECT * FROM prescriptions WHERE " . implode(' AND ', $where) . " LIMIT 1", $types, $params);
    }

    public static function bulkInsert(array $schedules): bool
    {
        if (empty($schedules)) {
            self::logSchedulingError('bulkInsert called with empty schedules');
            return false;
        }

        $insertedAnything = false;

        $first = $schedules[0];
        $pharmacistId = (int) (Auth::getUser()['id'] ?? 0);
        $pharmacyId = self::resolveSchedulePharmacyId($schedules);

        $masterSql = "INSERT INTO schedule_master (prescription_id, patient_nic, pharmacist_id";
        $masterSql .= $pharmacyId > 0 && PharmacyContext::tableHasPharmacyId('schedule_master') ? ", pharmacy_id) VALUES (?, ?, ?, ?)" : ") VALUES (?, ?, ?)";
        $masterParams = [(int) $first['prescription_id'], (string) $first['patient_nic'], $pharmacistId > 0 ? $pharmacistId : null];
        $masterTypes = 'isi';
        if ($pharmacyId > 0 && PharmacyContext::tableHasPharmacyId('schedule_master')) {
            $masterTypes .= 'i';
            $masterParams[] = $pharmacyId;
        }

        $masterOk = Database::execute($masterSql, $masterTypes, $masterParams);
        if (!$masterOk) {
            self::logSchedulingError('Insert into schedule_master failed');
            return false;
        }

        $scheduleMasterId = (int) (Database::$connection->insert_id ?? 0);
        if ($scheduleMasterId <= 0) {
            self::logSchedulingError('schedule_master insert_id missing');
            return false;
        }

        foreach ($schedules as $s) {
            $durationDays = max(1, (int) $s['duration_days']);
            $endDate = date('Y-m-d', strtotime((string) $s['start_date'] . ' +' . max(0, $durationDays - 1) . ' days'));

            $scheduleSql = "INSERT INTO medication_schedule (
                schedule_master_id,
                medicine_id,
                dosage_id,
                frequency_id,
                meal_timing_id,
                start_date,
                end_date,
                duration_days,
                instructions";
            $scheduleSql .= $pharmacyId > 0 && PharmacyContext::tableHasPharmacyId('medication_schedule') ? ",
                pharmacy_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" : "
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $scheduleParams = [
                $scheduleMasterId,
                (int) $s['medicine_id'],
                isset($s['dosage_id']) && $s['dosage_id'] !== '' ? (int) $s['dosage_id'] : null,
                isset($s['frequency_id']) && $s['frequency_id'] !== '' ? (int) $s['frequency_id'] : null,
                isset($s['meal_timing_id']) && $s['meal_timing_id'] !== '' ? (int) $s['meal_timing_id'] : null,
                (string) $s['start_date'],
                $endDate,
                $durationDays,
                trim((string) ($s['instructions'] ?? '')) !== '' ? (string) $s['instructions'] : null,
            ];
            $scheduleTypes = 'iiiiissis';

            if ($pharmacyId > 0 && PharmacyContext::tableHasPharmacyId('medication_schedule')) {
                $scheduleTypes .= 'i';
                $scheduleParams[] = $pharmacyId;
            }

            $ok = Database::execute($scheduleSql, $scheduleTypes, $scheduleParams);
            if (!$ok) {
                self::logSchedulingError('Insert into medication_schedule failed');
                return false;
            }

            $legacyId = (int) (Database::$connection->insert_id ?? 0);
            if ($legacyId > 0) {
                for ($day = 0; $day < $durationDays; $day++) {
                    $doseDate = date('Y-m-d', strtotime((string) $s['start_date'] . ' +' . $day . ' days'));
                    MedicationReminderService::createEventsForSchedule([
                        'patient_nic' => (string) $s['patient_nic'],
                        'source_type' => 'legacy',
                        'source_schedule_id' => $legacyId,
                        'dose_date' => $doseDate,
                        'times_of_day' => (string) ($s['times_of_day'] ?? ''),
                        'frequency_label' => (string) ($s['frequency'] ?? ''),
                        'message' => self::buildReminderMessage($s),
                        'pharmacy_id' => $pharmacyId,
                    ]);
                }
            }
            $insertedAnything = true;
        }

        if (!$insertedAnything) {
            self::logSchedulingError('No schedule rows inserted');
            return false;
        }

        $prescriptionId = (int) ($schedules[0]['prescription_id'] ?? 0);
        if ($prescriptionId > 0) {
            $where = ['id = ?'];
            $params = [$prescriptionId];
            $types = 'i';
            if (PharmacyContext::tableHasPharmacyId('prescriptions') && $pharmacyId > 0) {
                $where[] = 'pharmacy_id = ?';
                $params[] = $pharmacyId;
                $types .= 'i';
            }
            Database::execute("UPDATE prescriptions SET status = 'SCHEDULED' WHERE " . implode(' AND ', $where), $types, $params);
        }

        return true;
    }

    public static function createNotification(string $nic, string $message): void
    {
        $pharmacyId = self::resolvePatientPharmacyId($nic);
        if (PharmacyContext::tableHasPharmacyId('notifications') && $pharmacyId > 0) {
            Database::execute(
                "INSERT INTO notifications (patient_nic, message, type, is_read, created_at, pharmacy_id) VALUES (?, ?, 'SCHEDULE', 0, NOW(), ?)",
                'ssi',
                [$nic, $message, $pharmacyId]
            );
            return;
        }
        Database::execute(
            "INSERT INTO notifications (patient_nic, message, type, is_read, created_at) VALUES (?, ?, 'SCHEDULE', 0, NOW())",
            'ss',
            [$nic, $message]
        );
    }

    /**
     * Keep patient pharmacy context aligned with the pharmacy that created the schedule.
     * This prevents "scheduled but not visible" issues caused by pharmacy_id filtering.
     */
    public static function syncPatientPharmacySelection(string $nic): void
    {
        $pharmacyId = self::resolvePatientPharmacyId($nic);
        if ($pharmacyId <= 0 || trim($nic) === '') {
            return;
        }

        $safeNic = trim($nic);

        Database::execute("UPDATE patient_pharmacy_selection SET is_active = 0 WHERE patient_nic = ?", 's', [$safeNic]);
        Database::execute(
            "INSERT INTO patient_pharmacy_selection (patient_nic, pharmacy_id, selected_at, is_active) VALUES (?, ?, NOW(), 1)",
            'si',
            [$safeNic, $pharmacyId]
        );
    }
}
