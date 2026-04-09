<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';
Database::setUpConnection();
$rs = Database::search("SHOW TABLES LIKE 'chat_messages'");
if (!($rs instanceof mysqli_result) || $rs->num_rows === 0) {
    echo "NO_CHAT_TABLE\n";
    exit;
}
$crs = Database::search("SHOW COLUMNS FROM chat_messages");
while ($row = $crs->fetch_assoc()) {
    echo $row['Field'] . "\t" . $row['Type'] . "\n";
}
?>
