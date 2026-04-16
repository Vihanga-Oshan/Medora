<?php
/**
 * Guardian Alerts Layout
 */
$notifications = $data['notifications'];
$allNotifications = $data['allNotifications'];
$filter = $data['filter'];
$flash = $data['flash'];
$base = APP_BASE ?: '';
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Alerts | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/alerts-modern.css?v=<?= $cssVer ?>">
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<main class="main-content">
    <header class="dashboard-hero guardian-alerts-hero">
        <div class="hero-content">
            <div class="hero-kicker">Guardian Workspace</div>
            <h1 class="hero-title">Alerts that surface what needs your attention first.</h1>
            <p class="hero-subtitle">Track unread medication reminders, critical health updates, and patient-specific notifications in a cleaner workflow.</p>
        </div>
        <div class="hero-actions">
            <?php if ($data['unread'] > 0): ?>
                <form method="post" action="<?= htmlspecialchars($base) ?>/guardian/alerts">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('guardian_alerts_action')) ?>">
                    <input type="hidden" name="action" value="markAllRead">
                    <button type="submit" class="btn btn-primary">Mark All Read</button>
                </form>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($base) ?>/guardian/patients" class="btn btn-outline">View Patients</a>
        </div>
    </header>

    <div class="main-layout">
        <?php if ($flash): ?>
            <div class="guardian-alert-banner guardian-alert-banner-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-blue">&#128276;</div>
                <div class="stat-details">
                    <span class="stat-label">Total Alerts</span>
                    <h3 class="stat-number"><?= (int)$data['total'] ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-yellow">&#128339;</div>
                <div class="stat-details">
                    <span class="stat-label">Unread</span>
                    <h3 class="stat-number"><?= (int)$data['unread'] ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-red">&#9888;</div>
                <div class="stat-details">
                    <span class="stat-label">Critical</span>
                    <h3 class="stat-number"><?= (int)$data['critical'] ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-green">&#9989;</div>
                <div class="stat-details">
                    <span class="stat-label">Read</span>
                    <h3 class="stat-number"><?= (int)$data['resolved'] ?></h3>
                </div>
            </div>
        </section>

        <div class="guardian-alerts-grid">
            <aside class="card-panel alert-sidebar">
                <div class="section-header">
                    <div>
                        <h2>Filters</h2>
                        <p class="section-copy">Focus on the alerts that matter right now.</p>
                    </div>
                </div>

                <nav class="alert-filter-list">
                    <a class="alert-filter-chip <?= $filter === 'all' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/guardian/alerts?filter=all">
                        <span>All alerts</span>
                        <strong><?= (int)$data['total'] ?></strong>
                    </a>
                    <a class="alert-filter-chip <?= $filter === 'unread' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/guardian/alerts?filter=unread">
                        <span>Unread</span>
                        <strong><?= (int)$data['unread'] ?></strong>
                    </a>
                    <a class="alert-filter-chip <?= $filter === 'critical' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/guardian/alerts?filter=critical">
                        <span>Critical</span>
                        <strong><?= (int)$data['critical'] ?></strong>
                    </a>
                </nav>

                <div class="alert-sidebar-summary">
                    <h3>Quick Guide</h3>
                    <p>Unread alerts are highlighted. Critical items stay easy to spot, and each row lets you mark a specific alert as read without leaving the page flow.</p>
                </div>
            </aside>

            <section class="card-panel alerts-feed">
                <div class="section-header">
                    <div>
                        <h2>Patient Notifications</h2>
                        <p class="section-copy">
                            <?php if ($filter === 'unread'): ?>
                                Showing unread alerts only.
                            <?php elseif ($filter === 'critical'): ?>
                                Showing critical alerts only.
                            <?php else: ?>
                                Showing all guardian-visible notifications.
                            <?php endif; ?>
                        </p>
                    </div>
                    <span class="date-badge">&#128197; <span><?= date('Y-m-d') ?></span></span>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="empty-state-modern alert-empty-state">
                        <div class="state-icon state-icon-soft">&#128276;</div>
                        <h3>No alerts in this view</h3>
                        <p>
                            <?php if (!empty($allNotifications) && $filter !== 'all'): ?>
                                Try switching back to all alerts to see your full history.
                            <?php else: ?>
                                You have no guardian alerts yet. New medication reminders and patient updates will appear here.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="alert-feed-list">
                        <?php foreach ($notifications as $n): ?>
                            <?php
                                $type = strtoupper((string)($n['type'] ?? 'GENERAL'));
                                $isRead = (int)($n['is_read'] ?? 0) === 1;
                                $rowClass = $isRead ? 'is-read' : 'is-unread';
                                $badgeClass = $type === 'CRITICAL' ? 'status-missed' : ($type === 'REMINDER' ? 'status-pending' : 'status-taken');
                                $icon = $type === 'CRITICAL' ? '&#9888;' : ($type === 'PRESCRIPTION' ? '&#128196;' : '&#128276;');
                                $patientPhone = trim((string)($n['patient_phone'] ?? ''));
                            ?>
                            <article class="guardian-alert-row <?= htmlspecialchars($rowClass) ?>">
                                <div class="alert-icon-box <?= htmlspecialchars(strtolower($type)) ?>">
                                    <span><?= $icon ?></span>
                                </div>

                                <div class="alert-main">
                                    <div class="alert-topline">
                                        <div>
                                            <h3><?= htmlspecialchars((string)($n['patient_name'] ?? 'Patient')) ?></h3>
                                            <p class="alert-time"><?= htmlspecialchars(date('M d, Y | H:i', strtotime((string)$n['created_at']))) ?></p>
                                        </div>
                                        <span class="status-badge <?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($type) ?></span>
                                    </div>

                                    <p class="alert-message"><?= htmlspecialchars((string)($n['message'] ?? '')) ?></p>

                                    <div class="alert-meta-row">
                                        <span class="read-state <?= $isRead ? 'read' : 'unread' ?>">
                                            <?= $isRead ? 'Read' : 'Unread' ?>
                                        </span>
                                        <?php if ($patientPhone !== ''): ?>
                                            <a href="tel:<?= htmlspecialchars($patientPhone) ?>" class="mini-link">Call patient contact</a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="alert-actions">
                                    <?php if (!$isRead): ?>
                                        <form method="post" action="<?= htmlspecialchars($base) ?>/guardian/alerts?filter=<?= urlencode($filter) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('guardian_alerts_action')) ?>">
                                            <input type="hidden" name="action" value="markRead">
                                            <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-small">Mark Read</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="read-badge">Completed</span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

</body>
</html>
