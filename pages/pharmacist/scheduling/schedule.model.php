<?php
/**
 * Medication Scheduling Model
 */
class ScheduleModel
{
    private static array $columnExistsCache = [];

    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0)
            return $fromToken;
        return PharmacyContext::resolvePharmacistPharmacyId((int) ($auth['id'] ?? 0));
    }

    private static function tableExists(string $name): bool
    {
        return in_array($name, ['medication_schedules', 'medication_schedule', 'schedule_master', 'medication_log', 'medication_reminder_events', 'medicines', 'dosage_categories', 'frequencies', 'meal_timing', 'prescriptions', 'patient_pharmacy_selection', 'pharmacies'], true);
    }

    private static function columnExists(string $table, string $column): bool
    {
        $key = strtolower($table . '.' . $column);
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
        }

        $schema = [
            'medication_schedules' => ['id', 'patient_nic', 'medicine_id', 'dosage', 'frequency', 'meal_timing', 'schedule_date', 'status', 'instructions', 'prescription_id', 'created_at', 'pharmacy_id'],
            'medication_schedule' => ['id', 'start_date', 'end_date', 'duration_days', 'instructions', 'created_at', 'medicine_id', 'dosage_id', 'frequency_id', 'meal_timing_id', 'schedule_master_id'],
            'schedule_master' => ['id', 'prescription_id', 'patient_nic', 'pharmacist_id', 'created_at', 'updated_at', 'pharmacy_id'],
            'medication_log' => ['id', 'medication_schedule_id', 'patient_nic', 'dose_date', 'status', 'updated_at', 'time_slot', 'pharmacy_id'],
            'medication_reminder_events' => ['id', 'patient_nic', 'source_type', 'source_schedule_id', 'dose_date', 'time_slot', 'scheduled_at', 'message', 'status', 'delivered_at', 'delivered_notification_id', 'pharmacy_id', 'taken_at', 'created_at', 'updated_at'],
            'medicines' => ['id', 'name', 'med_name', 'generic_name', 'category', 'description', 'dosage_form', 'strength', 'quantity_in_stock', 'pricing', 'manufacturer', 'expiry_date', 'added_by', 'created_at', 'pharmacy_id'],
            'dosage_categories' => ['id', 'label'],
            'frequencies' => ['id', 'label', 'times_of_day'],
            'meal_timing' => ['id', 'label'],
            'prescriptions' => ['id', 'patient_nic', 'file_name', 'file_path', 'upload_date', 'status', 'pharmacy_id'],
            'patient_pharmacy_selection' => ['id', 'patient_nic', 'pharmacy_id', 'selected_at', 'is_active'],
            'pharmacies' => ['id', 'name', 'address_line1', 'address_line2', 'city', 'district', 'postal_code', 'latitude', 'longitude', 'phone', 'email', 'is_demo', 'status', 'created_at', 'updated_at'],
        ];

        $exists = in_array($column, $schema[$table] ?? [], true);
        self::$columnExistsCache[$key] = $exists;
        return $exists;
    }

    private static function ensureLookupData(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        // Ensure the lookup tables exist if this DB started from a partial schema.
        Database::iud("CREATE TABLE IF NOT EXISTS meal_timing (
            id INT NOT NULL AUTO_INCREMENT,
            label VARCHAR(50) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        Database::iud("CREATE TABLE IF NOT EXISTS frequencies (
            id INT NOT NULL AUTO_INCREMENT,
            label VARCHAR(50) NOT NULL,
            times_of_day VARCHAR(50) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        Database::iud("CREATE TABLE IF NOT EXISTS dosage_categories (
            id INT NOT NULL AUTO_INCREMENT,
            label VARCHAR(50) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Dosage categories to keep FK-compatible dropdown values available.
        $dosageLabels = ['1 tablet', '2 tablets', '5 ml'];
        foreach ($dosageLabels as $label) {
            $safe = Database::escape($label);
            Database::iud("INSERT INTO dosage_categories (label)
                           SELECT '$safe'
                           FROM DUAL
                           WHERE NOT EXISTS (
                               SELECT 1 FROM dosage_categories WHERE LOWER(TRIM(label)) = LOWER(TRIM('$safe')) LIMIT 1
                           )");
        }

        // Meal timings (from provided reference).
        $mealLabels = ['Before Meal', 'After Meal', 'With Meal'];
        foreach ($mealLabels as $label) {
            $safe = Database::escape($label);
            Database::iud("INSERT INTO meal_timing (label)
                           SELECT '$safe'
                           FROM DUAL
                           WHERE NOT EXISTS (
                               SELECT 1 FROM meal_timing WHERE LOWER(TRIM(label)) = LOWER(TRIM('$safe')) LIMIT 1
                           )");
        }

        // Frequencies (from provided reference).
        $freqRows = [
            ['Morning', 'morning'],
            ['Daytime', 'day'],
            ['Night', 'night'],
            ['Morning & Night', 'morning,night'],
            ['Day & Night', 'day,night'],
            ['Morning & Day', 'morning,day'],
            ['Morning & Day & Night', 'morning,day,night'],
        ];
        foreach ($freqRows as [$label, $times]) {
            $safeLabel = Database::escape($label);
            $safeTimes = Database::escape($times);
            Database::iud("INSERT INTO frequencies (label, times_of_day)
                           SELECT '$safeLabel', '$safeTimes'
                           FROM DUAL
                           WHERE NOT EXISTS (
                               SELECT 1 FROM frequencies WHERE LOWER(TRIM(label)) = LOWER(TRIM('$safeLabel')) LIMIT 1
                           )");

            // Keep canonical times_of_day in sync for existing label rows.
            Database::iud("UPDATE frequencies
                           SET times_of_day = '$safeTimes'
                           WHERE LOWER(TRIM(label)) = LOWER(TRIM('$safeLabel'))");
        }
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
        if ($medicineId > 0 && self::tableExists('medicines')) {
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
        if (!self::tableExists('medicines')) {
            return [];
        }

        $sql = "
            SELECT
                MIN(id) AS id,
                med_label AS name
            FROM (
                SELECT
                    id,
                    pharmacy_id,
                    TRIM(COALESCE(NULLIF(med_name, ''), NULLIF(name, ''))) AS med_label
                FROM medicines
            ) m
            WHERE med_label <> ''
        ";
        $types = '';
        $params = [];

        if (PharmacyContext::tableHasPharmacyId('medicines') && self::currentPharmacyId() > 0) {
            $sql .= ' AND m.pharmacy_id = ?';
            $types .= 'i';
            $params[] = self::currentPharmacyId();
        }

        $sql .= "
            GROUP BY LOWER(med_label), med_label
            ORDER BY med_label ASC
        ";

        return Database::fetchAll($sql, $types, $params);
    }

    public static function getDosages(): array
    {
        self::ensureLookupData();
        if (!self::tableExists('dosage_categories')) {
            return [];
        }
        return self::fetchRows("SELECT id, label FROM dosage_categories ORDER BY id ASC");
    }

    public static function getFrequencies(): array
    {
        self::ensureLookupData();
        if (!self::tableExists('frequencies')) {
            return [];
        }
        return self::fetchRows("SELECT id, label, times_of_day FROM frequencies ORDER BY id ASC");
    }

    public static function getMealTimings(): array
    {
        self::ensureLookupData();
        if (!self::tableExists('meal_timing')) {
            return [];
        }
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

        $hasExpandedTable = self::tableExists('medication_schedules')
            && self::columnExists('medication_schedules', 'patient_nic')
            && self::columnExists('medication_schedules', 'medicine_id')
            && self::columnExists('medication_schedules', 'schedule_date');
        $hasLegacyTables = self::tableExists('schedule_master') && self::tableExists('medication_schedule');
        if (!$hasExpandedTable && !$hasLegacyTables) {
            self::logSchedulingError('No supported schedule tables found');
            return false;
        }

        $insertedAnything = false;
        $useExpandedFlow = $hasExpandedTable;
        $useLegacyFlow = !$hasExpandedTable && $hasLegacyTables;

        // Newer PHP flow: one row per date in medication_schedules.
        if ($useExpandedFlow) {
            foreach ($schedules as $s) {
                $nic = Database::$connection->real_escape_string($s['patient_nic']);
                $medId = (int) $s['medicine_id'];
                $dosage = Database::$connection->real_escape_string($s['dosage']);
                $freq = Database::$connection->real_escape_string($s['frequency']);
                $meal = Database::$connection->real_escape_string($s['meal_timing'] ?? '');
                $start = Database::$connection->real_escape_string($s['start_date']);
                $dur = max(1, (int) $s['duration_days']);
                $inst = Database::$connection->real_escape_string($s['instructions'] ?? '');
                $prescId = (int) $s['prescription_id'];

                for ($i = 0; $i < $dur; $i++) {
                    $currentDate = date('Y-m-d', strtotime("$start +$i days"));
                    $cols = ['patient_nic', 'medicine_id', 'schedule_date'];
                    $vals = ["'$nic'", (string) $medId, "'$currentDate'"];
                    if (self::columnExists('medication_schedules', 'dosage')) {
                        $cols[] = 'dosage';
                        $vals[] = "'$dosage'";
                    }
                    if (self::columnExists('medication_schedules', 'frequency')) {
                        $cols[] = 'frequency';
                        $vals[] = "'$freq'";
                    }
                    if (self::columnExists('medication_schedules', 'meal_timing')) {
                        $cols[] = 'meal_timing';
                        $vals[] = "'$meal'";
                    }
                    if (self::columnExists('medication_schedules', 'instructions')) {
                        $cols[] = 'instructions';
                        $vals[] = "'$inst'";
                    }
                    if (self::columnExists('medication_schedules', 'status')) {
                        $cols[] = 'status';
                        $vals[] = "'PENDING'";
                    }
                    if (self::columnExists('medication_schedules', 'prescription_id')) {
                        $cols[] = 'prescription_id';
                        $vals[] = (string) $prescId;
                    }
                    if (self::columnExists('medication_schedules', 'pharmacy_id') && self::currentPharmacyId() > 0) {
                        $cols[] = 'pharmacy_id';
                        $vals[] = (string) self::currentPharmacyId();
                    }
                    $ok = Database::iud("INSERT INTO medication_schedules (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")");
                    if (!$ok) {
                        self::logSchedulingError('Insert into medication_schedules failed');
                        return false;
                    }
                    $expandedId = (int) (Database::$connection->insert_id ?? 0);
                    if ($expandedId > 0) {
                        MedicationReminderService::createEventsForSchedule([
                            'patient_nic' => (string) $s['patient_nic'],
                            'source_type' => 'expanded',
                            'source_schedule_id' => $expandedId,
                            'dose_date' => $currentDate,
                            'times_of_day' => (string) ($s['times_of_day'] ?? ''),
                            'frequency_label' => (string) ($s['frequency'] ?? ''),
                            'message' => self::buildReminderMessage($s),
                            'pharmacy_id' => self::currentPharmacyId(),
                        ]);
                    }
                    $insertedAnything = true;
                }
            }
        }

        // Java-compatible flow: schedule_master + medication_schedule (start + duration).
        if ($useLegacyFlow) {
            $first = $schedules[0];
            $nic = Database::$connection->real_escape_string($first['patient_nic']);
            $prescId = (int) $first['prescription_id'];

            $masterCols = [];
            $masterVals = [];
            if (self::columnExists('schedule_master', 'prescription_id')) {
                $masterCols[] = 'prescription_id';
                $masterVals[] = (string) $prescId;
            }
            if (self::columnExists('schedule_master', 'patient_nic')) {
                $masterCols[] = 'patient_nic';
                $masterVals[] = "'$nic'";
            }
            if (empty($masterCols)) {
                self::logSchedulingError('No writable columns found for schedule_master');
                return false;
            }
            if (self::columnExists('schedule_master', 'pharmacy_id') && self::currentPharmacyId() > 0) {
                $masterCols[] = 'pharmacy_id';
                $masterVals[] = (string) self::currentPharmacyId();
            }
            $masterOk = Database::iud("INSERT INTO schedule_master (" . implode(', ', $masterCols) . ") VALUES (" . implode(', ', $masterVals) . ")");
            if (!$masterOk) {
                self::logSchedulingError('Insert into schedule_master failed');
                return false;
            }
            $scheduleMasterId = (int) Database::$connection->insert_id;
            if ($scheduleMasterId <= 0) {
                self::logSchedulingError('schedule_master insert_id missing');
                return false;
            }

            foreach ($schedules as $s) {
                $medId = (int) $s['medicine_id'];
                $dosageId = isset($s['dosage_id']) && $s['dosage_id'] !== '' ? (int) $s['dosage_id'] : 0;
                $frequencyId = isset($s['frequency_id']) && $s['frequency_id'] !== '' ? (int) $s['frequency_id'] : 0;
                $mealTimingId = isset($s['meal_timing_id']) && $s['meal_timing_id'] !== '' ? (int) $s['meal_timing_id'] : 0;
                $startDate = Database::$connection->real_escape_string((string) $s['start_date']);
                $durationDays = max(1, (int) $s['duration_days']);
                $instructions = Database::$connection->real_escape_string((string) ($s['instructions'] ?? ''));

                $dosageSql = $dosageId > 0 ? (string) $dosageId : "NULL";
                $frequencySql = $frequencyId > 0 ? (string) $frequencyId : "NULL";
                $mealSql = $mealTimingId > 0 ? (string) $mealTimingId : "NULL";
                $instSql = $instructions !== '' ? "'$instructions'" : "NULL";

                $msCols = [];
                $msVals = [];
                if (self::columnExists('medication_schedule', 'schedule_master_id')) {
                    $msCols[] = 'schedule_master_id';
                    $msVals[] = (string) $scheduleMasterId;
                }
                if (self::columnExists('medication_schedule', 'medicine_id')) {
                    $msCols[] = 'medicine_id';
                    $msVals[] = (string) $medId;
                }
                if (self::columnExists('medication_schedule', 'dosage_id')) {
                    $msCols[] = 'dosage_id';
                    $msVals[] = $dosageSql;
                }
                if (self::columnExists('medication_schedule', 'frequency_id')) {
                    $msCols[] = 'frequency_id';
                    $msVals[] = $frequencySql;
                }
                if (self::columnExists('medication_schedule', 'meal_timing_id')) {
                    $msCols[] = 'meal_timing_id';
                    $msVals[] = $mealSql;
                }
                if (self::columnExists('medication_schedule', 'start_date')) {
                    $msCols[] = 'start_date';
                    $msVals[] = "'$startDate'";
                }
                if (self::columnExists('medication_schedule', 'duration_days')) {
                    $msCols[] = 'duration_days';
                    $msVals[] = (string) $durationDays;
                } elseif (self::columnExists('medication_schedule', 'end_date') && self::columnExists('medication_schedule', 'start_date')) {
                    $endDate = date('Y-m-d', strtotime($startDate . ' +' . max(0, $durationDays - 1) . ' days'));
                    $msCols[] = 'end_date';
                    $msVals[] = "'" . Database::$connection->real_escape_string($endDate) . "'";
                }
                if (self::columnExists('medication_schedule', 'instructions')) {
                    $msCols[] = 'instructions';
                    $msVals[] = $instSql;
                }
                if (empty($msCols)) {
                    self::logSchedulingError('No writable columns found for medication_schedule');
                    return false;
                }
                if (self::columnExists('medication_schedule', 'pharmacy_id') && self::currentPharmacyId() > 0) {
                    $msCols[] = 'pharmacy_id';
                    $msVals[] = (string) self::currentPharmacyId();
                }
                $ok = Database::iud("INSERT INTO medication_schedule (" . implode(', ', $msCols) . ") VALUES (" . implode(', ', $msVals) . ")");
                if (!$ok) {
                    self::logSchedulingError('Insert into medication_schedule failed');
                    return false;
                }
                $legacyId = (int) (Database::$connection->insert_id ?? 0);
                if ($legacyId > 0) {
                    for ($day = 0; $day < $durationDays; $day++) {
                        $doseDate = date('Y-m-d', strtotime($startDate . ' +' . $day . ' days'));
                        MedicationReminderService::createEventsForSchedule([
                            'patient_nic' => (string) $s['patient_nic'],
                            'source_type' => 'legacy',
                            'source_schedule_id' => $legacyId,
                            'dose_date' => $doseDate,
                            'times_of_day' => (string) ($s['times_of_day'] ?? ''),
                            'frequency_label' => (string) ($s['frequency'] ?? ''),
                            'message' => self::buildReminderMessage($s),
                            'pharmacy_id' => self::currentPharmacyId(),
                        ]);
                    }
                }
                $insertedAnything = true;
            }
        }

        if (!$insertedAnything) {
            self::logSchedulingError('No schedule rows inserted');
            return false;
        }

        // Keep prescription lifecycle aligned with Java flow.
        $prescriptionId = (int) ($schedules[0]['prescription_id'] ?? 0);
        if ($prescriptionId > 0 && self::tableExists('prescriptions')) {
            $where = ['id = ?'];
            $params = [$prescriptionId];
            $types = 'i';
            if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
                $where[] = 'pharmacy_id = ?';
                $params[] = self::currentPharmacyId();
                $types .= 'i';
            }
            Database::execute("UPDATE prescriptions SET status = 'SCHEDULED' WHERE " . implode(' AND ', $where), $types, $params);
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
