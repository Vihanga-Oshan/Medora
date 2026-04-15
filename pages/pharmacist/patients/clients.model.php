<?php

class PatientsClientsModel
{
    public static function getAll(string $search = '', int $currentPharmacyId = 0): array
    {
        $search = trim($search);
        $currentPharmacyId = max(0, $currentPharmacyId);

        $sql = "
            SELECT p.nic, p.name, p.email, p.emergency_contact
            FROM patient p
        ";
        $types = '';
        $params = [];
        $where = [];

        if ($currentPharmacyId > 0 && PharmacyContext::tableExists('patient_pharmacy_selection')) {
            $sql .= "
                INNER JOIN patient_pharmacy_selection pps
                    ON pps.patient_nic = p.nic
                   AND pps.is_active = 1
                   AND pps.pharmacy_id = ?
            ";
            $types .= 'i';
            $params[] = $currentPharmacyId;
        }

        if ($search !== '') {
            $where[] = '(p.nic LIKE ? OR p.name LIKE ? OR p.email LIKE ?)';
            $like = '%' . $search . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.name ASC, p.nic ASC';

        return Database::fetchAll($sql, $types, $params);
    }
}
