<?php
require_once __DIR__ . '/../../common/patient.head.php';

if (!Request::isPost()) {
    Response::redirect('/patient/prescriptions');
    return;
}

require_once __DIR__ . '/../prescriptions.controller.php';
require_once __DIR__ . '/../prescriptions.layout.php';
