<?php

class CounselorClientsModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $pid = (int)($auth['pharmacy_id'] ?? 0);
        if ($pid > 0) return $pid;
        return PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
    }

    public static function getAll(string $search = ''): array
    {
        Database::setUpConnection();

        $whereParts = [];
        if ($search !== '') {
            $safe = Database::escape($search);
            $whereParts[] = "(nic LIKE '%$safe%' OR name LIKE '%$safe%' OR email LIKE '%$safe%')";
        }

        if (PharmacyContext::tableHasPharmacyId('patient') && self::currentPharmacyId() > 0) {
            $whereParts[] = "pharmacy_id = " . self::currentPharmacyId();
        }

        $where = !empty($whereParts) ? ("WHERE " . implode(' AND ', $whereParts)) : '';

        $rs = Database::search("
            SELECT nic, name, email, emergency_contact
            FROM patient
            $where
            ORDER BY name ASC
        ");

        $rows = [];
        if ($rs instanceof mysqli_result) {
            while ($row = $rs->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
