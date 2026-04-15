<?php
/**
 * Medications Model
 * Ported from: ScheduleDAO.java / PatientMedicationTimetableServlet.java
 */
class MedicationsModel
{
    private static function currentPharmacyId(): int
    {
        return PharmacyContext::selectedPharmacyId();
    }

    private static function pharmacyCondition(string $alias, string $table): string
    {
        $pid = self::currentPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId($table)) {
            return '1=1';
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    private static function tableExists(string $table): bool
    {
        return in_array($table, ['medication_log', 'medication_schedule', 'schedule_master', 'medication_schedules', 'medication_reminder_events', 'prescriptions'], true);
    }

    public static function getByDate(string $nic, string $date): array
    {
        $eventRows = MedicationReminderService::getDoseRowsByDate($nic, $date);
        if (!empty($eventRows)) {
            return $eventRows;
        }

        $pid = self::currentPharmacyId();
        $rows = [];

        if (self::tableExists('medication_schedules')) {
            $rows = Database::fetchAll("
                SELECT ms.id, m.name AS medicine_name, ms.dosage, ms.frequency,
                       ms.meal_timing, ms.instructions, ms.status, ms.schedule_date
                FROM medication_schedules ms
                JOIN medicines m ON ms.medicine_id = m.id
                WHERE ms.patient_nic = ?
                  AND ms.schedule_date = ?
                  AND " . self::pharmacyCondition('ms', 'medication_schedules') . "
                  AND " . self::pharmacyCondition('m', 'medicines') . "
                ORDER BY FIELD(ms.frequency,'MORNING','AFTERNOON','EVENING','NIGHT')
            ", 'ss', [$nic, $date]);
            if (!empty($rows)) {
                return $rows;
            }

            if ($pid > 0) {
                $rows = Database::fetchAll("\n                    SELECT ms.id, m.name AS medicine_name, ms.dosage, ms.frequency,
                           ms.meal_timing, ms.instructions, ms.status, ms.schedule_date
                    FROM medication_schedules ms
                    JOIN medicines m ON ms.medicine_id = m.id
                    WHERE ms.patient_nic = ?
                      AND ms.schedule_date = ?
                    ORDER BY FIELD(ms.frequency,'MORNING','AFTERNOON','EVENING','NIGHT')
                ", 'ss', [$nic, $date]);
                if (!empty($rows)) {
                    return $rows;
                }
            }
        }

        if (self::tableExists('medication_schedule') && self::tableExists('schedule_master')) {
            $rows = Database::fetchAll("
                SELECT
                    ms.id,
                    COALESCE(m.name, 'Medication') AS medicine_name,
                    COALESCE(dc.label, '-') AS dosage,
                    COALESCE(f.label, '-') AS frequency,
                    COALESCE(mt.label, '-') AS meal_timing,
                    COALESCE(ms.instructions, '') AS instructions,
                    ? AS schedule_date,
                    COALESCE(UPPER(ml.status), 'PENDING') AS status
                FROM medication_schedule ms
                JOIN schedule_master sm ON sm.id = ms.schedule_master_id
                LEFT JOIN medicines m ON ms.medicine_id = m.id
                LEFT JOIN dosage_categories dc ON ms.dosage_id = dc.id
                LEFT JOIN frequencies f ON ms.frequency_id = f.id
                LEFT JOIN meal_timing mt ON ms.meal_timing_id = mt.id
                LEFT JOIN medication_log ml
                    ON ml.medication_schedule_id = ms.id
                    AND ml.patient_nic = sm.patient_nic
                    AND ml.dose_date = ?
                WHERE sm.patient_nic = ?
                  AND ? BETWEEN ms.start_date
                                  AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
                  AND " . self::pharmacyCondition('sm', 'schedule_master') . "
                  AND " . self::pharmacyCondition('ms', 'medication_schedule') . "
                  AND " . self::pharmacyCondition('m', 'medicines') . "
                ORDER BY ms.id ASC
            ", 'ssss', [$date, $date, $nic, $date]);

            if (empty($rows) && $pid > 0) {
                $rows = Database::fetchAll("\n                    SELECT
                        ms.id,
                        COALESCE(m.name, 'Medication') AS medicine_name,
                        COALESCE(dc.label, '-') AS dosage,
                        COALESCE(f.label, '-') AS frequency,
                        COALESCE(mt.label, '-') AS meal_timing,
                        COALESCE(ms.instructions, '') AS instructions,
                        ? AS schedule_date,
                        COALESCE(UPPER(ml.status), 'PENDING') AS status
                    FROM medication_schedule ms
                    JOIN schedule_master sm ON sm.id = ms.schedule_master_id
                    LEFT JOIN medicines m ON ms.medicine_id = m.id
                    LEFT JOIN dosage_categories dc ON ms.dosage_id = dc.id
                    LEFT JOIN frequencies f ON ms.frequency_id = f.id
                    LEFT JOIN meal_timing mt ON ms.meal_timing_id = mt.id
                    LEFT JOIN medication_log ml
                        ON ml.medication_schedule_id = ms.id
                        AND ml.patient_nic = sm.patient_nic
                        AND ml.dose_date = ?
                    WHERE sm.patient_nic = ?
                      AND ? BETWEEN ms.start_date
                                      AND DATE_ADD(ms.start_date, INTERVAL GREATEST(COALESCE(ms.duration_days, 1), 1) - 1 DAY)
                    ORDER BY ms.id ASC
                ", 'ssss', [$date, $date, $nic, $date]);
            }
        }

        return $rows;
    }

    /**
     * Mark a medication schedule as TAKEN or MISSED.
     * Ported from: MarkMedicationStatusServlet.java
     */
    public static function markStatus(int $scheduleId, string $nic, string $status, ?string $timeSlot = null): bool
    {
        $status = strtoupper($status);
        if (!in_array($status, ['TAKEN', 'MISSED'], true))
            return false;

        $today = date('Y-m-d');
        $slot = trim((string) ($timeSlot ?? ''));
        if ($slot === '') {
            $slot = 'General';
        }
        $updatedAny = false;

        // Java flow: upsert into medication_log for legacy schedule table.
        if (self::tableExists('medication_log') && self::tableExists('medication_schedule') && self::tableExists('schedule_master')) {
            $checkRow = Database::fetchOne("
                SELECT ml.id
                FROM medication_log ml
                JOIN medication_schedule ms ON ms.id = ml.medication_schedule_id
                JOIN schedule_master sm ON sm.id = ms.schedule_master_id
                WHERE ml.medication_schedule_id = ?
                  AND ml.patient_nic = ?
                  AND ml.dose_date = ?
                  AND ml.time_slot = ?
                  AND " . self::pharmacyCondition('ml', 'medication_log') . "
                LIMIT 1
            ", 'isss', [$scheduleId, $nic, $today, $slot]);

            if ($checkRow) {
                $logId = (int) ($checkRow['id'] ?? 0);
                if ($logId > 0) {
                    $ok = Database::execute("UPDATE medication_log SET status = ?, updated_at = NOW() WHERE id = ?", 'si', [$status, $logId]);
                    $updatedAny = $updatedAny || $ok;
                }
            } else {
                if (PharmacyContext::tableHasPharmacyId('medication_log') && self::currentPharmacyId() > 0) {
                    $ok = Database::execute(
                        "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at, pharmacy_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)",
                        'issssi',
                        [$scheduleId, $nic, $today, $status, $slot, self::currentPharmacyId()]
                    );
                } else {
                    $ok = Database::execute(
                        "INSERT INTO medication_log (medication_schedule_id, patient_nic, dose_date, status, time_slot, updated_at) VALUES (?, ?, ?, ?, ?, NOW())",
                        'issss',
                        [$scheduleId, $nic, $today, $status, $slot]
                    );
                }
                $updatedAny = $updatedAny || $ok;
            }
        }

        // Expanded-table flow: direct status update.
        if (self::tableExists('medication_schedules')) {
            $ok = Database::execute("
                UPDATE medication_schedules
                SET status = ?
                WHERE id = ? AND patient_nic = ?
                  AND " . self::pharmacyCondition('medication_schedules', 'medication_schedules') . "
            ", 'sis', [$status, $scheduleId, $nic]);
            $updatedAny = $updatedAny || $ok;
        }

        return $updatedAny;
    }
}
