<?php

/**
 * Database Configuration — MySQLi singleton
 * Provides iud() for INSERT/UPDATE/DELETE and search() for SELECT queries.
 */
class Database
{
    public static $connection;

    private static function connect(string $host, string $user, string $pass, string $name, int $port, bool $useSsl, string $sslKey = '', string $sslCert = '', string $sslCa = ''): ?mysqli
    {
        if ($useSsl) {
            $connection = mysqli_init();
            if ($connection === false) {
                return null;
            }

            $connection->ssl_set(
                $sslKey !== '' ? $sslKey : null,
                $sslCert !== '' ? $sslCert : null,
                $sslCa !== '' ? $sslCa : null,
                null,
                null
            );

            $ok = @$connection->real_connect($host, $user, $pass, $name, $port, null, MYSQLI_CLIENT_SSL);
            if ($ok === false) {
                return null;
            }

            return $connection;
        }

        $connection = @new mysqli($host, $user, $pass, $name, $port);
        if ($connection->connect_error) {
            return null;
        }

        return $connection;
    }

    private static function isRetryableConnectionError(int $errno, string $error = ''): bool
    {
        if (in_array($errno, [2002, 2006, 2013, 2055], true)) {
            return true;
        }

        $msg = strtolower($error);
        return str_contains($msg, 'server has gone away')
            || str_contains($msg, 'lost connection')
            || str_contains($msg, 'forcibly closed');
    }

    private static function reconnect(): bool
    {
        if (self::$connection instanceof mysqli) {
            @self::$connection->close();
        }

        self::$connection = null;
        self::setUpConnection();
        return self::$connection instanceof mysqli && !self::$connection->connect_error;
    }

    private static function queryWithReconnect(string $q)
    {
        self::setUpConnection();

        $result = @self::$connection->query($q);
        if ($result !== false) {
            return $result;
        }

        $errno = (int)(self::$connection->errno ?? 0);
        $error = (string)(self::$connection->error ?? '');
        if (!self::isRetryableConnectionError($errno, $error)) {
            return false;
        }

        if (!self::reconnect()) {
            return false;
        }

        return @self::$connection->query($q);
    }

    public static function setUpConnection()
    {
        if (!isset(self::$connection)) {
            $host = env('DB_HOST', 'localhost');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');
            $name = env('DB_NAME', 'medoradb');
            $port = (int) env('DB_PORT', '3306');
            $sslMode = strtolower(env('DB_SSL_MODE', ''));
            $sslCa = env('DB_SSL_CA', '');
            $sslCert = env('DB_SSL_CERT', '');
            $sslKey = env('DB_SSL_KEY', '');

            $useSsl = in_array($sslMode, ['required', 'verify_ca', 'verify_identity'], true);
            self::$connection = self::connect($host, $user, $pass, $name, $port, $useSsl, $sslKey, $sslCert, $sslCa);

            // Local dev fallback: if cloud host/DNS is unavailable, try local MySQL.
            if (!self::$connection && !in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)) {
                $fallbackHost = env('DB_FALLBACK_HOST', '127.0.0.1');
                $fallbackPort = (int)env('DB_FALLBACK_PORT', '3306');
                $fallbackUser = env('DB_FALLBACK_USER', 'root');
                $fallbackPass = env('DB_FALLBACK_PASS', '');
                $fallbackName = env('DB_FALLBACK_NAME', $name);

                self::$connection = self::connect(
                    $fallbackHost,
                    $fallbackUser,
                    $fallbackPass,
                    $fallbackName,
                    $fallbackPort,
                    false
                );
            }

            if (!self::$connection || self::$connection->connect_error) {
                $err = mysqli_connect_error();
                die("Database connection failed: " . ($err !== '' ? $err : 'Unknown connection error.'));
            }

            self::$connection->set_charset('utf8mb4');
        }
    }

    /**
     * Execute INSERT, UPDATE, DELETE queries
     */
    public static function iud($q)
    {
        $rs = self::queryWithReconnect($q);
        return (bool)$rs;
    }

    /**
     * Execute SELECT queries — returns mysqli_result
     */
    public static function search($q)
    {
        return self::queryWithReconnect($q);
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
