<?php
/**
 * Guardian Patient Monitoring Layout
 */
$patients = $data['patients'];
$selectedPatient = $data['selectedPatient'];
$medications = $data['medications'];
$summary = $data['summary'];
$selectedDate = $data['selectedDate'];
$flash = $data['flash'];
$base = APP_BASE ?: '';
$cssVer = time();
$selectedNic = (string)($selectedPatient['nic'] ?? '');
$openAddModal = (($_GET['modal'] ?? '') === 'add');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Patients | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/patients-modern.css?v=<?= $cssVer ?>">
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

        .guardian-patients-hero .page-hero-content {
            max-width: 620px;
            padding: 14px 4px;
            position: relative;
            z-index: 11;
        }

        .guardian-patients-hero .page-hero-title {
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

        .guardian-patients-hero .page-hero-subtitle {
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

        .guardian-patients-hero .page-hero-title,
        .guardian-patients-hero .page-hero-subtitle,
        .guardian-patients-hero .page-hero-content * {
            color: #fff !important;
        }

        .dashboard-hero::after {
            background: linear-gradient(135deg, rgba(0, 92, 148, 0.86) 0%, rgba(0, 56, 96, 0.78) 100%);
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
        }

        .dashboard-hero .btn-primary,
        .dashboard-hero .btn-primary:hover,
        .dashboard-hero .btn-primary:focus {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.5);
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

        .patient-spotlight {
            margin-bottom: 24px;
        }
    </style>
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<div class="dashboard-wrapper">
    <header class="dashboard-hero guardian-patients-hero">
        <div class="hero-content page-hero-content">
            <h1 class="page-hero-title">Manage your linked patients.</h1>
            <p class="page-hero-subtitle">Check patient updates and today&apos;s medication progress in one place.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-primary" onclick="openAddModal()">+ Add Patient</button>
        </div>
    </header>

    <div class="main-layout">
        <?php if ($flash): ?>
            <div class="guardian-alert guardian-alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-blue">&#128101;</div>
                <div class="stat-details">
                    <span class="stat-label">Linked Patients</span>
                    <h3 class="stat-number"><?= (int)$summary['linkedCount'] ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-green">&#9989;</div>
                <div class="stat-details">
                    <span class="stat-label">Taken Today</span>
                    <h3 class="stat-number"><?= (int)$summary['selectedTaken'] ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-yellow">&#8987;</div>
                <div class="stat-details">
                    <span class="stat-label">Pending Today</span>
                    <h3 class="stat-number"><?= (int)$summary['selectedPending'] ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-red">&#9888;</div>
                <div class="stat-details">
                    <span class="stat-label">Missed Today</span>
                    <h3 class="stat-number"><?= (int)$summary['selectedMissed'] ?></h3>
                </div>
            </div>
        </section>

        <div class="guardian-patients-grid">
            <aside class="card-panel linked-patients-panel">
                <div class="section-header">
                    <div>
                        <h2>Linked Patients</h2>
                        <p class="section-copy">Quickly switch between profiles and monitor current medication status.</p>
                    </div>
                    <button type="button" class="link-btn link-btn-button" onclick="openAddModal()">Add</button>
                </div>

                <?php if (empty($patients)): ?>
                    <div class="empty-state-modern linked-empty">
                        <div class="state-icon state-icon-soft">&#128100;</div>
                        <h3>No patients linked yet</h3>
                        <p>Use a patient NIC to send a link request and start monitoring their schedule once they approve.</p>
                        <button type="button" class="btn btn-primary" onclick="openAddModal()">Link First Patient</button>
                    </div>
                <?php else: ?>
                    <div class="linked-patient-list">
                        <?php foreach ($patients as $p): ?>
                            <?php
                                $isActive = $p['nic'] === $selectedNic;
                                $displayName = trim((string)($p['name'] ?? 'Patient'));
                                $gender = trim((string)($p['gender'] ?? 'Not specified'));
                                $chronicIssues = trim((string)($p['chronic_issues'] ?? ''));
                            ?>
                            <article class="linked-patient-card <?= $isActive ? 'active' : '' ?>" onclick="window.location.href='<?= htmlspecialchars($base) ?>/guardian/patients?nic=<?= urlencode($p['nic']) ?>'">
                                <div class="linked-patient-main">
                                    <div class="item-avatar"><?= htmlspecialchars(strtoupper(substr($displayName, 0, 1))) ?></div>
                                    <div class="linked-patient-copy">
                                        <div class="patient-card-topline">
                                            <h3><?= htmlspecialchars($displayName) ?></h3>
                                            <?php if ($isActive): ?>
                                                <span class="status-pill">Selected</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="patient-card-subline">NIC <?= htmlspecialchars($p['nic']) ?></p>
                                        <p class="patient-card-meta"><?= htmlspecialchars($gender) ?><?php if ($chronicIssues !== ''): ?> | <?= htmlspecialchars($chronicIssues) ?><?php endif; ?></p>
                                    </div>
                                </div>
                                <div class="linked-patient-actions">
                                    <a href="<?= htmlspecialchars($base) ?>/guardian/patients?nic=<?= urlencode($p['nic']) ?>" class="mini-link" onclick="event.stopPropagation()">Open</a>
                                    <form action="<?= htmlspecialchars($base) ?>/guardian/patients/remove" method="post" onsubmit="event.stopPropagation(); return confirm('Remove patient from guardian list?');">
                                        <input type="hidden" name="nic" value="<?= htmlspecialchars($p['nic']) ?>">
                                        <button type="submit" class="btn-remove">Remove</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </aside>

            <section class="patient-detail-column">
                <?php if ($selectedPatient): ?>
                    <div class="card-panel patient-spotlight">
                        <div class="patient-spotlight-header">
                            <div class="patient-identity">
                                <div class="big-avatar"><?= htmlspecialchars(strtoupper(substr((string)$selectedPatient['name'], 0, 1))) ?></div>
                                <div>
                                    <h2><?= htmlspecialchars($selectedPatient['name']) ?></h2>
                                    <p class="patient-subtitle">NIC <?= htmlspecialchars($selectedPatient['nic']) ?> | <?= htmlspecialchars((string)($selectedPatient['gender'] ?? 'Not specified')) ?></p>
                                </div>
                            </div>
                            <div class="spotlight-badge">Schedule for <?= htmlspecialchars($selectedDate) ?></div>
                        </div>

                        <div class="detail-metric-grid">
                            <div class="detail-metric">
                                <span class="metric-label">Chronic Issues</span>
                                <strong><?= htmlspecialchars((string)($selectedPatient['chronic_issues'] ?? 'None reported')) ?></strong>
                            </div>
                            <div class="detail-metric">
                                <span class="metric-label">Allergies</span>
                                <strong><?= htmlspecialchars((string)($selectedPatient['allergies'] ?? 'None reported')) ?></strong>
                            </div>
                            <div class="detail-metric">
                                <span class="metric-label">Emergency Contact</span>
                                <strong><?= htmlspecialchars((string)($selectedPatient['emergency_contact'] ?? 'Not available')) ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="card-panel">
                        <div class="section-header">
                            <div>
                                <h2>Today's Medication Plan</h2>
                                <p class="section-copy">A guardian-friendly view of the patient's current schedule and completion status.</p>
                            </div>
                            <span class="date-badge">&#128197; <span><?= htmlspecialchars($selectedDate) ?></span></span>
                        </div>

                        <div class="schedule-list">
                            <?php if (empty($medications)): ?>
                                <div class="empty-state-modern">
                                    <div class="state-icon state-icon-blue">&#128138;</div>
                                    <h3>No medications scheduled for today</h3>
                                    <p>This patient does not have an active medication plan for the selected day.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($medications as $m): ?>
                                    <?php
                                        $status = strtoupper((string)($m['status'] ?? 'PENDING'));
                                        $statusClass = $status === 'TAKEN' ? 'status-taken' : ($status === 'MISSED' ? 'status-missed' : 'status-pending');
                                    ?>
                                    <article class="guardian-medication-item <?= htmlspecialchars($statusClass) ?>">
                                        <div class="med-time">
                                            <span class="time-slot"><?= htmlspecialchars((string)($m['frequency'] ?? '-')) ?></span>
                                            <span class="meal-timing"><?= htmlspecialchars((string)($m['meal_timing'] ?? 'Anytime')) ?></span>
                                        </div>
                                        <div class="med-info-block">
                                            <h3><?= htmlspecialchars((string)($m['medicine_name'] ?? 'Medication')) ?></h3>
                                            <p class="dosage-info"><span>&#128138;</span> <?= htmlspecialchars((string)($m['dosage'] ?? '-')) ?></p>
                                            <p class="instructions"><span>&#8505;</span> <?= htmlspecialchars((string)($m['instructions'] ?? 'No extra instructions')) ?></p>
                                        </div>
                                        <div class="med-status-block">
                                            <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($status) ?></span>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-panel empty-detail-panel">
                        <div class="empty-state-modern">
                            <div class="state-icon state-icon-soft">&#128269;</div>
                            <h3>Select a linked patient</h3>
                            <p>Choose a patient from the left panel to review their medication plan, key profile details, and today's progress.</p>
                            <button type="button" class="btn btn-primary" onclick="openAddModal()">Add Patient</button>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<div id="addPatientModal" class="modal-overlay <?= $openAddModal ? 'is-open' : '' ?>" aria-hidden="<?= $openAddModal ? 'false' : 'true' ?>">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="addPatientTitle">
        <button type="button" class="close-btn" onclick="closeAddModal()" aria-label="Close add patient modal">&times;</button>
        <div class="modal-copy">
            <div class="hero-kicker">Patient Link Request</div>
            <h3 id="addPatientTitle">Send a patient link request</h3>
            <p>Enter the patient's NIC exactly as it appears on their profile. Medora will prevent duplicate or conflicting guardian links.</p>
        </div>
        <form action="<?= htmlspecialchars($base) ?>/guardian/patients/add" method="post" class="guardian-patient-form">
            <div class="field-group">
                <label for="guardian-patient-nic">Patient NIC</label>
                <input id="guardian-patient-nic" type="text" name="nic" required placeholder="e.g. 199512345678" autocomplete="off">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-muted" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Send Request</button>
            </div>
        </form>
    </div>
</div>

<script>
const addPatientModal = document.getElementById('addPatientModal');

function openAddModal() {
    if (!addPatientModal) return;
    addPatientModal.classList.add('is-open');
    addPatientModal.setAttribute('aria-hidden', 'false');
    const input = document.getElementById('guardian-patient-nic');
    if (input) input.focus();
}

function closeAddModal() {
    if (!addPatientModal) return;
    addPatientModal.classList.remove('is-open');
    addPatientModal.setAttribute('aria-hidden', 'true');
}

if (addPatientModal) {
    addPatientModal.addEventListener('click', function (event) {
        if (event.target === addPatientModal) {
            closeAddModal();
        }
    });
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeAddModal();
    }
});
</script>

<?php require_once __DIR__ . '/../../patient/common/patient.footer.php'; ?>

</body>
</html>
