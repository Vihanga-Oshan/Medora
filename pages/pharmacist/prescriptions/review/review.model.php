<?php
/**
 * Prescription Review Model
 */
class ReviewModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int)($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) return $fromToken;
        return PharmacyContext::resolvePharmacistPharmacyId((int)($auth['id'] ?? 0));
    }

    private static function tableExists(string $name): bool
    {
        $safe = Database::escape($name);
        $rs = Database::search("SHOW TABLES LIKE '$safe'");
        return $rs instanceof mysqli_result && $rs->num_rows > 0;
    }

    public static function getPrescriptionById(int $id): ?array
    {
        $where = ["id = $id"];
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $where[] = "pharmacy_id = " . self::currentPharmacyId();
        }
        $rs = Database::search("SELECT * FROM prescriptions WHERE " . implode(' AND ', $where) . " LIMIT 1");
        return $rs ? $rs->fetch_assoc() : null;
    }

    public static function getPatientByNic(string $nic): ?array
    {
        Database::setUpConnection();
        $nic = Database::$connection->real_escape_string($nic);
        $table = self::tableExists('patient') ? 'patient' : (self::tableExists('patients') ? 'patients' : '');
        if ($table === '') {
            return null;
        }

        $rs = Database::search("SELECT * FROM `$table` WHERE nic = '$nic' LIMIT 1");
        return $rs ? $rs->fetch_assoc() : null;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $status = strtoupper($status);
        if (!in_array($status, ['APPROVED', 'REJECTED'])) return false;
        
        $where = ["id = $id"];
        if (PharmacyContext::tableHasPharmacyId('prescriptions') && self::currentPharmacyId() > 0) {
            $where[] = "pharmacy_id = " . self::currentPharmacyId();
        }
        return Database::iud("UPDATE prescriptions SET status = '$status' WHERE " . implode(' AND ', $where));
    }

    public static function createNotification(string $nic, string $message, string $type = 'PRESCRIPTION'): bool
    {
        Database::setUpConnection();
        $nic     = Database::$connection->real_escape_string($nic);
        $message = Database::$connection->real_escape_string($message);
        $type    = Database::$connection->real_escape_string($type);
        
        $cols = ['patient_nic', 'message', 'type', 'is_read', 'created_at'];
        $vals = ["'$nic'", "'$message'", "'$type'", '0', 'NOW()'];
        if (PharmacyContext::tableHasPharmacyId('notifications') && self::currentPharmacyId() > 0) {
            $cols[] = 'pharmacy_id';
            $vals[] = (string)self::currentPharmacyId();
        }
        return Database::iud("INSERT INTO notifications (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")");
    }
}
