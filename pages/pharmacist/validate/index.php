<?php


$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    Response::redirect('/pharmacist/prescriptions/review?id=' . $id);
}

require_once __DIR__ . '/../common/pharmacist.head.php';
require_once __DIR__ . '/validate.model.php';
require_once __DIR__ . '/validate.controller.php';
require_once __DIR__ . '/validate.layout.php';
