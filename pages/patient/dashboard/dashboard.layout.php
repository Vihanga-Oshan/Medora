<?php
/**
 * Patient Dashboard Layout
 * Ported from: patient-dashboard.jsp
 * Variables available: $user, $data
 */
$medications        = $data['medications'];
$pendingMedications = $data['pendingMedications'];
$notifications      = $data['notifications'];
$requestingGuardian = $data['requestingGuardian'];
$selectedDate       = $data['selectedDate'];
$totalCount         = $data['totalCount'];
$takenCount         = $data['takenCount'];
$missedCount        = $data['missedCount'];
$pendingCount       = $data['pendingCount'];
$adherenceScore     = $data['adherenceScore'];
$selectedPharmacy   = $data['selectedPharmacy'] ?? null;
$patientName        = htmlspecialchars($user['name'] ?? 'Patient');
$base               = APP_BASE ?: '';
$cssVer             = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Medora Patient Dashboard — manage your medication schedule and adherence">
    <title>Patient Dashboard | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-body);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .dashboard-hero {
            position: relative;
            background-color: var(--medical-blue);
            padding: 120px 20px 100px;
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
            opacity: 0.5;
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
            color: white;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .dashboard-hero .btn-primary {
            background-color: white;
            color: #0078c3;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .dashboard-hero .btn-primary:hover {
            background-color: #f8fbff;
            transform: translateY(-2px);
        }

        .dashboard-hero .btn-outline {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .dashboard-hero .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<div class="dashboard-wrapper">

    <!-- ===== Hero Section ===== -->
    <header class="dashboard-hero">
        <div class="hero-content">
            <h1 class="hero-title">Welcome back, <span class="highlight-text"><?= $patientName ?></span></h1>
            <p class="hero-subtitle">Manage your health with precision and ease.</p>
            <?php if (!empty($selectedPharmacy)): ?>
                <p class="hero-subtitle" style="margin-top:8px;">
                    Pharmacy: <strong><?= htmlspecialchars((string)$selectedPharmacy['name']) ?></strong>
                    &nbsp; <a href="<?= htmlspecialchars($base) ?>/patient/pharmacy-select" style="color:#fff; text-decoration:underline;">Change</a>
                </p>
            <?php endif; ?>
        </div>
        <div class="hero-actions">
            <a href="<?= htmlspecialchars($base) ?>/patient/shop" class="btn btn-primary">&#128722; Order Medicines</a>
            <a href="<?= htmlspecialchars($base) ?>/patient/shop/orders" class="btn btn-outline">&#128230; My Orders</a>
        </div>
    </header>

    <div class="main-layout">

        <!-- ===== Stats Overview ===== -->
        <section class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-blue">&#128138;</div>
                <div class="stat-details">
                    <span class="stat-label">Total Doses</span>
                    <h3 class="stat-number"><?= $totalCount ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-green">&#9989;</div>
                <div class="stat-details">
                    <span class="stat-label">Taken</span>
                    <h3 class="stat-number"><?= $takenCount ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-yellow">&#8987;</div>
                <div class="stat-details">
                    <span class="stat-label">Pending</span>
                    <h3 class="stat-number"><?= $pendingCount ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-red">&#9888;</div>
                <div class="stat-details">
                    <span class="stat-label">Missed</span>
                    <h3 class="stat-number"><?= $missedCount ?></h3>
                </div>
            </div>
        </section>

        <!-- ===== Content Grid ===== -->
        <div class="content-grid">

            <!-- Left Column: Adherence & Today's Schedule -->
            <div class="schedule-column">

                <!-- Adherence Widget -->
                <div class="card-panel adherence-panel">
                    <div class="adherence-text">
                        <h3>Adherence Score</h3>
                        <p>Based on today's schedule</p>
                    </div>
                    <div class="adherence-widget">
                        <div class="adherence-chart" style="--adherence-deg: <?= $adherenceScore * 3.6 ?>deg;">
                            <span class="adherence-score"><?= $adherenceScore ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="section-header">
                    <h2>Today's Schedule</h2>
                    <div class="date-badge">&#128197; <span>Today</span></div>
                </div>

                <div class="schedule-list">
                    <?php if (empty($pendingMedications)): ?>
                        <div class="empty-state-modern">
                            <div class="state-icon" style="background:#dcfce7;">
                                <span style="font-size:24px; color:#10b981;">&#10003;</span>
                            </div>
                            <h3>All Caught Up!</h3>
                            <p>You have no pending medications for the rest of today.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pendingMedications as $m): ?>
                            <div class="medication-item">
                                <div class="med-time">
                                    <span class="time-slot"><?= htmlspecialchars($m['frequency']) ?></span>
                                    <span class="meal-timing"><?= htmlspecialchars($m['meal_timing'] ?? '') ?></span>
                                </div>
                                <div class="med-info-block">
                                    <h3><?= htmlspecialchars($m['medicine_name']) ?></h3>
                                    <p class="dosage-info"><span>&#128138;</span> <?= htmlspecialchars($m['dosage']) ?></p>
                                    <p class="instructions"><span>&#8505;</span> <?= htmlspecialchars($m['instructions'] ?? '') ?></p>
                                </div>
                                <div class="med-actions-block">
                                    <form action="<?= htmlspecialchars($base) ?>/patient/medications/mark" method="post">
                                        <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars(Csrf::token('patient_medication_mark')) ?>">
                                        <input type="hidden" name="schedule_id"  value="<?= (int)$m['id'] ?>">
                                        <input type="hidden" name="patient_nic"  value="<?= htmlspecialchars($user['nic']) ?>">
                                        <input type="hidden" name="status"       value="TAKEN">
                                        <input type="hidden" name="time_slot"    value="<?= htmlspecialchars($m['frequency']) ?>">
                                        <input type="hidden" name="redirect"     value="<?= htmlspecialchars($base) ?>/patient/dashboard">
                                        <button type="submit" class="action-btn btn-check" title="Mark as Taken">&#10003;</button>
                                    </form>
                                    <form action="<?= htmlspecialchars($base) ?>/patient/medications/mark" method="post">
                                        <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars(Csrf::token('patient_medication_mark')) ?>">
                                        <input type="hidden" name="schedule_id"  value="<?= (int)$m['id'] ?>">
                                        <input type="hidden" name="patient_nic"  value="<?= htmlspecialchars($user['nic']) ?>">
                                        <input type="hidden" name="status"       value="MISSED">
                                        <input type="hidden" name="time_slot"    value="<?= htmlspecialchars($m['frequency']) ?>">
                                        <input type="hidden" name="redirect"     value="<?= htmlspecialchars($base) ?>/patient/dashboard">
                                        <button type="submit" class="action-btn btn-cross" title="Mark as Missed">&#10007;</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Timetable & Notifications -->
            <div class="timetable-column">

                <!-- Medication Timetable -->
                <div class="card-panel">
                    <h2>Medication Timetable</h2>
                    <form method="get" class="modern-date-selector">
                        <label for="date">Select Date</label>
                        <div class="input-group">
                            <input type="date" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                            <button type="submit" class="btn-icon">&#128269;</button>
                        </div>
                    </form>
                    <div class="timetable-results">
                        <p class="results-header">Schedule for <strong><?= htmlspecialchars($selectedDate) ?></strong></p>
                        <?php if (empty($medications)): ?>
                            <div class="empty-mini-state"><p>No schedule found.</p></div>
                        <?php else: ?>
                            <div class="timetable-scroll">
                                <?php foreach ($medications as $m):
                                    $s = strtolower($m['status']);
                                    $pillClass = $s === 'taken' ? 'status-taken' : ($s === 'missed' ? 'status-missed' : 'status-pending');
                                ?>
                                    <div class="timetable-row">
                                        <div class="row-info">
                                            <h4><?= htmlspecialchars($m['medicine_name']) ?></h4>
                                            <span><?= htmlspecialchars($m['dosage']) ?> &bull; <?= htmlspecialchars($m['frequency']) ?></span>
                                        </div>
                                        <span class="status-pill <?= $pillClass ?>"><?= htmlspecialchars(strtoupper($m['status'])) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Guardian Link Request Alert -->
                <?php if ($requestingGuardian): ?>
                    <div class="card-panel alert-panel">
                        <h4>&#128276; Guardian Request</h4>
                        <p><strong><?= htmlspecialchars($requestingGuardian['name']) ?></strong> wants to be linked as your guardian.</p>
                        <div class="alert-actions">
                            <form action="<?= htmlspecialchars($base) ?>/patient/guardian/accept" method="post" style="display:inline;">
                                <input type="hidden" name="guardian_nic" value="<?= htmlspecialchars($requestingGuardian['nic']) ?>">
                                <button type="submit" class="btn btn-accept">Accept</button>
                            </form>
                            <form action="<?= htmlspecialchars($base) ?>/patient/guardian/reject" method="post" style="display:inline;">
                                <input type="hidden" name="guardian_nic" value="<?= htmlspecialchars($requestingGuardian['nic']) ?>">
                                <button type="submit" class="btn btn-reject">Decline</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Notifications -->
                <div class="card-panel">
                    <h2>Recent Alerts</h2>
                    <?php if (empty($notifications)): ?>
                        <div class="empty-mini-state"><p>No new notifications</p></div>
                    <?php else: ?>
                        <ul class="notification-list-widget">
                            <?php foreach ($notifications as $n): ?>
                                <li class="notification-item">
                                    <div class="notif-header">
                                        <span>&#128276; Alert</span>
                                        <span><?= htmlspecialchars(date('M d', strtotime($n['created_at']))) ?></span>
                                    </div>
                                    <p class="notif-msg"><?= htmlspecialchars($n['message']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            </div><!-- /timetable-column -->
        </div><!-- /content-grid -->
    </div><!-- /main-layout -->
</div><!-- /dashboard-wrapper -->

<?php require_once __DIR__ . '/../common/patient.footer.php'; ?>

</body>
</html>
