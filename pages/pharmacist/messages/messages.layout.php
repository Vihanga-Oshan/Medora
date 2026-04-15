<?php
$base = APP_BASE ?: '';

$base = APP_BASE ?: '';

$base = APP_BASE ?: '';

$base = APP_BASE ?: '';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$contacts = $data['contacts'] ?? [];
$selectedContactId = (string)($data['selectedContactId'] ?? '');
$selectedContact = $data['selectedContact'] ?? null;
$messages = $data['messages'] ?? [];
$chatType = $data['type'] ?? 'patients';
$unreadTotal = (int)($data['unreadTotal'] ?? 0);
$flash = $data['flash'] ?? '';
$hasChatTable = (bool)($data['hasChatTable'] ?? false);

$isDashboard = str_contains($currentPath, '/pharmacist/dashboard');
$isValidate = str_contains($currentPath, '/pharmacist/validate') || str_contains($currentPath, '/pharmacist/prescriptions');
$isApproved = str_contains($currentPath, '/pharmacist/approved-prescriptions') || str_contains($currentPath, '/pharmacist/scheduling');
$isPatients = str_contains($currentPath, '/pharmacist/patients');
$isMessages = str_contains($currentPath, '/pharmacist/messages') || str_contains($currentPath, '/pharmacist/dispensing');
$isMedicine = str_contains($currentPath, '/pharmacist/medicine-inventory') || str_contains($currentPath, '/pharmacist/inventory');
$isSettings = str_contains($currentPath, '/pharmacist/settings') || str_contains($currentPath, '/pharmacist/medication-plans');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/dashboard-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/chat-style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/messages.css">
</head>
<body class="messages-page">
<div class="container">
    <aside class="sidebar">
        <div class="logo-section">
            <div class="logo-icon">&#10010;</div>
            <h1 class="logo-text">Medora</h1>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/dashboard" class="nav-item <?= $isDashboard ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/validate" class="nav-item <?= $isValidate ? 'active' : '' ?>">Prescription Review</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/approved-prescriptions" class="nav-item <?= $isApproved ? 'active' : '' ?>">Approved Prescriptions</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/patients" class="nav-item <?= $isPatients ? 'active' : '' ?>">Patients</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/messages" class="nav-item <?= $isMessages ? 'active' : '' ?>">Messages<?php if ($unreadTotal > 0): ?> <span class="nav-badge"><?= $unreadTotal ?></span><?php endif; ?></a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/medicine-inventory" class="nav-item <?= $isMedicine ? 'active' : '' ?>">Medicine</a></li>
                <li><a href="<?= htmlspecialchars($base) ?>/pharmacist/settings" class="nav-item <?= $isSettings ? 'active' : '' ?>">Settings</a></li>
            </ul>
        </nav>

        <div class="footer-section">
            <form method="post" action="<?= htmlspecialchars($base) ?>/pharmacist/logout" style="margin-top:10px;">
                <button type="submit" class="nav-item logout-link" style="display:block; width:100%; text-align:left; border:none; background:none; cursor:pointer;">Logout</button>
            </form>
            <div class="copyright">Medora &copy; 2022</div>
            <div class="version">v 1.1.2</div>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <div class="user-info">
                <img src="<?= htmlspecialchars($base) ?>/assets/img/avatar.png" alt="User Avatar" class="avatar">
                <span class="user-role"><?= htmlspecialchars($user['name'] ?? 'Pharmacist') ?></span>
            </div>
            <div class="greeting">
                <span class="greeting-icon">&#9728;&#65039;</span>
                <div>
                    <span class="greeting-text"><?= htmlspecialchars($data['greeting'] ?? 'Good Day') ?></span>
                    <span class="date-time"><?= htmlspecialchars($data['currentDate'] ?? '') ?> &bull; <?= htmlspecialchars($data['currentTime'] ?? '') ?></span>
                </div>
            </div>
        </header>

        <?php if (!$hasChatTable): ?>
            <section class="messages-empty-db">
                <h2>Messages</h2>
                <p>`chat_messages` table is missing. Create it to enable pharmacist messaging.</p>
            </section>
        <?php else: ?>
            <?php if ($flash === 'failed'): ?>
                <div class="messages-flash error">Failed to send message. Please try again.</div>
            <?php elseif ($flash === 'sent'): ?>
                <div class="messages-flash success">Message sent.</div>
            <?php endif; ?>

            <section class="chat-layout pharmacist-chat-layout">
                <div class="contact-list">
                    <div class="contact-tabs">
                        <a class="tab-link <?= $chatType === 'patients' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/pharmacist/messages?type=patients">Patients</a>
                        <a class="tab-link <?= $chatType === 'suppliers' ? 'active' : '' ?>" href="<?= htmlspecialchars($base) ?>/pharmacist/messages?type=suppliers">Suppliers</a>
                    </div>

                    <?php if (empty($contacts)): ?>
                        <div class="contact-empty">No contacts found.</div>
                    <?php endif; ?>

                    <?php foreach ($contacts as $contact): ?>
                        <?php
                        $contactId = (string)$contact['id'];
                        $isActive = $contactId === $selectedContactId;
                        $unread = (int)($contact['unread'] ?? 0);
                        $initial = strtoupper(substr((string)$contact['name'], 0, 1));
                        $preview = trim((string)($contact['lastMessage'] ?? ''));
                        if ($preview === '') $preview = 'No messages yet';
                        if (strlen($preview) > 52) $preview = substr($preview, 0, 52) . '...';
                        $contactUrl = $base . '/pharmacist/messages?type=' . rawurlencode($chatType) . '&with=' . rawurlencode($contactId);
                        ?>
                        <a href="<?= htmlspecialchars($contactUrl) ?>" class="contact-item <?= $isActive ? 'active' : '' ?> <?= $unread > 0 ? 'has-unread' : '' ?>">
                            <div class="contact-avatar"><?= htmlspecialchars($initial) ?></div>
                            <div class="contact-info">
                                <h4>
                                    <?php if ($unread > 0): ?><span class="unread-dot"></span><?php endif; ?>
                                    <?= htmlspecialchars($contact['name']) ?>
                                </h4>
                                <p><?= htmlspecialchars($preview) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="chat-window <?= $selectedContact ? 'has-selection' : '' ?>">
                    <div class="no-selection">
                        <h3>Your Conversations</h3>
                        <p>Select a contact from the left to view messages.</p>
                    </div>

                    <div class="chat-container">
                        <div class="chat-header">
                            <div class="status-dot"></div>
                            <div>
                                <h3 id="current-chat-name"><?= htmlspecialchars($selectedContact['name'] ?? 'Conversation') ?></h3>
                                <div class="chat-header-subtitle">Active Session</div>
                            </div>
                        </div>

                        <div class="chat-messages" id="message-container">
                            <?php if (empty($messages) && $selectedContact): ?>
                                <div class="message received">
                                    <span>No messages yet. Start the conversation.</span>
                                    <span class="message-time">Now</span>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($messages as $msg): ?>
                                <?php
                                $isSent = ($msg['senderType'] ?? '') === 'pharmacist';
                                $text = (string)($msg['text'] ?? '');
                                $time = (string)($msg['sentAt'] ?? '');
                                $formattedTime = $time ? date('d M Y, h:i A', strtotime($time)) : '';
                                ?>
                                <div class="message <?= $isSent ? 'sent' : 'received' ?>">
                                    <span><?= htmlspecialchars($text) ?></span>
                                    <span class="message-time"><?= htmlspecialchars($formattedTime) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($selectedContact): ?>
                            <form method="post" class="chat-input-area">
                                <input type="hidden" name="type" value="<?= htmlspecialchars($chatType) ?>">
                                <input type="hidden" name="contact_id" value="<?= htmlspecialchars($selectedContactId) ?>">
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
            </section>
        <?php endif; ?>
    </main>
</div>

<script>
    (function () {
        const container = document.getElementById('message-container');
        const form = document.querySelector('.chat-input-area');
        const input = form ? form.querySelector('input[name="message"]') : null;
        const navBadge = document.querySelector('.nav-badge');
        const chatType = <?= json_encode((string)$chatType) ?>;
        const selectedContactId = <?= json_encode((string)$selectedContactId) ?>;
        let pollInFlight = false;
        let sendInFlight = false;

        if (!container) return;

        const esc = (value) => {
            const d = document.createElement('div');
            d.textContent = String(value ?? '');
            return d.innerHTML;
        };

        const formatTime = (sentAt) => {
            if (!sentAt) return '';
            const date = new Date(String(sentAt).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) return String(sentAt);
            return date.toLocaleString(undefined, {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        const renderMessages = (items) => {
            if (!Array.isArray(items) || items.length === 0) {
                container.innerHTML =
                    '<div class="message received">' +
                    '<span>No messages yet. Start the conversation.</span>' +
                    '<span class="message-time">Now</span>' +
                    '</div>';
                return;
            }

            container.innerHTML = items.map((msg) => {
                const sender = String(msg.senderType || '').toLowerCase();
                const isSent = sender === 'pharmacist';
                return (
                    '<div class="message ' + (isSent ? 'sent' : 'received') + '">' +
                    '<span>' + esc(msg.text || '') + '</span>' +
                    '<span class="message-time">' + esc(formatTime(msg.sentAt || '')) + '</span>' +
                    '</div>'
                );
            }).join('');
        };

        const scrollToBottom = () => {
            container.scrollTop = container.scrollHeight;
        };

        const updateUnreadUi = (contacts, unreadTotal) => {
            if (navBadge) {
                if (Number(unreadTotal) > 0) {
                    navBadge.textContent = String(unreadTotal);
                    navBadge.style.display = '';
                } else {
                    navBadge.style.display = 'none';
                }
            }

            if (!Array.isArray(contacts)) return;
            const unreadById = {};
            contacts.forEach((contact) => {
                unreadById[String(contact.id ?? '')] = Number(contact.unread ?? 0);
            });

            document.querySelectorAll('.contact-item').forEach((link) => {
                try {
                    const url = new URL(link.href);
                    const id = url.searchParams.get('with') || '';
                    const unread = unreadById[id] || 0;
                    link.classList.toggle('has-unread', unread > 0);
                    let dot = link.querySelector('.unread-dot');
                    if (unread > 0 && !dot) {
                        const h4 = link.querySelector('h4');
                        if (h4) {
                            dot = document.createElement('span');
                            dot.className = 'unread-dot';
                            h4.prepend(dot);
                        }
                    }
                    if (unread === 0 && dot) {
                        dot.remove();
                    }
                } catch (e) {}
            });
        };

        const fetchMessages = () => {
            if (!selectedContactId) return;
            if (pollInFlight) return;
            pollInFlight = true;
            const url = '<?= htmlspecialchars($base) ?>/pharmacist/messages?action=fetch&type=' +
                encodeURIComponent(chatType) +
                '&with=' + encodeURIComponent(selectedContactId);

            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            })
            .then((res) => res.ok ? res.json() : null)
            .then((payload) => {
                if (!payload || payload.ok !== true) return;
                const prevHeight = container.scrollHeight;
                const nearBottom = (container.scrollTop + container.clientHeight + 80) >= prevHeight;
                renderMessages(payload.messages || []);
                updateUnreadUi(payload.contacts || [], payload.unreadTotal || 0);
                if (nearBottom) scrollToBottom();
            })
            .catch(() => {})
            .finally(() => {
                pollInFlight = false;
            });
        };

        if (form) {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const message = (input ? input.value : '').trim();
                if (!message || !selectedContactId || sendInFlight) return;
                sendInFlight = true;

                const body = new URLSearchParams();
                body.set('type', chatType);
                body.set('contact_id', selectedContactId);
                body.set('message', message);
                body.set('ajax', '1');

                fetch('<?= htmlspecialchars($base) ?>/pharmacist/messages', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: body.toString()
                })
                .then((res) => res.ok ? res.json() : null)
                .then((payload) => {
                    if (!payload || payload.ok !== true) return;
                    if (input) input.value = '';
                    renderMessages(payload.messages || []);
                    updateUnreadUi(payload.contacts || [], payload.unreadTotal || 0);
                    scrollToBottom();
                })
                .catch(() => {})
                .finally(() => {
                    sendInFlight = false;
                });
            });
        }

        scrollToBottom();
        setInterval(fetchMessages, 4000);
    })();
</script>
</body>
</html>
