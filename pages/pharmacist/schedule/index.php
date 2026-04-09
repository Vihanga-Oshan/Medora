<?php
/**
 * Alias route for Java-compatibility.
 */
require_once __DIR__ . '/../common/pharmacist.head.php';

$base = APP_BASE ?: '';
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = $base . '/pharmacist/scheduling' . ($query !== '' ? ('?' . $query) : '');
header('Location: ' . $url);
exit;