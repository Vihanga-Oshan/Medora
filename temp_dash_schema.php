<?php
require 'config/env.php';
require 'config/database.php';
Database::setUpConnection();
$tables = ['patient','patients','prescriptions'];
foreach ($tables as $t) {
  $rs = Database::search("SHOW TABLES LIKE '$t'");
  echo "TABLE $t: " . (($rs instanceof mysqli_result && $rs->num_rows>0) ? 'YES' : 'NO') . PHP_EOL;
  if ($rs instanceof mysqli_result && $rs->num_rows>0) {
    $crs = Database::search("SHOW COLUMNS FROM `$t`");
    echo "COLUMNS $t: ";
    $cols=[];
    while($row=$crs->fetch_assoc()){ $cols[]=$row['Field']; }
    echo implode(',', $cols) . PHP_EOL;
  }
}
