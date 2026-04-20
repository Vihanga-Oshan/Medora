<?php
require_once ROOT . '/core/PharmacyOrderSupport.php';
require_once ROOT . '/core/AppLogger.php';

class PharmacistOrdersModel
{
    private static function writeLog(string $level, string $message, array $context = []): void
    {
        AppLogger::write('pharmacist-orders-debug.log', $level, $message, $context);
    }

    private static function currentPharmacyId(): int
    {
        if (isset($GLOBALS['currentPharmacyId']) && (int) $GLOBALS['currentPharmacyId'] > 0) {
            self::writeLog('DEBUG', 'Using pharmacist pharmacy from page context.', [
                'current_pharmacy_id' => (int) $GLOBALS['currentPharmacyId'],
                'user_id' => (int) ($GLOBALS['user']['id'] ?? 0),
            ]);
            return (int) $GLOBALS['currentPharmacyId'];
        }

        $auth = Auth::getUser();
        $authId = (int) ($auth['id'] ?? 0);
        if ($authId > 0) {
            $resolved = PharmacyContext::resolvePharmacistPharmacyId($authId);
            if ($resolved > 0) {
                self::writeLog('DEBUG', 'Resolved pharmacist pharmacy from mapping.', [
                    'auth_id' => $authId,
                    'resolved_pharmacy_id' => $resolved,
                    'token_pharmacy_id' => (int) ($auth['pharmacy_id'] ?? 0),
                ]);
                return $resolved;
            }
        }

        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            self::writeLog('DEBUG', 'Using pharmacist pharmacy from token fallback.', [
                'auth_id' => $authId,
                'token_pharmacy_id' => $fromToken,
            ]);
            return $fromToken;
        }

        self::writeLog('ERROR', 'Unable to resolve pharmacist pharmacy.', [
            'auth_id' => $authId,
            'token_pharmacy_id' => (int) ($auth['pharmacy_id'] ?? 0),
        ]);
        return 0;
    }

    public static function getOrders(int $limit = 100): array
    {
        $pharmacyId = self::currentPharmacyId();
        $orders = PharmacyOrderSupport::getPharmacyOrders($pharmacyId, $limit);
        self::writeLog('DEBUG', 'Fetched pharmacist orders.', [
            'pharmacy_id' => $pharmacyId,
            'limit' => $limit,
            'order_count' => count($orders),
            'order_ids' => array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $orders),
        ]);
        return $orders;
    }

    public static function getCompletedOrders(int $limit = 100): array
    {
        $pharmacyId = self::currentPharmacyId();
        $orders = PharmacyOrderSupport::getPharmacyCompletedOrders($pharmacyId, $limit);
        self::writeLog('DEBUG', 'Fetched completed pharmacist orders.', [
            'pharmacy_id' => $pharmacyId,
            'limit' => $limit,
            'order_count' => count($orders),
            'order_ids' => array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $orders),
        ]);
        return $orders;
    }

    public static function updateStatus(int $orderId, string $status, string $notes = ''): bool
    {
        return PharmacyOrderSupport::updateOrderStatus($orderId, self::currentPharmacyId(), $status, $notes);
    }
}
