<?php

/**
 * Admin head guard — include this at the top of every admin page index.php.
 *
 * What it does:
 *   1. Verifies the JWT cookie exists and is valid
 *   2. Confirms the role is 'admin'
 *   3. Sets $user = ['id', 'name', 'role', 'iat', 'exp']
 *   4. Redirects to /admin/login on any failure
 *
 * After this file is required, $user is available everywhere on the page.
 */
$user = Auth::requireRole('admin');

/**
 * Resolve current admin profile details for sidebar/profile usage.
 */
$adminDisplayName = trim((string)($user['name'] ?? 'Admin User'));
$adminEmail = trim((string)($user['email'] ?? ''));

$adminId = (int)($user['id'] ?? 0);
if ($adminId > 0) {
    $candidateTables = ['admins', 'admin', 'users'];
    foreach ($candidateTables as $table) {
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        if ($safeTable === '') {
            continue;
        }

        $exists = Database::search("SHOW TABLES LIKE '" . Database::escape($safeTable) . "'");
        if (!($exists instanceof mysqli_result) || $exists->num_rows === 0) {
            continue;
        }

        $cols = [];
        $colRs = Database::search("SHOW COLUMNS FROM `$safeTable`");
        if ($colRs instanceof mysqli_result) {
            while ($c = $colRs->fetch_assoc()) {
                $cols[] = (string)($c['Field'] ?? '');
            }
        }

        $idCol = in_array('user_id', $cols, true) ? 'user_id' : (in_array('admin_id', $cols, true) ? 'admin_id' : (in_array('id', $cols, true) ? 'id' : null));
        $emailCol = in_array('email', $cols, true) ? 'email' : (in_array('admin_email', $cols, true) ? 'admin_email' : null);
        $nameCol = in_array('name', $cols, true) ? 'name' : (in_array('full_name', $cols, true) ? 'full_name' : (in_array('display_name', $cols, true) ? 'display_name' : null));
        if ($idCol === null || $emailCol === null) {
            continue;
        }

        $where = "`$idCol` = $adminId";
        if ($safeTable === 'users' && in_array('role', $cols, true)) {
            $where .= " AND `role` = 'admin'";
        }

        $nameSelect = $nameCol !== null ? "`$nameCol` AS admin_name" : "'$adminDisplayName' AS admin_name";
        $rowRs = Database::search("SELECT `$emailCol` AS admin_email, $nameSelect FROM `$safeTable` WHERE $where LIMIT 1");
        if ($rowRs instanceof mysqli_result && $rowRs->num_rows > 0) {
            $row = $rowRs->fetch_assoc();
            $dbEmail = trim((string)($row['admin_email'] ?? ''));
            $dbName = trim((string)($row['admin_name'] ?? ''));
            if ($dbEmail !== '') {
                $adminEmail = $dbEmail;
                $user['email'] = $dbEmail;
            }
            if ($dbName !== '') {
                $adminDisplayName = $dbName;
                $user['name'] = $dbName;
            }
            break;
        }
    }
}

if ($adminEmail === '') {
    $adminEmail = 'admin@medora.com';
}
