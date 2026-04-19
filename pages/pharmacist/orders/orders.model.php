<?php
require_once ROOT . '/core/PharmacyOrderSupport.php';

class PharmacistOrdersModel
{
    private static function currentPharmacyId(): int
    {
        $auth = Auth::getUser();
        $fromToken = (int) ($auth['pharmacy_id'] ?? 0);
        if ($fromToken > 0) {
            return $fromToken;
        }

        return PharmacyContext::resolvePharmacistPharmacyId((int) ($auth['id'] ?? 0));
    }

    public static function getOrders(int $limit = 100): array
    {
        return PharmacyOrderSupport::getPharmacyOrders(self::currentPharmacyId(), $limit);
    }

    public static function getCompletedOrders(int $limit = 100): array
    {
        return PharmacyOrderSupport::getPharmacyCompletedOrders(self::currentPharmacyId(), $limit);
    }

    public static function updateStatus(int $orderId, string $status, string $notes = ''): bool
    {
        return PharmacyOrderSupport::updateOrderStatus($orderId, self::currentPharmacyId(), $status, $notes);
    }
}
