<?php
/**
 * Guardian Alerts Model
 */
class AlertsModel
{
    private const PATIENT_TABLE = 'patient';

    public static function getNotificationsByGuardian(string $guardianNic): array
    {
        return Database::fetchAll("
            SELECT n.*, p.name AS patient_name, '' AS patient_phone
            FROM notifications n
            JOIN `" . self::PATIENT_TABLE . "` p ON n.patient_nic = p.nic
            WHERE p.guardian_nic = ?
            ORDER BY n.created_at DESC
        ", 's', [$guardianNic]);
    }

    public static function markAsRead(int $id): bool
    {
        return Database::execute("UPDATE notifications SET is_read = 1 WHERE id = ?", 'i', [$id]);
    }

    public static function markAllRead(string $guardianNic): bool
    {
        return Database::execute("
            UPDATE notifications n
            JOIN `" . self::PATIENT_TABLE . "` p ON n.patient_nic = p.nic
            SET n.is_read = 1
            WHERE p.guardian_nic = ? AND n.is_read = 0
        ", 's', [$guardianNic]);
    }
}
