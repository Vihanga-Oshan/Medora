<?php
/**
 * Guardian Patient Monitoring Layout
 * Based on: guardian-patients.jsp
 */
$patients = $data['patients'];
$selectedPatient = $data['selectedPatient'];
$medications = $data['medications'];
$base = APP_BASE ?: '';
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Monitoring | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/dashboard.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/guardian/patients.css?v=<?= $cssVer ?>">
</head>
<body class="guardian-body">

<?php require_once __DIR__ . '/../common/guardian.navbar.php'; ?>

<main class="main-content">
    <header class="dashboard-hero">
        <div class="hero-content">
            <h1 class="hero-title">Patient Monitoring</h1>
            <p class="hero-subtitle">Real-time health status and medication tracking.</p>
        </div>
        <div class="hero-actions">
            <button class="btn btn-outline" onclick="openAddModal()">Add Patient</button>
        </div>
    </header>

    <div class="main-layout wrapper">
        <!-- Patient Selector List -->
        <div class="selector-column">
            <h3>Manage Linked Patients</h3>
            <div class="patient-list">
                <?php if (empty($patients)): ?>
                    <div class="empty-state">No patients linked.</div>
                <?php else: ?>
                    <?php foreach ($patients as $p): ?>
                        <div class="list-item <?= ($p['nic'] === ($selectedPatient['nic'] ?? '')) ? 'active' : '' ?>" 
                             onclick="window.location.href='<?= htmlspecialchars($base) ?>/guardian/patients?nic=<?= $p['nic'] ?>'">
                            <div class="item-avatar"><?= strtoupper(substr($p['name'], 0, 1)) ?></div>
                            <div class="item-info">
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                                <span><?= htmlspecialchars($p['gender']) ?></span>
                            </div>
                            <div class="item-action">
                                <form action="<?= htmlspecialchars($base) ?>/guardian/patients/remove" method="post" onsubmit="return confirm('Remove patient?')">
                                    <input type="hidden" name="nic" value="<?= $p['nic'] ?>">
                                    <button type="submit" class="btn-remove">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detail Monitoring View -->
        <div class="monitoring-column">
            <?php if ($selectedPatient): ?>
                <div class="detail-grid">
                    <!-- Profile Card -->
                    <div class="card-panel">
                        <h2>Patient Profile</h2>
                        <div class="profile-header">
                            <div class="big-avatar"><?= strtoupper(substr($selectedPatient['name'], 0, 1)) ?></div>
                            <h3><?= htmlspecialchars($selectedPatient['name']) ?></h3>
                            <p class="patient-nic">NIC: <?= htmlspecialchars($selectedPatient['nic']) ?></p>
                        </div>
                        <div class="profile-info">
                            <div class="info-row">
                                <span class="label">Chronic Issues:</span>
                                <span class="value"><?= htmlspecialchars($selectedPatient['chronic_issues'] ?? 'None') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Allergies:</span>
                                <span class="value text-danger"><?= htmlspecialchars($selectedPatient['allergies'] ?? 'None Reported') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Emergency Contact:</span>
                                <span class="value"><?= htmlspecialchars($selectedPatient['emergency_contact'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Schedule -->
                    <div class="card-panel">
                        <h2>Today's Medication Plan</h2>
                        <div class="schedule-list">
                            <?php if (empty($medications)): ?>
                                <p class="empty-msg">No medications scheduled for today.</p>
                            <?php else: ?>
                                <?php foreach ($medications as $m): ?>
                                    <div class="med-item status-<?= strtolower($m['status']) ?>">
                                        <div class="med-head">
                                            <strong><?= htmlspecialchars($m['medicine_name']) ?></strong>
                                            <span class="badge badge-<?= strtolower($m['status']) ?>"><?= $m['status'] ?></span>
                                        </div>
                                        <div class="med-meta">
                                            <?= htmlspecialchars($m['dosage']) ?> • <?= htmlspecialchars($m['frequency']) ?> • <?= htmlspecialchars($m['meal_timing'] ?? 'Anytime') ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-panel central-msg">
                    <img src="/public/assets/img/empty-patients.svg" alt="Select Patient" onerror="this.style.display='none'">
                    <h2>Select a Patient</h2>
                    <p>Choose a patient from the left list to view their health status and schedule.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Add Patient Modal -->
<div id="addPatientModal" class="modal hidden">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAddModal()">&times;</span>
        <h3>Add New Patient</h3>
        <p>Enter the NIC of the patient relative to link them to your monitoring dashboard.</p>
        <form action="/guardian/patients/add" method="post">
            <div class="field-group">
                <label>Patient NIC</label>
                <input type="text" name="nic" required placeholder="e.g. 199512345678">
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-muted" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Patient</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() { document.getElementById('addPatientModal').classList.remove('hidden'); }
function closeAddModal() { document.getElementById('addPatientModal').classList.add('hidden'); }
</script>

</body>
</html>
