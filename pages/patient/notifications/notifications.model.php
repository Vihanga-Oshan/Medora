<?php
/**
 * Notifications Model
 * Ported from: NotificationDAO.java + NotificationServlet.java
 */
class NotificationsModel
{
    private static function pharmacyWhere(string $alias = 'notifications'): string
    {
        $pid = PharmacyContext::selectedPharmacyId();
        if ($pid <= 0 || !PharmacyContext::tableHasPharmacyId('notifications')) {
            return '1=1';
        }
        return PharmacyContext::sqlFilter($alias, $pid);
    }

    public static function getAll(string $nic): array
    {
        MedicationReminderService::deliverDueReminders($nic);

        $rs = Database::fetchAll("
            SELECT
                n.id,
                n.message,
                n.is_read,
                n.created_at,
                e.id AS reminder_event_id,
                e.status AS reminder_status,
                e.time_slot AS reminder_time_slot,
                e.dose_date AS reminder_dose_date
            FROM notifications n
            LEFT JOIN medication_reminder_events e
              ON e.delivered_notification_id = n.id
                        WHERE n.patient_nic = ?
              AND " . self::pharmacyWhere('n') . "
            ORDER BY n.created_at DESC
                ", 's', [$nic]);
        $rows = [];
        foreach ($rs as $row) {
            $row['formatted_date'] = date('M d, Y H:i', strtotime($row['created_at']));
            $rows[] = $row;
        }
        return $rows;
    }

    public static function getLatestId(string $nic): int
    {
        MedicationReminderService::deliverDueReminders($nic);

        $row = Database::fetchOne("
            SELECT MAX(n.id) AS latest_id
            FROM notifications n
            WHERE n.patient_nic = ?
              AND " . self::pharmacyWhere('n') . "
        ", 's', [$nic]);

        return (int) ($row['latest_id'] ?? 0);
    }

    public static function getAfterId(string $nic, int $afterId, int $limit = 10): array
    {
        MedicationReminderService::deliverDueReminders($nic);

        $afterId = max(0, $afterId);
        $limit = max(1, min(20, $limit));

        $rows = Database::fetchAll("
            SELECT
                n.id,
                n.message,
                n.type,
                n.is_read,
                n.created_at
            FROM notifications n
            WHERE n.patient_nic = ?
              AND n.id > ?
              AND " . self::pharmacyWhere('n') . "
            ORDER BY n.id ASC
            LIMIT " . (int) $limit . "
        ", 'si', [$nic, $afterId]);

        foreach ($rows as &$row) {
            $row['formatted_date'] = date('M d, Y H:i', strtotime((string) ($row['created_at'] ?? '')));
        }
        unset($row);

        return $rows;
    }

    public static function markTaken(int $notificationId, string $nic): bool
    {
        $notificationId = max(0, $notificationId);
        if ($notificationId <= 0) {
            return false;
        }

        $row = Database::fetchOne("
            SELECT e.id AS reminder_event_id
            FROM notifications n
            JOIN medication_reminder_events e
              ON e.delivered_notification_id = n.id
            WHERE n.id = ?
              AND n.patient_nic = ?
              AND " . self::pharmacyWhere('n') . "
            LIMIT 1
        ", 'is', [$notificationId, $nic]);

        $eventId = (int) ($row['reminder_event_id'] ?? 0);
        if ($eventId <= 0) {
            return false;
        }
        $ok = MedicationReminderService::markTakenFromEvent($eventId, $nic);
        if ($ok) {
            Database::execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND patient_nic = ?", 'is', [$notificationId, $nic]);
        }
        return $ok;
    }

    public static function delete(int $id, string $nic): void
    {
        Database::execute("DELETE FROM notifications WHERE id = ? AND patient_nic = ? AND " . self::pharmacyWhere('notifications'), 'is', [$id, $nic]);
    }

    public static function clearAll(string $nic): void
    {
        Database::execute("DELETE FROM notifications WHERE patient_nic = ? AND " . self::pharmacyWhere('notifications'), 's', [$nic]);
    }
}
