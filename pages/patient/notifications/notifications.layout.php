<?php
/**
 * Notifications Layout
 * Ported from: notifications.jsp
 */
$notifications = $data['notifications'];
$base          = APP_BASE ?: '';
$cssVer        = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your medication alerts and notifications on Medora">
    <title>Notifications | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/notifications.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>

<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">Notifications</h1>

    <div class="card">
        <p class="card-subtitle">Here are your recent and upcoming medication alerts:</p>

        <ul class="notification-list" id="notificationList">
            <?php if (empty($notifications)): ?>
                <div class="empty-state"><p>No new notifications.</p></div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <li id="notification-<?= (int)$n['id'] ?>" class="<?= $n['is_read'] ? 'read' : 'unread' ?>">
                        <button class="close-btn" onclick="deleteNotification(<?= (int)$n['id'] ?>)">&#10005;</button>
                        <span class="date"><?= htmlspecialchars($n['formatted_date']) ?></span>
                        <span class="message"><?= htmlspecialchars($n['message']) ?></span>
                        <?php
                            $isReminder = !empty($n['reminder_event_id']);
                            $isPendingReminder = $isReminder && strtoupper((string)($n['reminder_status'] ?? '')) === 'PENDING';
                        ?>
                        <?php if ($isPendingReminder): ?>
                            <button class="btn btn-primary" style="margin-top:8px;" onclick="markTaken(<?= (int)$n['id'] ?>, this)">
                                Mark as Taken
                            </button>
                        <?php elseif ($isReminder): ?>
                            <span class="date" style="margin-top:8px;">Dose status: <?= htmlspecialchars((string)($n['reminder_status'] ?? '')) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <button class="btn btn-outline clear-all" onclick="clearAllNotifications()">
            Clear All Notifications
        </button>
    </div>
</main>

<script>
function deleteNotification(id) {
    if (!confirm('Delete this notification?')) return;
    fetch('<?= htmlspecialchars($base) ?>/patient/notifications?action=delete&id='+id, { method:'POST' })
        .then(res => {
            if (res.ok) {
                const el = document.getElementById('notification-'+id);
                if (el) el.remove();
                checkEmpty();
            }
        });
}
function clearAllNotifications() {
    if (!confirm('Clear all notifications?')) return;
    fetch('<?= htmlspecialchars($base) ?>/patient/notifications?action=clearAll', { method:'POST' })
        .then(res => {
            if (res.ok) {
                document.getElementById('notificationList').innerHTML =
                    '<div class="empty-state"><p>No notifications yet</p></div>';
            }
        });
}
function markTaken(id, btn) {
    fetch('<?= htmlspecialchars($base) ?>/patient/notifications?action=markTaken&id=' + id, { method: 'POST' })
        .then(res => {
            if (!res.ok) throw new Error('mark failed');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Taken';
            }
            const row = document.getElementById('notification-' + id);
            if (row) row.classList.remove('unread');
        })
        .catch(() => {
            alert('Could not mark this dose as taken. Please try again.');
        });
}
function checkEmpty() {
    const list = document.getElementById('notificationList');
    if (!list.querySelector('li'))
        list.innerHTML = '<div class="empty-state"><p>No notifications yet</p></div>';
}
</script>

<?php require_once __DIR__ . '/../common/patient.footer.php'; ?>

</body>
</html>
