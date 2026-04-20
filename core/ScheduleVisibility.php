<?php

class ScheduleVisibility
{
    private static array $columnCache = [];

    public static function ensureSchema(): void
    {
        if (!self::hasColumn('medication_schedule', 'deleted_at')) {
            Database::execute(
                "ALTER TABLE medication_schedule ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER created_at"
            );
            self::$columnCache['medication_schedule.deleted_at'] = true;
        }
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (array_key_exists($key, self::$columnCache)) {
            return self::$columnCache[$key];
        }

        $row = Database::fetchOne(
            "SELECT 1 AS present
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = ?
             LIMIT 1",
            'ss',
            [$table, $column]
        );

        self::$columnCache[$key] = $row !== null;
        return self::$columnCache[$key];
    }

    public static function activeCondition(string $alias = 'ms'): string
    {
        self::ensureSchema();
        return self::hasColumn('medication_schedule', 'deleted_at')
            ? "$alias.deleted_at IS NULL"
            : '1=1';
    }
}
