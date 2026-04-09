<?php
/**
 * Medication Scheduling Model
 */
class ScheduleModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int)($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) return $fromToken;
        return PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
    }

    private static function tableExists(string $name): bool
    {
        $safe = Database::escape($name);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function fetchRows(string $sql): array
    {
        $rs = Database::search($sql);
        if (!($rs instanceof mysqli_result)) {
            return [];
        }
        $rows = [];
        while ($row = $rs->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function getMedicines(): array
    {
        if (!self::tableExists('medicines')) {
            return [];
        }
        $where = '';
        if (PharmacyContext::tableHasPharmacyId('medicines') && self::currentPharmacyId() > 0) {
            $where = ' WHERE pharmacy_id = ' . self::currentPharmacyId();
        }
        return self::fetchRows("SELECT id, name FROM medicines$where ORDER BY name ASC");
    }

    public static function getDosages(): array
    {
        if (self::tableExists('dosage_categories')) {
            $rows = self::fetchRows("SELECT id, label FROM dosage_categories ORDER BY label ASC");
            if (!empty($rows)) return $rows;
        }
        $rows = [
            ['id' => 1, 'label' => '1 tablet'],
            ['id' => 2, 'label' => '2 tablets'],
            ['id' => 3, 'label' => '5 ml'],
        ];
        return $rows;
    }

    public static function getFrequencies(): array
    {
        if (self::tableExists('frequencies')) {
            $rows = self::fetchRows("SELECT id, label, times_of_day FROM frequencies ORDER BY label ASC");
            if (!empty($rows)) return $rows;
        }
        $rows = [
            ['id' => 1, 'label' => 'Once Daily', 'times_of_day' => 1],
            ['id' => 2, 'label' => 'Twice Daily', 'times_of_day' => 2],
            ['id' => 3, 'label' => 'Three Times Daily', 'times_of_day' => 3],
        ];
        return $rows;
    }

    public static function getMealTimings(): array
    {
        if (self::tableExists('meal_timing')) {
            $rows = self::fetchRows("SELECT id, label FROM meal_timing ORDER BY label ASC");
            if (!empty($rows)) return $rows;
        }
        $rows = [
            ['id' => 1, 'label' => 'Before Meal'],
            ['id' => 2, 'label' => 'After Meal'],
            ['id' => 3, 'label' => 'With Meal'],
        ];
        return $rows;
    }

    public static function getPrescription(int $id): ?array
    {
        $where = ["id = $id"];
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $where[] = "pharmacy_id = " . self::currentPharmacyId();
        }
        return Database::fetchOne("SELECT * FROM prescriptions WHERE " . implode(' AND ', $where) . " LIMIT 1");
    }

    public static function bulkInsert(array $schedules): bool
    {
        if (empty($schedules)) {
            return false;
        }

        $hasExpandedTable = self::tableExists('medication_schedules');
        $hasLegacyTables = self::tableExists('schedule_master') && self::tableExists('medication_schedule');
        if (!$hasExpandedTable && !$hasLegacyTables) {
            return false;
        }

        $insertedAnything = false;

        // Newer PHP flow: one row per date in medication_schedules.
        if ($hasExpandedTable) {
            foreach ($schedules as $s) {
                $nic = Database::$connection->real_escape_string($s['patient_nic']);
                $medId = (int)$s['medicine_id'];
                $dosage = Database::$connection->real_escape_string($s['dosage']);
                $freq = Database::$connection->real_escape_string($s['frequency']);
                $meal = Database::$connection->real_escape_string($s['meal_timing'] ?? '');
                $start = Database::$connection->real_escape_string($s['start_date']);
                $dur = max(1, (int)$s['duration_days']);
                $inst = Database::$connection->real_escape_string($s['instructions'] ?? '');
                $prescId = (int)$s['prescription_id'];

                for ($i = 0; $i < $dur; $i++) {
                    $currentDate = date('Y-m-d', strtotime("$start +$i days"));
                    $cols = ['patient_nic', 'medicine_id', 'dosage', 'frequency', 'meal_timing', 'schedule_date', 'instructions', 'status', 'prescription_id'];
                    $vals = ["'$nic'", (string)$medId, "'$dosage'", "'$freq'", "'$meal'", "'$currentDate'", "'$inst'", "'PENDING'", (string)$prescId];
                    if (PharmacyContext::tableHasPharmacyId('medication_schedules') && self::currentPharmacyId() > 0) {
                        $cols[] = 'pharmacy_id';
                        $vals[] = (string)self::currentPharmacyId();
                    }
                    $ok = Database::iud("INSERT INTO medication_schedules (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")");
                    if (!$ok) {
                        return false;
                    }
                    $insertedAnything = true;
                }
            }
        }

        // Java-compatible flow: schedule_master + medication_schedule (start + duration).
        if ($hasLegacyTables) {
            $first = $schedules[0];
            $nic = Database::$connection->real_escape_string($first['patient_nic']);
            $prescId = (int)$first['prescription_id'];

            $masterCols = ['prescription_id', 'patient_nic'];
            $masterVals = [(string)$prescId, "'$nic'"];
            if (PharmacyContext::tableHasPharmacyId('schedule_master') && self::currentPharmacyId() > 0) {
                $masterCols[] = 'pharmacy_id';
                $masterVals[] = (string)self::currentPharmacyId();
            }
            $masterOk = Database::iud("INSERT INTO schedule_master (" . implode(', ', $masterCols) . ") VALUES (" . implode(', ', $masterVals) . ")");
            if (!$masterOk) {
                return false;
            }
            $scheduleMasterId = (int) Database::$connection->insert_id;
            if ($scheduleMasterId <= 0) {
                return false;
            }

            foreach ($schedules as $s) {
                $medId = (int)$s['medicine_id'];
                $dosageId = isset($s['dosage_id']) && $s['dosage_id'] !== '' ? (int)$s['dosage_id'] : 0;
                $frequencyId = isset($s['frequency_id']) && $s['frequency_id'] !== '' ? (int)$s['frequency_id'] : 0;
                $mealTimingId = isset($s['meal_timing_id']) && $s['meal_timing_id'] !== '' ? (int)$s['meal_timing_id'] : 0;
                $startDate = Database::$connection->real_escape_string((string)$s['start_date']);
                $durationDays = max(1, (int)$s['duration_days']);
                $instructions = Database::$connection->real_escape_string((string)($s['instructions'] ?? ''));

                $dosageSql = $dosageId > 0 ? (string)$dosageId : "NULL";
                $frequencySql = $frequencyId > 0 ? (string)$frequencyId : "NULL";
                $mealSql = $mealTimingId > 0 ? (string)$mealTimingId : "NULL";
                $instSql = $instructions !== '' ? "'$instructions'" : "NULL";

                $msCols = ['schedule_master_id', 'medicine_id', 'dosage_id', 'frequency_id', 'meal_timing_id', 'start_date', 'duration_days', 'instructions'];
                $msVals = [(string)$scheduleMasterId, (string)$medId, $dosageSql, $frequencySql, $mealSql, "'$startDate'", (string)$durationDays, $instSql];
                if (PharmacyContext::tableHasPharmacyId('medication_schedule') && self::currentPharmacyId() > 0) {
                    $msCols[] = 'pharmacy_id';
                    $msVals[] = (string)self::currentPharmacyId();
                }
                $ok = Database::iud("INSERT INTO medication_schedule (" . implode(', ', $msCols) . ") VALUES (" . implode(', ', $msVals) . ")");
                if (!$ok) {
                    return false;
                }
                $insertedAnything = true;
            }
        }

        if (!$insertedAnything) {
            return false;
        }

        // Keep prescription lifecycle aligned with Java flow.
        $prescriptionId = (int)($schedules[0]['prescription_id'] ?? 0);
        if ($prescriptionId > 0 && self::tableExists('prescriptions')) {
            $where = ["id = $prescriptionId"];
            if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
                $where[] = "pharmacy_id = " . self::currentPharmacyId();
            }
            Database::iud("UPDATE prescriptions SET status = 'SCHEDULED' WHERE " . implode(' AND ', $where));
        }

        return true;
    }

    public static function createNotification(string $nic, string $message): void
    {
        if (PharmacyContext::tableHasPharmacyId('notifications') && self::currentPharmacyId() > 0) {
            Database::execute(
                "INSERT INTO notifications (patient_nic, message, type, is_read, created_at, pharmacy_id) VALUES (?, ?, 'SCHEDULE', 0, NOW(), ?)",
                'ssi',
                [$nic, $message, self::currentPharmacyId()]
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
        $pharmacyId = self::currentPharmacyId();
        if ($pharmacyId <= 0 || trim($nic) === '') {
            return;
        }

        $safeNic = trim($nic);

        if (self::tableExists('patient_pharmacy_selection')) {
            Database::execute("UPDATE patient_pharmacy_selection SET is_active = 0 WHERE patient_nic = ?", 's', [$safeNic]);
            Database::execute(
                "INSERT INTO patient_pharmacy_selection (patient_nic, pharmacy_id, selected_at, is_active) VALUES (?, ?, NOW(), 1)",
                'si',
                [$safeNic, $pharmacyId]
            );
        }
    }
}
