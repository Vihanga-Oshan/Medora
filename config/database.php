<?php

/**
 * Database Configuration — MySQLi singleton
 * Provides iud() for INSERT/UPDATE/DELETE and search() for SELECT queries.
 */
class Database
{
    public static $connection;

    public static function setUpConnection()
    {
        if (!isset(self::$connection)) {
            self::$connection = new mysqli(
                env('DB_HOST', 'localhost'),
                env('DB_USER', 'root'),
                env('DB_PASS', ''),
                env('DB_NAME', 'medoradb'),
                env('DB_PORT', '3306')
            );

            if (self::$connection->connect_error) {
                die("Database connection failed: " . self::$connection->connect_error);
            }

            self::$connection->set_charset('utf8mb4');
        }
    }

    /**
     * Execute INSERT, UPDATE, DELETE queries
     */
    public static function iud($q)
    {
        self::setUpConnection();
        return (bool) self::$connection->query($q);
    }

    /**
     * Execute SELECT queries — returns mysqli_result
     */
    public static function search($q)
    {
        self::setUpConnection();
        $rs = self::$connection->query($q);
        return $rs;
    }

    private static function normalizeTypes(string $types, array $params): string
    {
        if ($types !== '') {
            return $types;
        }
        if (empty($params)) {
            return '';
        }
        return str_repeat('s', count($params));
    }

    private static function bindParams(mysqli_stmt $stmt, string $types, array $params): bool
    {
        if ($types === '' || empty($params)) {
            return true;
        }

        $refs = [];
        foreach ($params as $k => $v) {
            $refs[$k] = &$params[$k];
        }
        array_unshift($refs, $types);
        return (bool)call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    /**
     * Execute prepared INSERT/UPDATE/DELETE.
     */
    public static function execute(string $sql, string $types = '', array $params = []): bool
    {
        self::setUpConnection();
        $stmt = self::$connection->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $types = self::normalizeTypes($types, $params);
        if (!self::bindParams($stmt, $types, $params)) {
            $stmt->close();
            return false;
        }

        $ok = $stmt->execute();
        $stmt->close();
        return (bool)$ok;
    }

    /**
     * Execute prepared SELECT and return first row.
     */
    public static function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        $rows = self::fetchAll($sql, $types, $params);
        return $rows[0] ?? null;
    }

    /**
     * Execute prepared SELECT and return all rows.
     */
    public static function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        self::setUpConnection();
        $stmt = self::$connection->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $types = self::normalizeTypes($types, $params);
        if (!self::bindParams($stmt, $types, $params)) {
            $stmt->close();
            return [];
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        if (!($result instanceof mysqli_result)) {
            $stmt->close();
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    public static function beginTransaction(): bool
    {
        self::setUpConnection();
        return (bool)self::$connection->begin_transaction();
    }

    public static function commit(): bool
    {
        self::setUpConnection();
        return (bool)self::$connection->commit();
    }

    public static function rollback(): bool
    {
        self::setUpConnection();
        return (bool)self::$connection->rollback();
    }

    /**
     * Escape a value safely for SQL usage.
     */
    public static function escape(string $value): string
    {
        self::setUpConnection();
        return self::$connection->real_escape_string($value);
    }
}
