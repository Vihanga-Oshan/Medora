<?php
/**
 * Admin Dashboard Layout (Medora)
 */
$s = $data['summary'];
$base = APP_BASE ?: '';
$recentLogs = $data['recentLogs'] ?? [];

if (empty($recentLogs)) {
    $recentLogs = [
        ['name' => 'Dr. Sarah Johnson', 'action' => 'Created new pharmacist account', 'time' => '2 minutes ago', 'tone' => 'green'],
        ['name' => 'John Doe', 'action' => 'Updated patient profile', 'time' => '15 minutes ago', 'tone' => 'blue'],
        ['name' => 'Admin User', 'action' => 'Deleted inactive pharmacist', 'time' => '1 hour ago', 'tone' => 'red'],
        ['name' => 'Jane Smith', 'action' => 'Added new guardian link', 'time' => '2 hours ago', 'tone' => 'green'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/dashboard.css">
</head>
<body class="admin-body">

<aside class="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
        <span>Medora Admin</span>
    </div>
    <ul class="nav-links">
        <li class="active"><a href="<?= htmlspecialchars($base) ?>/admin/dashboard"><i>&#128202;</i> Dashboard</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacists"><i>&#9877;</i> Pharmacists</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacies"><i>&#127973;</i> Pharmacies</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacy-assignments"><i>&#128279;</i> Assignments</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests"><i>&#128221;</i> Requests</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/settings"><i>&#9881;</i> Settings</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/logout"><i>&#128682;</i> Logout</a></li>
    </ul>
    <div class="admin-profile">
        <div class="profile-icon">AD</div>
        <div class="profile-info">
            <div class="name"><?= htmlspecialchars($data['adminName']) ?></div>
            <div class="email">admin@medora.com</div>
        </div>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <span>&#128269;</span>
            <input type="text" placeholder="Search users, pharmacists..." />
        </div>
        <div class="top-icons">
            <i title="Notifications">&#128276;</i>
        </div>
    </header>

    <section class="dashboard">
        <h1>Dashboard</h1>
        <p class="subtitle">Welcome back! Here's what's happening today.</p>

        <div class="stats-grid">
            <div class="card">
                <div class="card-icon blue"><i>&#128101;</i></div>
                <div>
                    <h2><?= (int)$s['totalPatients'] + (int)$s['totalGuardians'] ?></h2>
                    <p>Total Active Users</p>
                    <span class="trend">+12.5% from last month</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon green"><i>&#128104;&#8205;&#9877;&#65039;</i></div>
                <div>
                    <h2><?= $s['activePharmacists'] ?></h2>
                    <p>Active Pharmacists</p>
                    <span class="trend">+4.2% from last month</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon purple"><i>&#128200;</i></div>
                <div>
                    <h2><?= $s['totalPatients'] ?></h2>
                    <p>Patients Today</p>
                    <span class="trend">+8.1% from last month</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon pink"><i>&#128105;</i></div>
                <div>
                    <h2><?= $s['totalGuardians'] ?></h2>
                    <p>Active Guardians</p>
                    <span class="trend">+6.3% from last month</span>
                </div>
            </div>
        </div>
    </section>

    <section class="recent-activity">
        <div class="activity-card">
            <div class="activity-header">
                <span class="activity-icon">&#128200;</span>
                <div>
                    <h2>Recent Activity</h2>
                    <p>Latest system actions and events</p>
                </div>
            </div>

            <ul class="activity-list">
                <?php foreach ($recentLogs as $log): ?>
                    <?php
                    $tone = $log['tone'] ?? 'blue';
                    $name = $log['name'] ?? 'System';
                    $action = $log['action'] ?? 'Updated record';
                    $time = $log['time'] ?? 'just now';
                    ?>
                    <li>
                        <div class="activity-left">
                            <span class="activity-badge <?= htmlspecialchars($tone) ?>">
                                <?= $tone === 'red' ? '&#10007;' : '&#10003;' ?>
                            </span>
                            <div>
                                <strong><?= htmlspecialchars($name) ?></strong>
                                <p><?= htmlspecialchars($action) ?></p>
                            </div>
                        </div>
                        <span class="time"><?= htmlspecialchars($time) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>

</body>
</html>

