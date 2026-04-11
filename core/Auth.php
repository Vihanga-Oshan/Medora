<?php

/**
 * Auth — Pure PHP JWT (HS256) implementation.
 * No third-party libraries needed.
 *
 * Usage:
 *   $token = Auth::sign(['id' => 1, 'role' => 'admin']);
 *   $user  = Auth::requireRole('admin');   // call at top of any protected page
 */
class Auth
{
    private const ROLE_COOKIE_PREFIX = 'jwt_';
    private const LEGACY_COOKIE_NAME = 'jwt';

    private static function cookieNameForRole(string $role): string
    {
        return self::ROLE_COOKIE_PREFIX . strtolower($role);
    }

    private static function knownCookieNames(): array
    {
        return [
            self::LEGACY_COOKIE_NAME,
            self::cookieNameForRole('patient'),
            self::cookieNameForRole('pharmacist'),
            self::cookieNameForRole('guardian'),
            self::cookieNameForRole('admin'),
            self::cookieNameForRole('user'),
        ];
    }

    private static function secret(): string
    {
        // JWT_SECRET must be set in .env — at least 32 chars
        $secret = env('JWT_SECRET', '');
        if ($secret === '') {
            throw new RuntimeException('JWT_SECRET is not set in .env');
        }
        return $secret;
    }

    // -------------------------------------------------------------------------
    // Sign — create a JWT token
    // -------------------------------------------------------------------------
    public static function sign(array $payload, int $ttlSeconds = 86400): string
    {
        $header = self::base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $payload['iat'] = time();
        $payload['exp'] = time() + $ttlSeconds;

        $body      = self::base64url(json_encode($payload));
        $signature = self::base64url(hash_hmac('sha256', "$header.$body", self::secret(), true));

        return "$header.$body.$signature";
    }

    // -------------------------------------------------------------------------
    // Decode — verify signature and expiry, return payload or null
    // -------------------------------------------------------------------------
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $sig] = $parts;

        // Verify signature
        $expected = self::base64url(hash_hmac('sha256', "$header.$body", self::secret(), true));
        if (!hash_equals($expected, $sig)) return null;

        $payload = json_decode(self::base64urlDecode($body), true);
        if (!is_array($payload)) return null;

        // Check expiry
        if (isset($payload['exp']) && $payload['exp'] < time()) return null;

        return $payload;
    }

    // -------------------------------------------------------------------------
    // requireRole — call at the top of every protected page via head.php
    //   - Reads role-scoped cookie first (e.g. jwt_patient), then legacy 'jwt'
    //   - Verifies signature + expiry + role
    //   - Redirects to login on failure
    //   - Returns the decoded payload ($user) on success
    // -------------------------------------------------------------------------
    public static function requireRole(string $role): array
    {
        $cookieCandidates = [
            self::cookieNameForRole($role),
            self::LEGACY_COOKIE_NAME, // backward compatibility
        ];

        foreach ($cookieCandidates as $cookieName) {
            $token = $_COOKIE[$cookieName] ?? null;
            if ($token === null) {
                continue;
            }

            $payload = self::decode($token);
            if ($payload !== null && ($payload['role'] ?? '') === $role) {
                return $payload;
            }
        }
        self::denyAccess($role);
    }

    // -------------------------------------------------------------------------
    // setTokenCookie — write JWT to HttpOnly cookie after login
    // -------------------------------------------------------------------------
    public static function setTokenCookie(string $token, int $ttlSeconds = 86400, ?string $role = null): void
    {
        if ($role === null) {
            $decoded = self::decode($token);
            $role = is_array($decoded) ? (string)($decoded['role'] ?? '') : '';
            if ($role === '') {
                $role = null;
            }
        }

        if ($role !== null) {
            setcookie(self::cookieNameForRole($role), $token, [
                'expires'  => time() + $ttlSeconds,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
                'secure'   => self::isHttps(),
            ]);
        }

        // keep legacy cookie for existing flows that still expect `jwt`
        setcookie(self::LEGACY_COOKIE_NAME, $token, [
            'expires'  => time() + $ttlSeconds,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => self::isHttps(),   // true on HTTPS, false on local HTTP
        ]);
    }

    // -------------------------------------------------------------------------
    // clearTokenCookie — delete JWT cookie on logout
    // -------------------------------------------------------------------------
    public static function clearTokenCookie(?string $role = null): void
    {
        $cookieNames = $role
            ? [self::cookieNameForRole($role), self::LEGACY_COOKIE_NAME]
            : self::knownCookieNames();

        foreach ($cookieNames as $cookieName) {
            setcookie($cookieName, '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // getUser — decode the current JWT without enforcing a specific role.
    //   Returns null if no valid token is present.
    // -------------------------------------------------------------------------
    public static function getUser(): ?array
    {
        $legacy = $_COOKIE[self::LEGACY_COOKIE_NAME] ?? null;
        if ($legacy !== null) {
            $payload = self::decode($legacy);
            if ($payload !== null) return $payload;
        }

        foreach (self::knownCookieNames() as $cookieName) {
            if ($cookieName === self::LEGACY_COOKIE_NAME) {
                continue;
            }
            $token = $_COOKIE[$cookieName] ?? null;
            if ($token === null) {
                continue;
            }
            $payload = self::decode($token);
            if ($payload !== null) return $payload;
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------
    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function denyAccess(string $role = ''): never
    {
        $loginByRole = [
            'patient' => '/patient/login',
            'guardian' => '/guardian/login',
            'pharmacist' => '/pharmacist/login',
            'admin' => '/admin/login',
            'counselor' => '/pharmacist/login',
        ];

        Response::redirect($loginByRole[strtolower($role)] ?? '/patient/login');
    }

    private static function isHttps(): bool
    {
        $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
        if ($https === 'on' || $https === '1') {
            return true;
        }

        $forwarded = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($forwarded === 'https') {
            return true;
        }

        return false;
    }
}
