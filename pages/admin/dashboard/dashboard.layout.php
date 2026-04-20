<?php
/**
 * Admin Dashboard Layout (Medora)
 */
$s = $data['summary'];
$base = APP_BASE ?: '';
$recentLogs = $data['recentLogs'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css?v=6">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/dashboard.css?v=2">
</head>
<body class="admin-body">

<aside class="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
        <span>Medora Admin</span>
    </div>
    <ul class="nav-links">
        <li class="active"><a href="<?= htmlspecialchars($base) ?>/admin/dashboard"><i></i> Dashboard</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacists"><i></i> Pharmacists</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacies"><i></i> Pharmacies</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacy-assignments"><i></i> Assignments</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests"><i></i> Requests</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/settings"><i></i> Settings</a></li>
    </ul>
        <div class="admin-profile js-admin-profile">
        <button type="button" class="admin-profile-trigger" aria-haspopup="true" aria-expanded="false">
            <div class="profile-icon">AD</div>
            <div class="profile-info">
                <div class="name"><?= htmlspecialchars($adminEmail ?? ($user['email'] ?? 'admin@medora.com')) ?></div>
            </div>
        </button>
        <div class="admin-profile-menu" role="menu" hidden>
            <div class="admin-profile-menu-email"><?= htmlspecialchars($adminEmail ?? 'admin@medora.com') ?></div>
            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/logout">
                <button type="submit" class="admin-profile-menu-logout">Logout</button>
            </form>
        </div>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <span></span>
            <input id="admin-global-search" type="text" placeholder="Search this page..." autocomplete="off" />
        </div>
    </header>

    <section class="dashboard">
        <h1>Dashboard</h1>
        <p class="subtitle">Welcome back! Here's what's happening today.</p>
        <p class="ux-hint"> Metrics and activity update automatically as system events are recorded.</p>

        <div class="stats-grid">
            <div class="card">
                <div class="card-icon user-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <circle cx="12" cy="9" r="3" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <path d="M6.5 17.5c1.3-2 3.2-3 5.5-3s4.2 1 5.5 3" fill="none" stroke="#000000" stroke-width="1.7" stroke-linecap="round"></path>
                    </svg>
                </div>
                <div>
                    <h2><?= (int)$s['totalPatients'] + (int)$s['totalGuardians'] ?></h2>
                    <p>Total Active Users</p>
                    <span class="trend">Patients + guardians</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon user-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <circle cx="12" cy="9" r="3" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <path d="M6.5 17.5c1.3-2 3.2-3 5.5-3s4.2 1 5.5 3" fill="none" stroke="#000000" stroke-width="1.7" stroke-linecap="round"></path>
                    </svg>
                </div>
                <div>
                    <h2><?= $s['activePharmacists'] ?></h2>
                    <p>Active Pharmacists</p>
                    <span class="trend">Currently active in system</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon user-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <circle cx="12" cy="9" r="3" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <path d="M6.5 17.5c1.3-2 3.2-3 5.5-3s4.2 1 5.5 3" fill="none" stroke="#000000" stroke-width="1.7" stroke-linecap="round"></path>
                    </svg>
                </div>
                <div>
                    <h2><?= (int)($s['patientsToday'] ?? 0) ?></h2>
                    <p>Patients Today</p>
                    <span class="trend">Registered today</span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon user-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <circle cx="12" cy="9" r="3" fill="none" stroke="#000000" stroke-width="1.7"></circle>
                        <path d="M6.5 17.5c1.3-2 3.2-3 5.5-3s4.2 1 5.5 3" fill="none" stroke="#000000" stroke-width="1.7" stroke-linecap="round"></path>
                    </svg>
                </div>
                <div>
                    <h2><?= $s['totalGuardians'] ?></h2>
                    <p>Total Guardians</p>
                    <span class="trend">Total guardian accounts</span>
                </div>
            </div>
        </div>
    </section>

    <section class="recent-activity">
        <div class="activity-card">
            <div class="activity-header">
                <span class="activity-icon"></span>
                <div>
                    <h2>Recent Activity</h2>
                    <p>Latest system actions and events</p>
                </div>
            </div>

            <ul class="activity-list">
                <?php if (empty($recentLogs)): ?>
                    <li>
                        <div class="activity-left">
                            <div>
                                <strong>System</strong>
                                <p>No recent activity found yet.</p>
                            </div>
                        </div>
                        <span class="time">just now</span>
                    </li>
                <?php endif; ?>
                <?php foreach ($recentLogs as $i => $log): ?>
                    <?php
                    $tone = $log['tone'] ?? 'blue';
                    $name = $log['name'] ?? 'System';
                    $action = $log['action'] ?? 'Updated record';
                    $time = $log['time'] ?? 'just now';
                    $isHiddenInitially = $i >= 5;
                    ?>
                    <li class="<?= $isHiddenInitially ? 'activity-item is-hidden' : 'activity-item' ?>" <?= $isHiddenInitially ? 'style="display:none;"' : '' ?>>
                        <div class="activity-left">
                            <div>
                                <strong><?= htmlspecialchars($name) ?></strong>
                                <p><?= htmlspecialchars($action) ?></p>
                            </div>
                        </div>
                        <span class="time"><?= htmlspecialchars($time) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if (count($recentLogs) > 5): ?>
                <div class="activity-actions">
                    <button id="load-more-activity" type="button" class="btn btn-muted btn-small">See More (<?= (int)(count($recentLogs) - 5) ?> left)</button>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-search.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-profile-menu.js?v=6"></script>
<script>
    (function () {
        const btn = document.getElementById('load-more-activity');
        if (!btn) return;

        const hiddenItems = Array.from(document.querySelectorAll('.activity-item.is-hidden'));
        let revealed = 0;
        const step = 5;

        btn.addEventListener('click', function () {
            const next = hiddenItems.slice(revealed, revealed + step);
            next.forEach(function (item) {
                item.style.display = '';
            });
            revealed += next.length;
            const left = Math.max(hiddenItems.length - revealed, 0);

            if (revealed >= hiddenItems.length) {
                btn.style.display = 'none';
            } else {
                btn.textContent = 'See More (' + left + ' left)';
            }
        });
    })();
</script>
</body>
</html>



