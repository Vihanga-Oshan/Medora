<?php

$type = strtolower(trim((string)($_GET['type'] ?? 'patients')));
if (!in_array($type, ['patients', 'suppliers'], true)) {
    $type = 'patients';
}

$selectedContactId = trim((string)($_GET['with'] ?? ''));
$flash = trim((string)($_GET['msg'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedType = strtolower(trim((string)($_POST['type'] ?? $type)));
    if (!in_array($postedType, ['patients', 'suppliers'], true)) {
        $postedType = 'patients';
    }

    $postedContact = trim((string)($_POST['contact_id'] ?? ''));
    $text = trim((string)($_POST['message'] ?? ''));
    $sent = PharmacistMessagesModel::sendMessage((int)($user['id'] ?? 0), $postedContact, $text);

    $query = http_build_query([
        'type' => $postedType,
        'with' => $postedContact,
        'msg' => $sent ? 'sent' : 'failed',
    ]);
    Response::redirect('/pharmacist/messages?' . $query);
}

$contacts = PharmacistMessagesModel::getContacts($type);
if ($selectedContactId === '' && !empty($contacts)) {
    $selectedContactId = (string)$contacts[0]['id'];
}

$selectedContact = null;
foreach ($contacts as $contact) {
    if ((string)$contact['id'] === $selectedContactId) {
        $selectedContact = $contact;
        break;
    }
}

$messages = [];
if ($selectedContactId !== '') {
    $messages = PharmacistMessagesModel::getMessages($selectedContactId, $type);
    PharmacistMessagesModel::markAsRead($selectedContactId, $type);
}

$unreadTotal = 0;
foreach ($contacts as $contact) {
    $unreadTotal += (int)($contact['unread'] ?? 0);
}

$data = [
    'type' => $type,
    'contacts' => $contacts,
    'selectedContactId' => $selectedContactId,
    'selectedContact' => $selectedContact,
    'messages' => $messages,
    'unreadTotal' => $unreadTotal,
    'flash' => $flash,
    'hasChatTable' => PharmacistMessagesModel::canUseMessages(),
    'greeting' => (function (): string {
        $hour = (int)date('H');
        if ($hour < 12) return 'Good Morning';
        if ($hour < 18) return 'Good Afternoon';
        return 'Good Evening';
    })(),
    'currentDate' => date('d F Y'),
    'currentTime' => date('H:i:s'),
];

