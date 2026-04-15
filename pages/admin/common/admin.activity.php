<?php
/**
 * Admin Activity Log helper
 */
class AdminActivityLog
{
    private static function ensureTable(): void
    {
        Database::setUpConnection();
        Database::iud("
            CREATE TABLE IF NOT EXISTS admin_activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NULL,
                actor_name VARCHAR(120) NOT NULL,
                action_text VARCHAR(255) NOT NULL,
                tone ENUM('green','blue','red','purple') NOT NULL DEFAULT 'blue',
                entity_type VARCHAR(60) NULL,
                entity_id INT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_created_at (created_at),
                INDEX idx_entity (entity_type, entity_id)
            )
        ");
    }

    public static function log(array $user, string $actionText, string $tone = 'blue', ?string $actorName = null, ?string $entityType = null, ?int $entityId = null): bool
    {
        self::ensureTable();

        $adminId = (int) ($user['id'] ?? 0);
        $name = trim((string) ($actorName ?? ($user['name'] ?? 'Admin')));
        if ($name === '') {
            $name = 'Admin';
        }

        $action = trim($actionText);
        if ($action === '') {
            return false;
        }

        $allowedTones = ['green', 'blue', 'red', 'purple'];
        if (!in_array($tone, $allowedTones, true)) {
            $tone = 'blue';
        }

        $safeName = Database::escape($name);
        $safeAction = Database::escape($action);
        $safeTone = Database::escape($tone);
        $safeType = Database::escape((string) ($entityType ?? 'system'));
        $safeEntityId = $entityId !== null ? (int) $entityId : null;
        $adminIdValue = $adminId > 0 ? (string) $adminId : 'NULL';
        $entityIdValue = $safeEntityId !== null && $safeEntityId > 0 ? (string) $safeEntityId : 'NULL';

        return Database::iud("
            INSERT INTO admin_activity_log (admin_id, actor_name, action_text, tone, entity_type, entity_id, created_at)
            VALUES ($adminIdValue, '$safeName', '$safeAction', '$safeTone', '$safeType', $entityIdValue, NOW())
        ");
    }

    public static function getRecent(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $rows = [];
        $rs = Database::search("
            SELECT actor_name, action_text, tone, created_at
            FROM admin_activity_log
            ORDER BY created_at DESC, id DESC
            LIMIT $limit
        ");
        if ($rs instanceof mysqli_result) {
            while ($r = $rs->fetch_assoc()) {
                $rows[] = [
                    'name' => (string) ($r['actor_name'] ?? 'Admin'),
                    'action' => (string) ($r['action_text'] ?? ''),
                    'tone' => (string) ($r['tone'] ?? 'blue'),
                    'created_at' => (string) ($r['created_at'] ?? ''),
                ];
            }
        }
        return $rows;
    }
}

