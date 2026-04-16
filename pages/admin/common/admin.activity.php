<?php
/**
 * Admin Activity Log helper
 */
class AdminActivityLog
{
    private static function ensureTable(): void
    {
        // Fixed-schema application: admin activity table is managed outside runtime code.
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

        $entityType = (string) ($entityType ?? 'system');
        $entityId = $entityId !== null && $entityId > 0 ? $entityId : null;
        $adminId = $adminId > 0 ? $adminId : null;

        return Database::execute("
            INSERT INTO admin_activity_log (admin_id, actor_name, action_text, tone, entity_type, entity_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ", 'issssi', [$adminId, $name, $action, $tone, $entityType, $entityId]);
    }

    public static function getRecent(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $result = Database::fetchAll("
            SELECT actor_name, action_text, tone, created_at
            FROM admin_activity_log
            ORDER BY created_at DESC, id DESC
            LIMIT $limit
        ");
        $rows = [];
        foreach ($result as $r) {
            $rows[] = [
                'name' => (string) ($r['actor_name'] ?? 'Admin'),
                'action' => (string) ($r['action_text'] ?? ''),
                'tone' => (string) ($r['tone'] ?? 'blue'),
                'created_at' => (string) ($r['created_at'] ?? ''),
            ];
        }
        return $rows;
    }
}

