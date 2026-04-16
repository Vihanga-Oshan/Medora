<?php

/**
 * Response — simple output helpers
 */
class Response
{

    /**
     * Redirect to a URL and stop execution.
     * Automatically prepends APP_BASE for relative paths.
     */
    public static function redirect(string $url): never
    {
        // Auto-prepend base path for relative URLs (starting with /)
        // Skip if already has the base prefix or is an absolute URL
        $base = defined('APP_BASE') ? APP_BASE : '';
        if ($base && str_starts_with($url, '/') && !str_starts_with($url, $base . '/')) {
            $url = $base . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Set an HTTP status code.
     */
    public static function status(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Emit a JSON response and stop execution.
     */
    public static function json(array $payload, int $status = 200, bool $noCache = true): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        if ($noCache) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }
        echo json_encode($payload);
        exit;
    }

    /**
     * Send an empty response with the given status and stop execution.
     */
    public static function empty(int $status = 204): never
    {
        http_response_code($status);
        exit;
    }

    /**
     * Abort with an HTTP status and a plain message.
     */
    public static function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        echo htmlspecialchars($message);
        exit;
    }
}
