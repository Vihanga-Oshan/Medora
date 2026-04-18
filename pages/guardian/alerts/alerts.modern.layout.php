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
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
    <style>
        body {
            padding-top: 68px !important;
            overflow-y: auto;
            overflow-x: hidden;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 110px !important;
            }
        }

        .dashboard-wrapper {
            min-height: calc(100vh - 68px);
            display: flex;
            flex-direction: column;
        }

        .dashboard-hero {
            position: relative;
            background-color: var(--medical-blue);
            padding: 56px 20px 78px;
            overflow: hidden;
            isolation: isolate;
        }

        .dashboard-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('<?= htmlspecialchars($base) ?>/assets/img/hero-image.jpg');
            background-size: cover;
            background-position: center 30%;
            filter: blur(3px);
            opacity: 0.45;
            z-index: -2;
        }

        .dashboard-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 120, 195, 0.75) 0%, rgba(0, 74, 124, 0.6) 100%);
            z-index: -1;
        }

        .hero-content,
        .hero-actions {
            position: relative;
            z-index: 10;
            color: #fff !important;
        }

        .guardian-alerts-hero .page-hero-content {
            max-width: 620px;
            padding: 0;
            position: relative;
            z-index: 11;
        }

        .guardian-alerts-hero .page-hero-title {
            font-size: clamp(2rem, 2.6vw, 2.6rem);
            margin-bottom: 10px;
            letter-spacing: -0.4px;
            line-height: 1.15;
            font-weight: 700;
            color: #fff !important;
            text-shadow: 0 2px 18px rgba(0, 0, 0, 0.35);
            opacity: 1 !important;
            position: relative;
            z-index: 12;
        }

        .guardian-alerts-hero .page-hero-subtitle {
            font-size: 1.06rem;
            line-height: 1.55;
            color: rgba(255, 255, 255, 0.96) !important;
            max-width: 560px;
            font-weight: 400;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.28);
            opacity: 1 !important;
            position: relative;
            z-index: 12;
        }

        .guardian-alerts-hero .page-hero-title,
        .guardian-alerts-hero .page-hero-subtitle,
        .guardian-alerts-hero .page-hero-content * {
            color: #fff !important;
        }

        .dashboard-hero::after {
            background: linear-gradient(135deg, rgba(0, 74, 124, 0.94) 0%, rgba(0, 43, 74, 0.9) 100%);
        }

        .dashboard-hero .btn-primary {
            background-color: #fff;
            color: #0078c3;
        }

        .dashboard-hero .btn-outline {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .dashboard-hero .btn,
        .dashboard-hero a,
        .dashboard-hero button {
            color: #fff !important;
            font-weight: 700;
        }

        .dashboard-hero .btn-primary,
        .dashboard-hero .btn-primary:hover,
        .dashboard-hero .btn-primary:focus {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.55);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        }

        .dashboard-hero .btn-outline,
        .dashboard-hero .btn-outline:hover,
        .dashboard-hero .btn-outline:focus {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.16);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        }

        .main-layout {
            margin: -42px auto 0;
            padding: 0 20px 8px;
            max-width: 1240px;
        }

        .stats-overview {
            margin-bottom: 24px;
            gap: 14px;
        }

        .stat-card,
        .card-panel {
            padding: 16px 18px;
        }

        .card-panel {
            margin-bottom: 24px;
        }

        .section-header h2,
        .card-panel h2 {
            margin-bottom: 12px;
            padding-bottom: 10px;
        }
    </style>
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<div class="dashboard-wrapper">
    <header class="dashboard-hero guardian-alerts-hero">
        <div class="hero-content page-hero-content">
            <h1 class="page-hero-title">Review guardian alerts.</h1>
            <p class="page-hero-subtitle">See unread and important updates at a glance.</p>
        </div>
        <div class="hero-actions">
            <?php if ($data['unread'] > 0): ?>
                <form method="post" action="<?= htmlspecialchars($base) ?>/guardian/alerts">
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
</div>

<?php require_once __DIR__ . '/../../patient/common/patient.footer.php'; ?>

</body>
</html>
