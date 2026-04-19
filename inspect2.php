<?php
require "config/env.php";
require "config/database.php";
Database::setUpConnection();

echo "--- 1) Total Counts ---\n";
$p_res = Database::fetchOne("SELECT COUNT(*) as count FROM prescriptions");
$o_res = Database::fetchOne("SELECT COUNT(*) as count FROM pharmacy_orders");
echo "Prescriptions: " . ($p_res["count"] ?? 0) . "\n";
echo "Pharmacy Orders: " . ($o_res["count"] ?? 0) . "\n\n";

echo "--- 2) Last 10 Prescriptions ---\n";
foreach (Database::fetchAll("SELECT id, patient_nic, pharmacy_id, status, wants_medicine_order, upload_date FROM prescriptions ORDER BY id DESC LIMIT 10") as $p) {
    echo implode(" | ", $p) . "\n";
}
echo "\n";

echo "--- 3) Last 10 Pharmacy Orders ---\n";
foreach (Database::fetchAll("SELECT id, prescription_id, patient_nic, pharmacy_id, source, status, created_at FROM pharmacy_orders ORDER BY id DESC LIMIT 10") as $o) {
    echo implode(" | ", $o) . "\n";
}
echo "\n";

echo "--- 4) Grouped Orders Count ---\n";
foreach (Database::fetchAll("SELECT pharmacy_id, COUNT(*) as count FROM pharmacy_orders GROUP BY pharmacy_id") as $g) {
    echo "Pharmacy ID " . ($g["pharmacy_id"] ?? "NULL") . ": " . $g["count"] . "\n";
}
echo "\n";

echo "--- 5) Last 10 Patient Pharmacy Selection ---\n";
try {
    foreach (Database::fetchAll("SELECT * FROM patient_pharmacy_selection ORDER BY id DESC LIMIT 10") as $s) {
        echo implode(" | ", $s) . "\n";
    }
} catch (Exception $e) { echo "Error: " . $e->getMessage(); }

