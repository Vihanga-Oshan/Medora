<?php
/**
 * Session-backed state for patient e-shop:
 * - cart items
 * - recently viewed medicine IDs
 */

if (!function_exists('shopEnsureSession')) {
    function shopEnsureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}

if (!function_exists('shopGetCart')) {
    function shopGetCart(): array
    {
        shopEnsureSession();
        $cart = $_SESSION['patient_shop_cart'] ?? [];
        return is_array($cart) ? $cart : [];
    }
}

if (!function_exists('shopSetCart')) {
    function shopSetCart(array $cart): void
    {
        shopEnsureSession();
        $_SESSION['patient_shop_cart'] = $cart;
    }
}

if (!function_exists('shopAddToCart')) {
    function shopAddToCart(int $medicineId, int $qty = 1): void
    {
        if ($medicineId <= 0 || $qty <= 0) {
            return;
        }
        $cart = shopGetCart();
        $key = (string)$medicineId;
        $cart[$key] = (int)($cart[$key] ?? 0) + $qty;
        if ($cart[$key] < 1) {
            $cart[$key] = 1;
        }
        shopSetCart($cart);
    }
}

if (!function_exists('shopRemoveFromCart')) {
    function shopRemoveFromCart(int $medicineId): void
    {
        if ($medicineId <= 0) {
            return;
        }
        $cart = shopGetCart();
        unset($cart[(string)$medicineId]);
        shopSetCart($cart);
    }
}

if (!function_exists('shopCartCount')) {
    function shopCartCount(): int
    {
        $cart = shopGetCart();
        return count($cart);
    }
}

if (!function_exists('shopTrackRecentlyViewed')) {
    function shopTrackRecentlyViewed(int $medicineId): void
    {
        if ($medicineId <= 0) {
            return;
        }
        shopEnsureSession();
        $recent = $_SESSION['patient_shop_recent'] ?? [];
        if (!is_array($recent)) {
            $recent = [];
        }

        $id = (string)$medicineId;
        $recent = array_values(array_filter($recent, static fn($x) => (string)$x !== $id));
        array_unshift($recent, $id);
        $recent = array_slice($recent, 0, 8);
        $_SESSION['patient_shop_recent'] = $recent;
    }
}

if (!function_exists('shopGetRecentlyViewedIds')) {
    function shopGetRecentlyViewedIds(): array
    {
        shopEnsureSession();
        $recent = $_SESSION['patient_shop_recent'] ?? [];
        if (!is_array($recent)) {
            return [];
        }
        $ids = [];
        foreach ($recent as $id) {
            $num = (int)$id;
            if ($num > 0) {
                $ids[] = $num;
            }
        }
        return array_values(array_unique($ids));
    }
}

if (!function_exists('shopSetFlash')) {
    function shopSetFlash(string $message, string $type = 'success'): void
    {
        shopEnsureSession();
        $_SESSION['patient_shop_flash'] = [
            'message' => $message,
            'type' => $type,
        ];
    }
}

if (!function_exists('shopPopFlash')) {
    function shopPopFlash(): ?array
    {
        shopEnsureSession();
        $flash = $_SESSION['patient_shop_flash'] ?? null;
        unset($_SESSION['patient_shop_flash']);
        return is_array($flash) ? $flash : null;
    }
}
