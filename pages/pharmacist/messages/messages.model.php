<?php

class PharmacistMessagesModel
{
    private static ?array $chatColumnsCache = null;
    private static ?int $pharmacyIdCache = null;

    private static function currentPharmacyId(): int
    {
        if (self::$pharmacyIdCache !== null) {
            return self::$pharmacyIdCache;
        }
        $auth = Auth::getUser();
        $fromToken = (int)($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            self::$pharmacyIdCache = $fromToken;
            return $fromToken;
        }
        self::$pharmacyIdCache = PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
        return self::$pharmacyIdCache;
    }

    private static function hasChatPharmacy(): bool
    {
        $cols = self::getChatColumns();
        return isset($cols['pharmacy_id']);
    }

    private static function tableExists(string $name): bool
    {
        $safe = Database::escape($name);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    private static function getChatColumns(): array
    {
        if (self::$chatColumnsCache !== null) {
            return self::$chatColumnsCache;
        }

        $cols = [];
        if (!self::tableExists('chat_messages')) {
            self::$chatColumnsCache = $cols;
            return $cols;
        }

        $rs = Database::search("SHOW COLUMNS FROM chat_messages");
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $field = strtolower((string)($row['Field'] ?? ''));
                if ($field !== '') {
                    $cols[$field] = true;
                }
            }
        }

        self::$chatColumnsCache = $cols;
        return $cols;
    }

    public static function canUseMessages(): bool
    {
        return self::tableExists('chat_messages');
    }

    public static function getContacts(string $type): array
    {
        Database::setUpConnection();

        if ($type === 'suppliers') {
            if (!self::tableExists('supplier')) {
                return [];
            }

            $sql = "
                SELECT
                    CAST(s.id AS CHAR) AS contact_id,
                    s.name,
                    m.message_text,
                    m.sent_at,
                    (
                        SELECT COUNT(*)
                        FROM chat_messages cm
                        WHERE cm.sender_type = 'supplier'
                          AND cm.sender_id = CAST(s.id AS CHAR)
                          AND cm.receiver_id = 'PHARMACIST'
                          AND cm.is_read = 0
                          " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND cm.pharmacy_id = " . self::currentPharmacyId() : '') . "
                    ) AS unread_count
                FROM supplier s
                LEFT JOIN chat_messages m ON m.id = (
                    SELECT MAX(m2.id)
                    FROM chat_messages m2
                    WHERE (m2.sender_type = 'supplier' AND m2.sender_id = CAST(s.id AS CHAR) AND m2.receiver_id = 'PHARMACIST')
                       OR (m2.sender_type = 'pharmacist' AND m2.receiver_id = CAST(s.id AS CHAR))
                    " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND m2.pharmacy_id = " . self::currentPharmacyId() : '') . "
                )
                ORDER BY CASE WHEN m.sent_at IS NULL THEN 1 ELSE 0 END, m.sent_at DESC, s.name ASC
            ";
        } else {
            if (!self::tableExists('patient')) {
                return [];
            }

            $sql = "
                SELECT
                    p.nic AS contact_id,
                    p.name,
                    m.message_text,
                    m.sent_at,
                    (
                        SELECT COUNT(*)
                        FROM chat_messages cm
                        WHERE cm.sender_id = p.nic
                          AND cm.receiver_id = 'PHARMACIST'
                          AND cm.is_read = 0
                          " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND cm.pharmacy_id = " . self::currentPharmacyId() : '') . "
                    ) AS unread_count
                FROM patient p
                LEFT JOIN chat_messages m ON m.id = (
                    SELECT MAX(m2.id)
                    FROM chat_messages m2
                    WHERE (m2.sender_id = p.nic AND m2.receiver_id = 'PHARMACIST')
                       OR (m2.sender_type = 'pharmacist' AND m2.receiver_id = p.nic)
                    " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND m2.pharmacy_id = " . self::currentPharmacyId() : '') . "
                )
                ORDER BY CASE WHEN m.sent_at IS NULL THEN 1 ELSE 0 END, m.sent_at DESC, p.name ASC
            ";
        }

        $rows = [];
        $rs = Database::search($sql);
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = [
                    'id' => (string)($row['contact_id'] ?? ''),
                    'name' => (string)($row['name'] ?? 'Unknown'),
                    'lastMessage' => (string)($row['message_text'] ?? ''),
                    'lastMessageAt' => (string)($row['sent_at'] ?? ''),
                    'unread' => (int)($row['unread_count'] ?? 0),
                ];
            }
        }

        return $rows;
    }

    public static function getMessages(string $contactId, string $type): array
    {
        if ($contactId === '' || !self::canUseMessages()) {
            return [];
        }

        $safeId = Database::escape($contactId);

        if ($type === 'suppliers') {
            $sql = "
                SELECT id, sender_type, sender_id, receiver_id, message_text, sent_at, is_read
                FROM chat_messages
                WHERE (
                    (sender_type = 'supplier' AND sender_id = '$safeId' AND receiver_id = 'PHARMACIST')
                    OR (sender_type = 'pharmacist' AND receiver_id = '$safeId')
                )
                " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND pharmacy_id = " . self::currentPharmacyId() : '') . "
                ORDER BY sent_at ASC, id ASC
                LIMIT 200
            ";
        } else {
            $sql = "
                SELECT id, sender_type, sender_id, receiver_id, message_text, sent_at, is_read
                FROM chat_messages
                WHERE (
                    (sender_id = '$safeId' AND receiver_id = 'PHARMACIST')
                    OR (sender_type = 'pharmacist' AND receiver_id = '$safeId')
                )
                " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND pharmacy_id = " . self::currentPharmacyId() : '') . "
                ORDER BY sent_at ASC, id ASC
                LIMIT 200
            ";
        }

        $rows = [];
        $rs = Database::search($sql);
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = [
                    'id' => (int)($row['id'] ?? 0),
                    'senderType' => (string)($row['sender_type'] ?? ''),
                    'senderId' => (string)($row['sender_id'] ?? ''),
                    'receiverId' => (string)($row['receiver_id'] ?? ''),
                    'text' => (string)($row['message_text'] ?? ''),
                    'sentAt' => (string)($row['sent_at'] ?? ''),
                    'isRead' => (int)($row['is_read'] ?? 0) === 1,
                ];
            }
        }

        return $rows;
    }

    public static function markAsRead(string $contactId, string $type): void
    {
        if ($contactId === '' || !self::canUseMessages()) {
            return;
        }

        $safeId = Database::escape($contactId);

        if ($type === 'suppliers') {
            Database::iud("
                UPDATE chat_messages
                SET is_read = 1
                WHERE receiver_id = 'PHARMACIST'
                  AND sender_type = 'supplier'
                  AND sender_id = '$safeId'
                  AND is_read = 0
                  " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND pharmacy_id = " . self::currentPharmacyId() : '') . "
            ");
            return;
        }

        Database::iud("
            UPDATE chat_messages
            SET is_read = 1
        WHERE receiver_id = 'PHARMACIST'
              AND sender_id = '$safeId'
              AND is_read = 0
              " . (self::hasChatPharmacy() && self::currentPharmacyId() > 0 ? "AND pharmacy_id = " . self::currentPharmacyId() : '') . "
        ");
    }

    public static function sendMessage(int $pharmacistId, string $contactId, string $message): bool
    {
        if ($contactId === '' || trim($message) === '' || !self::canUseMessages()) {
            return false;
        }

        $safeContact = Database::escape($contactId);
        $safeMessage = Database::escape(trim($message));
        $safeSenderId = Database::escape((string)$pharmacistId);
        $cols = self::getChatColumns();

        $insertCols = [];
        $insertVals = [];

        // Role/type column can be named differently in legacy schemas.
        if (isset($cols['sender_type'])) {
            $insertCols[] = 'sender_type';
            $insertVals[] = "'pharmacist'";
        }
        if (isset($cols['typing'])) {
            $insertCols[] = 'typing';
            $insertVals[] = "'pharmacist'";
        }
        if (isset($cols['type'])) {
            $insertCols[] = 'type';
            $insertVals[] = "'pharmacist'";
        }

        if (isset($cols['sender_id'])) {
            $insertCols[] = 'sender_id';
            $insertVals[] = "'$safeSenderId'";
        } elseif (isset($cols['sender'])) {
            $insertCols[] = 'sender';
            $insertVals[] = "'$safeSenderId'";
        }

        if (isset($cols['receiver_id'])) {
            $insertCols[] = 'receiver_id';
            $insertVals[] = "'$safeContact'";
        } elseif (isset($cols['receiver'])) {
            $insertCols[] = 'receiver';
            $insertVals[] = "'$safeContact'";
        }

        if (isset($cols['message_text'])) {
            $insertCols[] = 'message_text';
            $insertVals[] = "'$safeMessage'";
        } elseif (isset($cols['message'])) {
            $insertCols[] = 'message';
            $insertVals[] = "'$safeMessage'";
        }

        if (isset($cols['sent_at'])) {
            $insertCols[] = 'sent_at';
            $insertVals[] = 'NOW()';
        } elseif (isset($cols['created_at'])) {
            $insertCols[] = 'created_at';
            $insertVals[] = 'NOW()';
        }

        if (isset($cols['is_read'])) {
            $insertCols[] = 'is_read';
            $insertVals[] = '0';
        }
        if (isset($cols['pharmacy_id']) && self::currentPharmacyId() > 0) {
            $insertCols[] = 'pharmacy_id';
            $insertVals[] = (string)self::currentPharmacyId();
        }

        if (empty($insertCols)) {
            return false;
        }

        $sql = "INSERT INTO chat_messages (" . implode(', ', $insertCols) . ")
                VALUES (" . implode(', ', $insertVals) . ")";
        return Database::iud($sql);
    }
}
