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
        $nic = Database::escape($nic);
        $rs  = Database::search("
            SELECT id, message, is_read, created_at
            FROM notifications
            WHERE patient_nic = '$nic'
              AND " . self::pharmacyWhere('notifications') . "
            ORDER BY created_at DESC
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
