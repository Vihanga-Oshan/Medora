<?php
$session = $session ?? [];
$isUpcoming = !empty($isUpcoming);
$sessionDateTime = !empty($session['sessionDatetime']) ? strtotime((string) $session['sessionDatetime']) : null;
$displayTime = $sessionDateTime ? date('D, M j \a\t g:i A', $sessionDateTime) : 'Schedule unavailable';
$clientName = $session['userName'] ?? 'Client';
$sessionType = $session['sessionType'] ?? 'video';
$typeLabel = match ($sessionType) {
    'in_person' => 'In Person',
    'audio' => 'Audio',
    'chat' => 'Chat',
    default => 'Video',
};
?>
<div class="counselor-session-card">
    <div class="counselor-session-info">
        <h4><?= htmlspecialchars($clientName) ?></h4>
        <span><?= htmlspecialchars($displayTime) ?></span>
        <?php if ($isUpcoming): ?>
            <span><?= htmlspecialchars($typeLabel) ?></span>
            <?php if (!empty($session['meetingLink'])): ?>
                <a class="btn-join" href="<?= htmlspecialchars($session['meetingLink']) ?>" target="_blank" rel="noopener">Join</a>
            <?php else: ?>
                <button class="btn-join" type="button" disabled>No Link</button>
            <?php endif; ?>
        <?php else: ?>
            <span><?= htmlspecialchars($typeLabel) ?></span>
            <div class="session-action-row">
                <a class="btn-join" href="/pharmacist/dispensing">View Notes</a>
                <button class="btn-warning" type="button">Report</button>
            </div>
        <?php endif; ?>
    </div>
    <img src="/assets/img/avatar.png" alt="Client avatar" class="counselors-image" />
</div>
