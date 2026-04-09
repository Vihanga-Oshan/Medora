<?php
/**
 * Guardian Alerts Model
 */
class AlertsModel
{
    public static function getNotificationsByGuardian(string $guardianNic): array
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        $rs = Database::search("
            SELECT n.*, p.name AS patient_name, p.phone AS patient_phone
            FROM notifications n
            JOIN patients p ON n.patient_nic = p.nic
            WHERE p.guardian_nic = '$guardianNic'
            ORDER BY n.created_at DESC
        ");
        $rows = [];
        while ($row = $rs->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public static function markAsRead(int $id): bool
    {
        return Database::iud("UPDATE notifications SET is_read = 1 WHERE id = $id");
    }

    public static function markAllRead(string $guardianNic): bool
    {
        Database::setUpConnection();
        $guardianNic = Database::$connection->real_escape_string($guardianNic);
        return Database::iud("
            UPDATE notifications n
            JOIN patients p ON n.patient_nic = p.nic
            SET n.is_read = 1
            WHERE p.guardian_nic = '$guardianNic' AND n.is_read = 0
        ");
    }
}
