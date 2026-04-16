<?php
/**
 * Guardian Dashboard Layout
 * Patient-style dashboard layout for guardians.
 */
$patients = $data['patients'];
$recentAlerts = $data['recentAlerts'];
$unreadCount = $data['unreadCount'];
$avgAdherence = $data['avgAdherence'];
$guardianName = htmlspecialchars($data['guardianName'] ?? 'Guardian');
$base = APP_BASE ?: '';
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Dashboard | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/dashboard.css?v=<?= $cssVer ?>">
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
                overflow-y: auto;
                overflow-x: hidden;
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
            z-index: 0;
        }

        .dashboard-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 120, 195, 0.75) 0%, rgba(0, 74, 124, 0.6) 100%);
            z-index: 1;
        }

        .hero-content,
        .hero-actions {
            position: relative;
            z-index: 2;
        }

        .highlight-text {
            color: #fff;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 4px;
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

        .hero-title {
            font-size: clamp(2rem, 2.6vw, 2.6rem);
            margin-bottom: 10px;
            letter-spacing: -0.4px;
            line-height: 1.15;
            font-weight: 700;
        }

        .hero-subtitle {
            font-size: 1.06rem;
            line-height: 1.65;
            color: rgba(226, 232, 240, 0.92);
            max-width: 720px;
            font-weight: 400;
        }
        .hero-helper {
            margin-top: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .93rem;
            color: rgba(241, 245, 249, 0.95);
            background: rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            padding: 6px 12px;
        }

        .highlight-text {
            display: inline-block;
            padding: 0 0 2px;
            border-bottom: 3px solid rgba(255, 255, 255, 0.9);
            text-decoration: none;
            text-underline-offset: 0;
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

        .stat-card {
            padding: 16px 18px;
            min-height: 106px;
        }

        .content-grid {
            gap: 24px;
            align-items: start;
        }

        .card-panel {
            padding: 16px 18px;
            margin-bottom: 24px;
        }

        .card-panel h2 {
            margin-bottom: 12px;
            padding-bottom: 10px;
        }

        .adherence-panel {
            margin-bottom: 24px;
        }

        .timetable-scroll {
            max-height: 220px;
        }

        .notification-list-widget {
            max-height: 290px;
            overflow: auto;
        }

    </style>
</head>
<body>

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<div class="dashboard-wrapper">
    <header class="dashboard-hero">
        <div class="hero-content">
            <h1 class="hero-title">Welcome back, <span class="highlight-text"><?= $guardianName ?></span></h1>
            <p class="hero-subtitle">Keep track of linked patients, medication adherence, and recent alerts.</p>
            <p class="hero-helper">&#9201; Stats refresh as patients respond and update their doses.</p>
        </div>
        <div class="hero-actions">
            <a href="<?= htmlspecialchars($base) ?>/guardian/patients" class="btn btn-primary">&#128101; Manage Patients</a>
            <a href="<?= htmlspecialchars($base) ?>/guardian/alerts" class="btn btn-outline">&#128276; View Alerts</a>
        </div>
    </header>

    <div class="main-layout">
        <section class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-blue">&#128100;</div>
                <div class="stat-details">
                    <span class="stat-label">Total Patients</span>
                    <h3 class="stat-number"><?= count($patients) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-yellow">&#128276;</div>
                <div class="stat-details">
                    <span class="stat-label">Unread Alerts</span>
                    <h3 class="stat-number"><?= $unreadCount ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-green">&#128200;</div>
                <div class="stat-details">
                    <span class="stat-label">Avg Adherence</span>
                    <h3 class="stat-number"><?= $avgAdherence ?>%</h3>
                </div>
            </div>
        </section>

        <div class="content-grid">
            <div class="schedule-column">
                <div class="card-panel adherence-panel">
                    <div class="adherence-text">
                        <h3>Average Adherence</h3>
                        <p>Across all linked patients</p>
                    </div>
                    <div class="adherence-widget">
                        <div class="adherence-chart" style="--adherence-deg: <?= max(0, min(100, (int)$avgAdherence)) * 3.6 ?>deg;">
                            <span class="adherence-score"><?= (int)$avgAdherence ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="card-panel">
                    <h2>Your Patients <a href="<?= htmlspecialchars($base) ?>/guardian/patients">Manage</a></h2>
                    <?php if (empty($patients)): ?>
                        <div class="empty-mini-state"><p>No patients linked to your account yet.</p></div>
                    <?php else: ?>
                        <div class="timetable-scroll">
                            <?php foreach ($patients as $p): ?>
                                <div class="timetable-row">
                                    <div class="row-info">
                                        <h4><?= htmlspecialchars((string)$p['name']) ?></h4>
                                        <span>
                                            NIC: <?= htmlspecialchars((string)$p['nic']) ?>
                                            <?php if (!empty($p['chronic_issues'])): ?>
                                                &bull; <?= htmlspecialchars((string)$p['chronic_issues']) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <a class="btn btn-outline" href="<?= htmlspecialchars($base) ?>/guardian/patients?nic=<?= urlencode((string)$p['nic']) ?>">View</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="timetable-column">
                <div class="card-panel">
                    <h2>Recent Alerts <a href="<?= htmlspecialchars($base) ?>/guardian/alerts">View All</a></h2>
                    <?php if (empty($recentAlerts)): ?>
                        <div class="empty-mini-state"><p>No recent alerts.</p></div>
                    <?php else: ?>
                        <ul class="notification-list-widget">
                            <?php foreach ($recentAlerts as $a): ?>
                                <?php
                                $type = strtolower((string)($a['type'] ?? 'info'));
                                $pillClass = $type === 'critical'
                                    ? 'status-missed'
                                    : ((int)($a['is_read'] ?? 0) === 0 ? 'status-pending' : 'status-taken');
                                ?>
                                <li class="notification-item">
                                    <div class="notif-header">
                                        <span><?= htmlspecialchars((string)($a['patient_name'] ?? 'Patient')) ?></span>
                                        <span><?= htmlspecialchars((string)date('M d, H:i', strtotime((string)$a['created_at']))) ?></span>
                                    </div>
                                    <p class="notif-msg"><?= htmlspecialchars((string)$a['message']) ?></p>
                                    <span class="status-pill <?= $pillClass ?>"><?= htmlspecialchars(strtoupper((string)($a['type'] ?? 'INFO'))) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../patient/common/patient.footer.php'; ?>

</body>
</html>
