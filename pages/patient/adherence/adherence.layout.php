<?php
/**
 * Adherence History Layout
 * Ported from: adherence-history.jsp
 */
$overallAdherence  = $data['overallAdherence'];
$weeklyAdherence   = $data['weeklyAdherence'];
$medicationHistory = $data['medicationHistory'];
$base              = APP_BASE ?: '';
$cssVer            = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your medication adherence history and compliance score on Medora">
    <title>Adherence History | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/adherence.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>

<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">Adherence History</h1>
    <p class="section-subtitle">Track your medication compliance over time</p>

    <div class="stats-row">
        <!-- Overall Adherence -->
        <div class="card adherence-overall">
            <div class="card-title">&#128200; Overall Adherence Rate</div>
            <p class="card-subtitle">Your medication compliance score</p>
            <div class="adherence-value" id="overallAdherenceValue"><?= $overallAdherence ?>%</div>
            <div class="progress-bar">
                <div class="progress-fill" id="overallAdherenceBar" style="width: <?= $overallAdherence ?>%;"></div>
            </div>
            <p class="tip" id="overallAdherenceTip">
                <?php if ($overallAdherence >= 80): ?>
                    &#127775; Great job! Keep up the consistency.
                <?php elseif ($overallAdherence >= 50): ?>
                    &#128161; Try to take your medications on time to improve your score.
                <?php else: ?>
                    &#9888; Your adherence is low. Please follow your prescription schedule.
                <?php endif; ?>
            </p>
        </div>

        <!-- Last 7 Days -->
        <div class="card adherence-week">
            <h3 class="card-title">Last 7 Days</h3>
            <p class="card-subtitle">Daily adherence rates</p>
            <div class="day-stats" id="weeklyAdherenceRows">
                <?php foreach ($weeklyAdherence as $entry): ?>
                    <div class="day-row">
                        <span class="day-label"><?= htmlspecialchars($entry['day']) ?></span>
                        <div class="bar">
                            <div class="fill" style="width: <?= $entry['percentage'] ?>%;"></div>
                        </div>
                        <span class="day-pct"><?= $entry['percentage'] ?>%</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Medication History Table -->
    <div class="card">
        <h2 class="card-title">Medication History</h2>
        <p class="card-subtitle">Complete log of your medication intake (Last 50 entries)</p>

        <?php if (empty($medicationHistory)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#128197;</div>
                <p>No medication history recorded yet.</p>
            </div>
        <?php else: ?>
            <table class="history-table" id="historyTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicationHistory as $log):
                        $s = strtolower($log['status']);
                        $pillClass = $s === 'taken' ? 'status-taken' : 'status-missed';
                    ?>
                        <tr>
                            <td><?= htmlspecialchars((string)($log['displayDate'] ?? $log['date'])) ?></td>
                            <td><?= htmlspecialchars($log['medicine']) ?></td>
                            <td><span class="slot-badge"><?= htmlspecialchars((string)($log['timeSlot'] ?? 'General')) ?></span></td>
                            <td><span class="status-badge <?= $pillClass ?>"><?= strtoupper($log['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <p id="adherenceLiveNote" style="margin-top:10px; color:#64748b; font-size:12px;">Live update: checking every 15s</p>
    </div>
</main>

<?php require_once __DIR__ . '/../common/patient.footer.php'; ?>

<script>
(() => {
    const base = <?= json_encode($base) ?>;
    const endpoint = `${base}/patient/adherence/data`;

    const overallValue = document.getElementById('overallAdherenceValue');
    const overallBar = document.getElementById('overallAdherenceBar');
    const overallTip = document.getElementById('overallAdherenceTip');
    const weeklyRows = document.getElementById('weeklyAdherenceRows');
    const historyTable = document.getElementById('historyTable');
    const liveNote = document.getElementById('adherenceLiveNote');

    const getTip = (score) => {
        if (score >= 80) return 'Great job! Keep up the consistency.';
        if (score >= 50) return 'Try to take your medications on time to improve your score.';
        return 'Your adherence is low. Please follow your prescription schedule.';
    };

    const renderWeekly = (rows) => {
        if (!weeklyRows || !Array.isArray(rows)) return;
        weeklyRows.innerHTML = rows.map((entry) => `
            <div class="day-row">
                <span class="day-label">${String(entry.day ?? '')}</span>
                <div class="bar"><div class="fill" style="width: ${Number(entry.percentage ?? 0)}%;"></div></div>
                <span class="day-pct">${Number(entry.percentage ?? 0)}%</span>
            </div>
        `).join('');
    };

    const renderHistory = (items) => {
        if (!historyTable || !Array.isArray(items)) return;
        const tbody = historyTable.querySelector('tbody');
        if (!tbody) return;
        tbody.innerHTML = items.map((log) => {
            const status = String(log.status ?? '').toUpperCase();
            const cls = status === 'TAKEN' ? 'status-taken' : 'status-missed';
            return `
                <tr>
                    <td>${String(log.displayDate ?? log.date ?? '')}</td>
                    <td>${String(log.medicine ?? '')}</td>
                    <td><span class="slot-badge">${String(log.timeSlot ?? 'General')}</span></td>
                    <td><span class="status-badge ${cls}">${status}</span></td>
                </tr>
            `;
        }).join('');
    };

    const refresh = async () => {
        try {
            const res = await fetch(endpoint, { credentials: 'same-origin', cache: 'no-store' });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            if (!data || data.ok !== true) throw new Error('Invalid payload');

            const score = Number(data.overallAdherence ?? 0);
            if (overallValue) overallValue.textContent = `${score}%`;
            if (overallBar) overallBar.style.width = `${score}%`;
            if (overallTip) overallTip.textContent = getTip(score);

            renderWeekly(data.weeklyAdherence ?? []);
            renderHistory(data.medicationHistory ?? []);

            if (liveNote) liveNote.textContent = `Live update: last synced at ${new Date().toLocaleTimeString()}`;
        } catch (err) {
            if (liveNote) liveNote.textContent = 'Live update temporarily unavailable';
        }
    };

    refresh();
    setInterval(refresh, 15000);
})();
</script>

</body>
</html>
