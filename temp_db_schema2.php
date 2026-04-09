<?php
require 'config/env.php';
require 'config/database.php';
Database::setUpConnection();
$rs = Database::search('SHOW CREATE TABLE pharmacist');
$row = $rs->fetch_assoc();
echo $row['Create Table'] . PHP_EOL;
