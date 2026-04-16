<?php

class AppLogger
{
    public static function write(string $file, string $level, string $message, array $context = []): void
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__);
        $logDir = $rootDir . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $normalizedContext = [];
        foreach ($context as $key => $value) {
            $normalizedContext[$key] = is_scalar($value) || $value === null
                ? $value
                : json_encode($value, JSON_UNESCAPED_SLASHES);
        }

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($normalizedContext, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        @file_put_contents($logDir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }
}
