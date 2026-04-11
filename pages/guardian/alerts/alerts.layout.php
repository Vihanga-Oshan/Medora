<?php
/**
 * Guardian Alerts Layout
 * Based on: guardian-alerts.jsp
 */
$notifications = $data['notifications'];
$base = APP_BASE ?: '';
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts & Notifications | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/alerts.css?v=<?= $cssVer ?>">
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<main class="main-content">
    <header class="dashboard-hero">
        <div class="hero-content">
            <h1 class="hero-title">Alerts & Notifications</h1>
            <p class="hero-subtitle">Monitor medication alerts and health updates for your patients.</p>
        </div>
        <div class="hero-actions">
            <?php if ($data['unread'] > 0): ?>
                <form method="post">
                    <input type="hidden" name="action" value="markAllRead">
                    <button type="submit" class="btn btn-outline">&#10003; Mark All Read</button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-layout wrapper">
        <!-- Stats Row -->
        <section class="stats-row">
            <div class="stat-mini-card">
                <span class="mini-label">Total Alerts</span>
                <span class="mini-value"><?= $data['total'] ?></span>
            </div>
            <div class="stat-mini-card">
                <span class="mini-label">Unread</span>
                <span class="mini-value text-warning"><?= $data['unread'] ?></span>
            </div>
            <div class="stat-mini-card">
                <span class="mini-label">Resolved</span>
                <span class="mini-value text-success"><?= $data['resolved'] ?></span>
            </div>
        </section>

        <!-- Alerts List -->
        <div class="alerts-container card-panel">
            <h2>Recent Notifications</h2>
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <p>No alerts found in your history.</p>
                </div>
            <?php else: ?>
                <div class="alert-list">
                    <?php foreach ($notifications as $n): ?>
                        <div class="alert-row <?= $n['is_read'] ? '' : 'unread' ?>">
                            <div class="alert-icon-box">
                                <?php if ($n['type'] === 'Critical'): ?>
                                    <span class="icon critical">&#9888;</span>
                                <?php elseif ($n['type'] === 'PRESCRIPTION'): ?>
                                    <span class="icon info">&#128196;</span>
                                <?php else: ?>
                                    <span class="icon general">&#128276;</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="alert-main">
                                <div class="alert-header">
                                    <span class="patient-name"><?= htmlspecialchars($n['patient_name']) ?></span>
                                    <span class="alert-time"><?= date('M d, Y • H:i', strtotime($n['created_at'])) ?></span>
                                </div>
                                <p class="alert-msg"><?= htmlspecialchars($n['message']) ?></p>
                                <div class="alert-meta">
                                    <span class="badge badge-<?= strtolower($n['type']) ?>"><?= $n['type'] ?></span>
                                </div>
                            </div>

                            <div class="alert-actions">
                                <?php if (!$n['is_read']): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="markRead">
                                        <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                        <button type="submit" class="btn-dismiss" title="Dismiss">Dismiss</button>
                                    </form>
                                <?php endif; ?>
                                <a href="tel:<?= $n['patient_phone'] ?>" class="btn-call" title="Call Patient">&#128222;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

</body>
</html>
