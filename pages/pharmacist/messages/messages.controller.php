<?php

$type = strtolower(trim((string)($_GET['type'] ?? 'patients')));
if (!in_array($type, ['patients', 'suppliers'], true)) {
    $type = 'patients';
}

$selectedContactId = trim((string)($_GET['with'] ?? ''));
$flash = trim((string)($_GET['msg'] ?? ''));
$isAjax = Request::expectsJson();
$pickDefaultContactId = static function (array $contacts, string $contactId): string {
    if ($contactId !== '' || empty($contacts)) {
        return $contactId;
    }
    return (string) ($contacts[0]['id'] ?? '');
};
$sumUnread = static function (array $contacts): int {
    $total = 0;
    foreach ($contacts as $contact) {
        $total += (int) ($contact['unread'] ?? 0);
    }
    return $total;
};

if (Request::isGet() && (string)($_GET['action'] ?? '') === 'fetch') {
    $contacts = PharmacistMessagesModel::getContacts($type);
    $selectedContactId = $pickDefaultContactId($contacts, $selectedContactId);

    $messages = [];
    if ($selectedContactId !== '') {
        $messages = PharmacistMessagesModel::getMessages($selectedContactId, $type);
        PharmacistMessagesModel::markAsRead($selectedContactId, $type);
    }

    $contacts = PharmacistMessagesModel::getContacts($type);
    $unreadTotal = $sumUnread($contacts);

    Response::json([
        'ok' => true,
        'type' => $type,
        'with' => $selectedContactId,
        'messages' => $messages,
        'contacts' => $contacts,
        'unreadTotal' => $unreadTotal,
        'timestamp' => time(),
    ]);
}

if (Request::isPost()) {
    $postedType = strtolower(trim((string)($_POST['type'] ?? $type)));
    if (!in_array($postedType, ['patients', 'suppliers'], true)) {
        $postedType = 'patients';
    }

    $postedContact = trim((string)($_POST['contact_id'] ?? ''));
    $text = trim((string)($_POST['message'] ?? ''));
    $sent = PharmacistMessagesModel::sendMessage((int)($user['id'] ?? 0), $postedContact, $text, $postedType);

    if ($isAjax) {
        $messages = [];
        if ($postedContact !== '') {
            $messages = PharmacistMessagesModel::getMessages($postedContact, $postedType);
            PharmacistMessagesModel::markAsRead($postedContact, $postedType);
        }

        $contacts = PharmacistMessagesModel::getContacts($postedType);
        $unreadTotal = $sumUnread($contacts);

        Response::json([
            'ok' => (bool)$sent,
            'type' => $postedType,
            'with' => $postedContact,
            'messages' => $messages,
            'contacts' => $contacts,
            'unreadTotal' => $unreadTotal,
            'timestamp' => time(),
        ]);
    }

    $query = http_build_query([
        'type' => $postedType,
        'with' => $postedContact,
        'msg' => $sent ? 'sent' : 'failed',
    ]);
    Response::redirect('/pharmacist/messages?' . $query);
}

$contacts = PharmacistMessagesModel::getContacts($type);
$selectedContactId = $pickDefaultContactId($contacts, $selectedContactId);

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

$unreadTotal = $sumUnread($contacts);

$data = [
    'type' => $type,
    'contacts' => $contacts,
    'selectedContactId' => $selectedContactId,
    'selectedContact' => $selectedContact,
    'messages' => $messages,
    'unreadTotal' => $unreadTotal,
    'flash' => $flash,
    'hasChatTable' => true,
    'greeting' => (function (): string {
        $hour = (int)date('H');
        if ($hour < 12) return 'Good Morning';
        if ($hour < 18) return 'Good Afternoon';
        return 'Good Evening';
    })(),
    'currentDate' => date('d F Y'),
    'currentTime' => date('H:i:s'),
];
