<?php

require_once __DIR__ . '/../common/pharmacist.head.php';

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = '/pharmacist/messages' . ($query !== '' ? ('?' . $query) : '');
Response::redirect($target);
