<?php

/**
 * CSRF token helper.
 * Plain PHP session-based token storage with per-form scopes.
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_tokens';

    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function token(string $scope = 'default'): string
    {
        self::ensureSession();
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        if (empty($_SESSION[self::SESSION_KEY][$scope])) {
            $_SESSION[self::SESSION_KEY][$scope] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION[self::SESSION_KEY][$scope];
    }

    public static function verify(?string $token, string $scope = 'default'): bool
    {
        self::ensureSession();
        if (!is_string($token) || $token === '') {
            return false;
        }

        $expected = (string)($_SESSION[self::SESSION_KEY][$scope] ?? '');
        if ($expected === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }
}

