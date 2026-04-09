<?php

$search = trim(Request::get('nic') ?? '');
$patientList = CounselorClientsModel::getAll($search);
