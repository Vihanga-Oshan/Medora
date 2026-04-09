<?php
require 'config/env.php';
require 'config/database.php';
Database::setUpConnection();
$rs = Database::search('SHOW TABLES');
while($row = $rs->fetch_row()) { echo $row[0] . PHP_EOL; }
