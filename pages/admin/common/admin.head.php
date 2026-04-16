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
    $row = Database::fetchOne("SELECT email AS admin_email, name AS admin_name FROM admins WHERE id = ? LIMIT 1", 'i', [$adminId]);
    if ($row) {
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
    }
}

if ($adminEmail === '') {
    $adminEmail = 'admin@medora.com';
}
