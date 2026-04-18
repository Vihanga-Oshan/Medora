<?php

/**
 * Database Configuration - MySQLi singleton.
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

            if (!self::$connection && !in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)) {
                $fallbackHost = env('DB_FALLBACK_HOST', '127.0.0.1');
                $fallbackPort = (int) env('DB_FALLBACK_PORT', '3306');
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
                die('Database connection failed: ' . ($err !== '' ? $err : 'Unknown connection error.'));
            }

            self::$connection->set_charset('utf8mb4');

            $tz = new DateTimeZone(date_default_timezone_get());
            $offset = $tz->getOffset(new DateTime('now', $tz));
            $sign = $offset < 0 ? '-' : '+';
            $offset = abs($offset);
            $hours = str_pad((string) intdiv($offset, 3600), 2, '0', STR_PAD_LEFT);
            $minutes = str_pad((string) intdiv($offset % 3600, 60), 2, '0', STR_PAD_LEFT);
            $mysqlOffset = $sign . $hours . ':' . $minutes;

            $stmt = self::$connection->prepare("SET time_zone = ?");
            if ($stmt) {
                $stmt->bind_param('s', $mysqlOffset);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    private static function normalizeTypes(string $types, array $params): string
    {
        if ($types !== '') {
            return $types;
        }

        return empty($params) ? '' : str_repeat('s', count($params));
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
        return (bool) call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    private static function prepareStatement(string $sql, string $types, array $params): ?mysqli_stmt
    {
        self::setUpConnection();
        $stmt = self::$connection->prepare($sql);
        if (!$stmt) {
            return null;
        }

        if (!self::bindParams($stmt, self::normalizeTypes($types, $params), $params)) {
            $stmt->close();
            return null;
        }

        return $stmt;
    }

    public static function execute(string $sql, string $types = '', array $params = []): bool
    {
        $stmt = self::prepareStatement($sql, $types, $params);
        if (!$stmt) {
            return false;
        }

        $ok = $stmt->execute();
        $stmt->close();
        return (bool) $ok;
    }

    public static function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        $rows = self::fetchAll($sql, $types, $params);
        return $rows[0] ?? null;
    }

    public static function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        $stmt = self::prepareStatement($sql, $types, $params);
        if (!$stmt) {
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

}
