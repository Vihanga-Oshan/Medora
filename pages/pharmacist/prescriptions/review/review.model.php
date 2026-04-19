<?php
/**
 * Prescription Review Model
 */
require_once ROOT . '/core/PharmacyOrderSupport.php';

class ReviewModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0)
            return $fromToken;
        return PharmacyContext::resolvePharmacistPharmacyId((int) ($auth['id'] ?? 0));
    }

    public static function getPrescriptionById(int $id): ?array
    {
        PharmacyOrderSupport::ensureSchema();
        $sql = "SELECT * FROM prescriptions WHERE id = ?";
        $types = 'i';
        $params = [$id];
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $sql .= " AND pharmacy_id = ?";
            $types .= 'i';
            $params[] = self::currentPharmacyId();
        }
        return Database::fetchOne($sql . " LIMIT 1", $types, $params);
    }

    public static function getPatientByNic(string $nic): ?array
    {
        return Database::fetchOne("SELECT * FROM patient WHERE nic = ? LIMIT 1", 's', [$nic]);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $status = strtoupper($status);
        if (!in_array($status, ['APPROVED', 'REJECTED']))
            return false;

        $sql = "UPDATE prescriptions SET status = ? WHERE id = ?";
        $types = 'si';
        $params = [$status, $id];
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $sql .= " AND pharmacy_id = ?";
            $types .= 'i';
            $params[] = self::currentPharmacyId();
        }
        $ok = Database::execute($sql, $types, $params);
        if ($ok) {
            PharmacyOrderSupport::syncPrescriptionOrderStatus($id, $status);
        }
        return $ok;
    }

    public static function getOrderByPrescriptionId(int $prescriptionId): ?array
    {
        return PharmacyOrderSupport::getPrescriptionOrder($prescriptionId);
    }

    public static function createNotification(string $nic, string $message, string $type = 'PRESCRIPTION'): bool
    {
        $pharmacyId = self::currentPharmacyId();
        if (PharmacyContext::tableHasPharmacyId('notifications') && $pharmacyId > 0) {
            return Database::execute(
                "INSERT INTO notifications (patient_nic, message, type, is_read, created_at, pharmacy_id)
                 VALUES (?, ?, ?, 0, NOW(), ?)",
                'sssi',
                [$nic, $message, $type, $pharmacyId]
            );
        }

        return Database::execute(
            "INSERT INTO notifications (patient_nic, message, type, is_read, created_at)
             VALUES (?, ?, ?, 0, NOW())",
            'sss',
            [$nic, $message, $type]
        );
    }
}
