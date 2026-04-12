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

        $nic = Database::escape($nic);
        $rs  = Database::search("
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
            WHERE n.patient_nic = '$nic'
              AND " . self::pharmacyWhere('n') . "
            ORDER BY n.created_at DESC
        ");
        if (!$rs) {
            return [];
        }
        $rows = [];
        while ($row = $rs->fetch_assoc()) {
            $row['formatted_date'] = date('M d, Y H:i', strtotime($row['created_at']));
            $rows[] = $row;
        }
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

        $eventId = (int)($row['reminder_event_id'] ?? 0);
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
        $nic = Database::escape($nic);
        Database::iud("DELETE FROM notifications WHERE id = $id AND patient_nic = '$nic' AND " . self::pharmacyWhere('notifications'));
    }

    public static function clearAll(string $nic): void
    {
        $nic = Database::escape($nic);
        Database::iud("DELETE FROM notifications WHERE patient_nic = '$nic' AND " . self::pharmacyWhere('notifications'));
    }
}
