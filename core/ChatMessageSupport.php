<?php

class ChatMessageSupport
{
    private static ?array $columns = null;
    private static array $lastDebugLogAt = [];

    public static function columns(): array
    {
        if (self::$columns !== null) {
            return self::$columns;
        }

        $cols = [];
        Database::setUpConnection();
        $result = Database::$connection->query("SHOW COLUMNS FROM chat_messages");
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $name = strtolower(trim((string) ($row['Field'] ?? '')));
                if ($name !== '') {
                    $cols[$name] = true;
                }
            }
        }

        if (empty($cols)) {
            $cols = array_fill_keys([
                'id',
                'sender_type',
                'sender_id',
                'receiver_id',
                'message_text',
                'sent_at',
                'is_read',
                'typing',
                'type',
                'created_at',
                'pharmacy_id',
            ], true);
        }

        self::$columns = $cols;
        return self::$columns;
    }

    public static function hasColumn(string $column): bool
    {
        return isset(self::columns()[strtolower($column)]);
    }

    private static function qualify(string $column, string $alias = ''): string
    {
        return $alias !== '' ? $alias . '.' . $column : $column;
    }

    public static function firstExistingColumn(array $candidates, string $alias = ''): ?string
    {
        foreach ($candidates as $candidate) {
            if (self::hasColumn($candidate)) {
                return self::qualify($candidate, $alias);
            }
        }
        return null;
    }

    public static function participantExpr(array $candidates, string $alias = ''): ?string
    {
        $present = [];
        foreach ($candidates as $candidate) {
            if (self::hasColumn($candidate)) {
                $present[] = self::qualify($candidate, $alias);
            }
        }

        if (empty($present)) {
            return null;
        }
        if (count($present) === 1) {
            return $present[0];
        }

        $parts = [];
        foreach ($present as $col) {
            $parts[] = "NULLIF($col, '')";
        }
        return "COALESCE(" . implode(', ', $parts) . ")";
    }

    public static function messageSelectSql(string $alias = ''): string
    {
        $idCol = self::firstExistingColumn(['id'], $alias) ?? self::qualify('id', $alias);
        $senderTypeCol = self::firstExistingColumn(['sender_type', 'type', 'typing'], $alias);
        $messageCol = self::firstExistingColumn(['message_text', 'message'], $alias);
        $sentAtCol = self::firstExistingColumn(['sent_at', 'created_at'], $alias);
        $isReadCol = self::firstExistingColumn(['is_read'], $alias);

        return implode(', ', [
            $idCol . ' AS id',
            ($senderTypeCol ?: "''") . ' AS sender_type',
            ($messageCol ?: "''") . ' AS message_text',
            ($sentAtCol ?: "''") . ' AS sent_at',
            ($isReadCol ?: '0') . ' AS is_read',
        ]);
    }

    public static function threadSelectSql(string $alias = ''): string
    {
        $senderExpr = self::participantExpr(['sender_id', 'sender'], $alias);
        $receiverExpr = self::participantExpr(['receiver_id', 'receiver'], $alias);

        return implode(', ', [
            self::messageSelectSql($alias),
            ($senderExpr ?: "''") . ' AS sender_id',
            ($receiverExpr ?: "''") . ' AS receiver_id',
        ]);
    }

    public static function buildInsertParts(array $payload): array
    {
        $columns = [];
        $values = [];
        $types = '';
        $params = [];

        $addParam = static function (string $column, string $type, $value) use (&$columns, &$values, &$types, &$params): void {
            $columns[] = $column;
            $values[] = '?';
            $types .= $type;
            $params[] = $value;
        };

        if (self::hasColumn('sender_type') && isset($payload['sender_type'])) {
            $addParam('sender_type', 's', (string) $payload['sender_type']);
        }
        if (self::hasColumn('typing') && isset($payload['typing'])) {
            $addParam('typing', 's', (string) $payload['typing']);
        }
        if (self::hasColumn('type') && isset($payload['type'])) {
            $addParam('type', 's', (string) $payload['type']);
        }

        if (self::hasColumn('sender_id') && array_key_exists('sender_id', $payload)) {
            $addParam('sender_id', 's', (string) $payload['sender_id']);
        }
        if (self::hasColumn('sender') && array_key_exists('sender', $payload)) {
            $addParam('sender', 's', (string) $payload['sender']);
        }

        if (self::hasColumn('receiver_id') && array_key_exists('receiver_id', $payload)) {
            $addParam('receiver_id', 's', (string) $payload['receiver_id']);
        }
        if (self::hasColumn('receiver') && array_key_exists('receiver', $payload)) {
            $addParam('receiver', 's', (string) $payload['receiver']);
        }

        if (self::hasColumn('message_text') && isset($payload['message_text'])) {
            $addParam('message_text', 's', (string) $payload['message_text']);
        } elseif (self::hasColumn('message') && isset($payload['message'])) {
            $addParam('message', 's', (string) $payload['message']);
        }

        if (self::hasColumn('sent_at')) {
            $columns[] = 'sent_at';
            $values[] = 'NOW()';
        } elseif (self::hasColumn('created_at')) {
            $columns[] = 'created_at';
            $values[] = 'NOW()';
        }

        if (self::hasColumn('is_read')) {
            $columns[] = 'is_read';
            $values[] = '0';
        }

        if (self::hasColumn('pharmacy_id') && !empty($payload['pharmacy_id'])) {
            $addParam('pharmacy_id', 'i', (int) $payload['pharmacy_id']);
        }

        return [
            'columns' => $columns,
            'values' => $values,
            'types' => $types,
            'params' => $params,
        ];
    }

    public static function fetchAllTimed(string $sql, string $types = '', array $params = []): array
    {
        $startedAt = microtime(true);
        $rows = Database::fetchAll($sql, $types, $params);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $dbError = Database::$connection->error ?? '';

        return [
            'rows' => $rows,
            'duration_ms' => $durationMs,
            'db_error' => $dbError,
            'failed' => empty($rows) && $dbError !== '',
        ];
    }

    public static function shouldWriteDebug(string $key, int $intervalSeconds = 20): bool
    {
        $now = time();
        $lastAt = self::$lastDebugLogAt[$key] ?? null;

        if ($lastAt === null || ($now - $lastAt) >= $intervalSeconds) {
            self::$lastDebugLogAt[$key] = $now;
            return true;
        }

        return false;
    }

    public static function mapPatientThreadRows(array $rows): array
    {
        $messages = [];
        $pharmacistCount = 0;

        foreach ($rows as $row) {
            $senderType = (string) ($row['sender_type'] ?? '');
            if (strtolower(trim($senderType)) === 'pharmacist') {
                $pharmacistCount++;
            }

            $messages[] = [
                'id' => (int) ($row['id'] ?? 0),
                'senderType' => $senderType,
                'text' => (string) ($row['message_text'] ?? ''),
                'sentAt' => (string) ($row['sent_at'] ?? ''),
            ];
        }

        return [
            'rows' => $messages,
            'pharmacist_count' => $pharmacistCount,
        ];
    }

    public static function mapPharmacistThreadRows(array $rows): array
    {
        $messages = [];

        foreach ($rows as $row) {
            $messages[] = [
                'id' => (int) ($row['id'] ?? 0),
                'senderType' => (string) ($row['sender_type'] ?? ''),
                'senderId' => (string) ($row['sender_id'] ?? ''),
                'receiverId' => (string) ($row['receiver_id'] ?? ''),
                'text' => (string) ($row['message_text'] ?? ''),
                'sentAt' => (string) ($row['sent_at'] ?? ''),
                'isRead' => (int) ($row['is_read'] ?? 0) === 1,
            ];
        }

        return $messages;
    }

    public static function mapContactRow(array $row): array
    {
        return [
            'id' => (string) ($row['contact_id'] ?? ''),
            'name' => (string) ($row['name'] ?? 'Unknown'),
            'lastMessage' => (string) ($row['message_text'] ?? ''),
            'lastMessageAt' => (string) ($row['sent_at'] ?? ''),
            'unread' => (int) ($row['unread_count'] ?? 0),
        ];
    }
}
