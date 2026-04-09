<?php
require 'config/env.php';
require 'config/database.php';
require 'pages/admin/pharmacists/pharmacists.model.php';
Database::setUpConnection();
$email = 'codex_test_' . time() . '@example.com';
$ok = PharmacistsModel::create([
    'name' => 'Codex Test Pharmacist',
    'email' => $email,
    'phone' => '0712345678',
    'license_no' => 'PH-TEMP-001',
    'password' => 'TempPass123'
]);
echo $ok ? "CREATE_OK\n" : ("CREATE_FAIL: " . Database::$connection->error . "\n");
Database::iud("DELETE FROM pharmacist WHERE email = '" . Database::escape($email) . "'");
Database::iud("DELETE FROM pharmacists WHERE email = '" . Database::escape($email) . "'");
