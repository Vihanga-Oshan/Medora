<?php
require_once __DIR__ . '/pharmacies.model.php';
require_once __DIR__ . '/../common/admin.activity.php';

$base = APP_BASE ?: '';
$error = null;

if (Request::isPost()) {
    $action = Request::post('action') ?? '';
    if ($action === 'create') {
        $ok = PharmaciesModel::create($_POST);
        if (!$ok) {
            $error = 'Unable to create pharmacy. Please check required fields.';
        } else {
            $pharmacyName = trim((string)($_POST['name'] ?? 'Pharmacy'));
            if ($pharmacyName !== '') {
                AdminActivityLog::log($user, "Created pharmacy {$pharmacyName}", 'green', $user['name'] ?? 'Admin', 'pharmacy');
            }
            Response::redirect('/admin/pharmacies');
        }
    }

    if ($action === 'delete') {
        $id = (int)(Request::post('id') ?? 0);
        $ok = PharmaciesModel::softDelete($id);
        if ($ok && $id > 0) {
            $row = Database::fetchOne("SELECT name FROM pharmacies WHERE id = ? LIMIT 1", 'i', [$id]);
            if ($row) {
                $name = trim((string)($row['name'] ?? 'Pharmacy'));
                AdminActivityLog::log($user, "Deleted pharmacy {$name}", 'red', $user['name'] ?? 'Admin', 'pharmacy', $id);
            }
        }
        Response::redirect('/admin/pharmacies');
    }
}

$allPharmacies = PharmaciesModel::all();
$cityFilter = trim((string) (Request::get('city') ?? ''));

$cityMap = [];
foreach ($allPharmacies as $pharmacyRow) {
    $cityName = trim((string) ($pharmacyRow['city'] ?? ''));
    if ($cityName === '') {
        continue;
    }
    $cityMap[strtolower($cityName)] = $cityName;
}
$cities = array_values($cityMap);
usort($cities, static fn(string $a, string $b): int => strcasecmp($a, $b));

$pharmacies = $allPharmacies;
if ($cityFilter !== '') {
    $pharmacies = array_values(array_filter(
        $allPharmacies,
        static fn(array $row): bool => strcasecmp(trim((string) ($row['city'] ?? '')), $cityFilter) === 0
    ));
}
