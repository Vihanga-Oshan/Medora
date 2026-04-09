<?php
require 'config/env.php';
require 'config/database.php';
Database::setUpConnection();
$rs = Database::search('SHOW CREATE TABLE pharmacists');
if (!$rs) {
    echo "ERR: " . Database::$connection->error . PHP_EOL;
    exit(1);
}
$row = $rs->fetch_assoc();
echo $row['Create Table'] . PHP_EOL;
