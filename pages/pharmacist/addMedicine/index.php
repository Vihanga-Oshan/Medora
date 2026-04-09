<?php
/**
 * Java-compatibility route alias: /pharmacist/addMedicine
 */
require_once __DIR__ . '/../common/pharmacist.head.php';

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = '/pharmacist/inventory/add' . ($query !== '' ? ('?' . $query) : '');
Response::redirect($target);
