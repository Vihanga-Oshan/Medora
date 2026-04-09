<?php
define('APP_BASE','');
$data = [
  'metrics' => ['pendingCount'=>0,'approvedCount'=>0,'newPatientCount'=>0],
  'patientsNeedingCheck' => [],
  'patientsNeedingSchedule' => [],
  'greeting' => 'Hi',
  'currentDate' => 'x',
  'currentTime' => 'y',
];
$user = ['name'=>'Test'];
include __DIR__ . '/pages/pharmacist/dashboard/dashboard.layout.php';