<?php
$base = APP_BASE ?: '';
$messages = $data['messages'] ?? [];
$activeMeds = $data['activeMeds'] ?? [];
$flash = $data['flash'] ?? '';
$hasChatTable = (bool)($data['hasChatTable'] ?? false);
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/chat-style.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/messages.css?v=<?= $cssVer ?>">
</head>
<body class="patient-messages-page">

<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<main class="container patient-view">
    <section class="chat-layout patient-chat-layout">
        <div class="chat-window has-selection">
            <div class="chat-container">
                <div class="chat-header">
                    <div class="status-dot"></div>
                    <div>
                        <h3>Pharmacy Support</h3>
                        <div class="pm-subtitle">Expert Healthcare Advisor</div>
                    </div>
                </div>

                <div class="chat-messages" id="message-container">
                    <?php if (empty($messages)): ?>
                        <div class="pm-empty-chat">No messages yet. Start chatting with the pharmacy team.</div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <?php
                            $isSent = ($msg['senderType'] ?? '') === 'patient';
                            $text = (string)($msg['text'] ?? '');
                            $time = (string)($msg['sentAt'] ?? '');
                            $formattedTime = $time ? date('d M Y, h:i A', strtotime($time)) : '';
                            ?>
                            <div class="message <?= $isSent ? 'sent' : 'received' ?>">
                                <span><?= htmlspecialchars($text) ?></span>
                                <span class="message-time"><?= htmlspecialchars($formattedTime) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($hasChatTable): ?>
                    <form method="post" class="chat-input-area">
                        <input type="text" class="chat-input" name="message" maxlength="1000" placeholder="Write your message..." autocomplete="off" required>
                        <button type="submit" class="send-btn" aria-label="Send message">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <aside class="chat-sidebar">
            <div class="sidebar-section">
                <h3>Active Medications</h3>
                <?php if (empty($activeMeds)): ?>
                    <p class="pm-no-meds">No active prescriptions found.</p>
                <?php else: ?>
                    <div class="meds-list">
                        <?php foreach ($activeMeds as $med): ?>
                            <div class="med-widget-item">
                                <h4><?= htmlspecialchars($med['medicine_name'] ?? 'Medication') ?></h4>
                                <div class="dosage"><?= htmlspecialchars($med['dosage'] ?? '-') ?></div>
                                <div class="frequency">
                                    <?= htmlspecialchars($med['frequency'] ?? '-') ?> - <?= htmlspecialchars($med['meal_timing'] ?? '-') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </section>
</main>

<script>
    (function () {
        const container = document.getElementById('message-container');
        if (container) container.scrollTop = container.scrollHeight;
    })();
</script>

</body>
</html>
