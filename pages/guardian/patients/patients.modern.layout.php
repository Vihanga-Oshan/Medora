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
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<main class="main-content">
    <header class="dashboard-hero guardian-patients-hero">
        <div class="hero-content">
            <div class="hero-kicker">Guardian Workspace</div>
            <h1 class="hero-title">Stay on top of every linked patient's care with one focused view.</h1>
            <p class="hero-subtitle">Review linked patients, track today's medication progress, and send new patient link requests from one place.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn btn-primary" onclick="openAddModal()">+ Add Patient</button>
            <?php if ($selectedPatient): ?>
                <a href="<?= htmlspecialchars($base) ?>/guardian/patients?nic=<?= urlencode($selectedPatient['nic']) ?>" class="btn btn-outline">Viewing <?= htmlspecialchars($selectedPatient['name']) ?></a>
            <?php endif; ?>
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
</main>

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

</body>
</html>
