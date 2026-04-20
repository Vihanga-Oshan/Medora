<?php
$error = null;
$notice = null;

function nearestGuestPharmacyId(array $pharmacies, float $userLat, float $userLng): int
{
    $bestId = 0;
    $bestDist = PHP_FLOAT_MAX;

    foreach ($pharmacies as $p) {
        $lat = (float) ($p['latitude'] ?? 0);
        $lng = (float) ($p['longitude'] ?? 0);
        if ($lat === 0.0 && $lng === 0.0) {
            continue;
        }

        $earth = 6371.0;
        $dLat = deg2rad($lat - $userLat);
        $dLng = deg2rad($lng - $userLng);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($userLat)) * cos(deg2rad($lat))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $dist = $earth * $c;

        if ($dist < $bestDist) {
            $bestDist = $dist;
            $bestId = (int) ($p['id'] ?? 0);
        }
    }

    return $bestId;
}

if (Request::isPost()) {
    $action = (string) (Request::post('action') ?? 'select');
    $pharmacyId = (int) (Request::post('pharmacy_id') ?? 0);
    $pharmacies = PharmacyContext::getPharmacies();

    if ($action === 'auto_select') {
        $lat = (float) (Request::post('user_lat') ?? 0);
        $lng = (float) (Request::post('user_lng') ?? 0);
        if ($lat !== 0.0 || $lng !== 0.0) {
            $pharmacyId = nearestGuestPharmacyId($pharmacies, $lat, $lng);
            if ($pharmacyId > 0) {
                $notice = 'Nearest pharmacy selected automatically.';
            }
        }
    }

    if ($pharmacyId <= 0) {
        $error = 'Please select a valid pharmacy.';
    } else {
        PharmacyContext::setSelectedPharmacyId($pharmacyId);
        Response::redirect('/shop');
    }
}

$pharmacies = PharmacyContext::getPharmacies();
$selectedPharmacyId = PharmacyContext::selectedPharmacyId();
