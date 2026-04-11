<?php
/**
 * Guardian Dashboard Layout
 * Based on: guardian-dashboard.jsp
 */
$patients = $data['patients'];
$recentAlerts = $data['recentAlerts'];
$unreadCount = $data['unreadCount'];
$avgAdherence = $data['avgAdherence'];
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
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/dashboard.css?v=<?= $cssVer ?>">
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<main class="main-content">
    <!-- Hero Section -->
    <header class="dashboard-hero">
        <div class="hero-content">
            <h1 class="hero-title">Welcome back, <span class="highlight-text"><?= htmlspecialchars($data['guardianName']) ?></span></h1>
            <p class="hero-subtitle">Monitor your patients' medication adherence and health status.</p>
        </div>
        <div class="hero-actions">
            <a href="<?= htmlspecialchars($base) ?>/guardian/patients" class="btn btn-primary">
                <i>&#128101;</i> View Patients
            </a>
            <a href="<?= htmlspecialchars($base) ?>/guardian/alerts" class="btn btn-outline">
                <i>&#128276;</i> Active Alerts
            </a>
        </div>
    </header>

    <div class="main-layout">
        <!-- Stats Overview -->
        <section class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-blue">&#128100;</div>
                <div class="stat-details">
                    <span class="stat-label">Total Patients</span>
                    <h3 class="stat-number"><?= count($patients) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-red">&#9940;</div>
                <div class="stat-details">
                    <span class="stat-label">Active Alerts</span>
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

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Left Column: Patient List -->
            <div class="patients-column card-panel">
                <div class="section-header">
                    <h2>Your Patients</h2>
                    <a href="<?= htmlspecialchars($base) ?>/guardian/patients" class="link-btn">Manage &#187;</a>
                </div>
                <div class="patient-list">
                    <?php if (empty($patients)): ?>
                        <p class="empty-msg">No patients linked to your account.</p>
                    <?php else: ?>
                        <?php foreach ($patients as $p): ?>
                            <div class="patient-item">
                                <div class="avatar-circle"><?= strtoupper(substr($p['name'], 0, 1)) ?></div>
                                <div class="patient-info">
                                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                                    <p><?= htmlspecialchars($p['chronic_issues'] ?? 'No chronic issues reported') ?></p>
                                </div>
                                <a href="<?= htmlspecialchars($base) ?>/guardian/patients?nic=<?= $p['nic'] ?>" class="btn-action">View Profile</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Recent Alerts -->
            <div class="alerts-column card-panel">
                <div class="section-header">
                    <h2>Recent Alerts</h2>
                    <a href="<?= htmlspecialchars($base) ?>/guardian/alerts" class="link-btn">View All &#187;</a>
                </div>
                <ul class="notification-list">
                    <?php if (empty($recentAlerts)): ?>
                        <p class="empty-msg">No recent alerts.</p>
                    <?php else: ?>
                        <?php foreach ($recentAlerts as $a): ?>
                            <li class="notification-item <?= $a['is_read'] ? '' : 'unread' ?>">
                                <div class="notif-header">
                                    <span class="notif-type priority-<?= strtolower($a['type']) ?>">
                                        <?= $a['type'] === 'Critical' ? '&#9888;' : '&#8505;' ?> <?= $a['type'] ?>
                                    </span>
                                    <span class="notif-date"><?= date('M d, H:i', strtotime($a['created_at'])) ?></span>
                                </div>
                                <p class="notif-msg"><strong><?= htmlspecialchars($a['patient_name']) ?></strong>: <?= htmlspecialchars($a['message']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</main>

</body>
</html>
