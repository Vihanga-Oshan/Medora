<?php
require 'config/env.php';
require 'config/database.php';
Database::setUpConnection();
$rs = Database::search("SHOW COLUMNS FROM pharmacist");
if (!($rs instanceof mysqli_result)) {
    echo "ERR: " . Database::$connection->error . PHP_EOL;
    exit(1);
}
while ($row = $rs->fetch_assoc()) {
    echo $row['Field'] . PHP_EOL;
}
