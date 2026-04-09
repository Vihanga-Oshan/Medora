<?php
/**
 * Legacy public front controller.
 * Canonical URLs now run from project root; keep this file only to redirect old links.
 */

$uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
$path = (string)(parse_url($uri, PHP_URL_PATH) ?? '/');
$query = (string)(parse_url($uri, PHP_URL_QUERY) ?? '');

$cleanPath = preg_replace('#/public(?:/|$)#', '/', $path, 1);
if ($cleanPath === null || $cleanPath === '') {
    $cleanPath = '/';
}
$target = $cleanPath . ($query !== '' ? ('?' . $query) : '');

header('Location: ' . $target, true, 302);
exit;