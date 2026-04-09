<?php
require 'config/env.php';
require 'config/database.php';
Database::setUpConnection();
$rs = Database::search("SHOW COLUMNS FROM pharmacist");
while ($row = $rs->fetch_assoc()) {
    echo $row['Field'] . PHP_EOL;
}
