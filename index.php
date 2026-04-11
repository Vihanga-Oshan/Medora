<?php
/**
 * Front Controller — project root (clean URLs)
 */
define('ROOT', __DIR__);

// Root app base (e.g. /Medora)
$appBase = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
if ($appBase === '.' || $appBase === '/') {
    $appBase = '';
}

// Fallback for rewritten requests where SCRIPT_NAME may appear as /index.php
if ($appBase === '') {
    $uriPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    $firstSegment = trim((string)strtok(ltrim($uriPath, '/'), '/'));
    $rootName = basename(ROOT);
    if ($firstSegment !== '' && strcasecmp($firstSegment, $rootName) === 0) {
        $appBase = '/' . $firstSegment;
    }
}
define('APP_BASE', $appBase);

require_once ROOT . '/config/env.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/core/Auth.php';
require_once ROOT . '/core/Csrf.php';
require_once ROOT . '/core/PharmacyContext.php';
require_once ROOT . '/core/Response.php';
require_once ROOT . '/core/Request.php';

PharmacyContext::boot();

$path = Request::path();
$path = '/' . trim($path, '/');

if (str_contains($path, '..')) {
    Response::abort(400, 'Bad Request');
}

if ($path === '/landing') {
    // Canonicalize old landing URL to root.
    Response::redirect('/');
}

if ($path === '/') {
    // Serve landing content at root URL.
    $path = '/landing';
}

$pagePath = ROOT . '/pages' . $path . '/index.php';
if (file_exists($pagePath)) {
    require_once $pagePath;
    exit;
}

http_response_code(404);
require_once ROOT . '/pages/404.php';
