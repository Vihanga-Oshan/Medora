<?php
$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Pharmacy | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/common.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/auth.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        body { background:#eef3fb; margin:0; font-family:'Montserrat',sans-serif; }
        .wrap { max-width:1200px; margin:24px auto; padding:0 16px; display:grid; grid-template-columns: 380px 1fr; gap:16px; }
        .panel { background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.08); padding:16px; }
        .map { height:640px; border-radius:12px; overflow:hidden; }
        .list { max-height:560px; overflow:auto; display:flex; flex-direction:column; gap:10px; margin-top:12px; }
        .item { border:1px solid #d9e1ee; border-radius:10px; padding:12px; }
        .item h4 { margin:0 0 4px 0; font-size:16px; }
        .item p { margin:0 0 10px 0; color:#60708a; font-size:13px; }
        .btn { width:100%; height:38px; border:none; border-radius:8px; background:#1b5ecf; color:#fff; cursor:pointer; }
        .btn.secondary { background:#e7eefc; color:#1b5ecf; }
        .toolbar { display:flex; justify-content:space-between; align-items:center; gap:10px; }
        .status { color:#60708a; font-size:13px; }
        @media (max-width: 980px){ .wrap { grid-template-columns:1fr; } .map{height:420px;} }
    </style>
</head>
<body>
<div class="wrap">
    <section class="panel">
        <div class="toolbar">
            <h3 style="margin:0;">Choose Your Pharmacy</h3>
            <span class="status" id="geoStatus">Locating...</span>
        </div>

        <?php if (!empty($error)): ?>
            <p style="color:#c62828;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (!empty($notice)): ?>
            <p style="color:#2e7d32;"><?= htmlspecialchars($notice) ?></p>
        <?php endif; ?>

        <form method="post" id="autoSelectForm" style="margin:10px 0 12px;">
            <input type="hidden" name="action" value="auto_select">
            <input type="hidden" name="user_lat" id="userLatInput" value="">
            <input type="hidden" name="user_lng" id="userLngInput" value="">
            <button type="submit" class="btn">Auto-select nearest pharmacy</button>
        </form>

        <div class="list" id="pharmacyList">
            <?php foreach ($pharmacies as $p): ?>
                <form method="post" class="item" data-lat="<?= htmlspecialchars((string)$p['latitude']) ?>" data-lng="<?= htmlspecialchars((string)$p['longitude']) ?>" data-id="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="action" value="select">
                    <h4><?= htmlspecialchars((string)$p['name']) ?></h4>
                    <p><?= htmlspecialchars((string)($p['address_line1'] ?? '')) ?>, <?= htmlspecialchars((string)($p['city'] ?? '')) ?></p>
                    <?php if ((int)($p['is_demo'] ?? 0) === 1): ?>
                        <p style="color:#1b5ecf;font-size:12px;margin:0 0 8px 0;">Demo branch</p>
                    <?php endif; ?>
                    <input type="hidden" name="pharmacy_id" value="<?= (int)$p['id'] ?>">
                    <button class="btn <?= ((int)$selectedPharmacyId === (int)$p['id']) ? 'secondary' : '' ?>" type="submit">
                        <?= ((int)$selectedPharmacyId === (int)$p['id']) ? 'Selected' : 'Select Pharmacy' ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel map">
        <div id="map" style="height:100%;"></div>
    </section>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
const pharmacies = <?= json_encode(array_values(array_map(static function($p){
    return [
        'id' => (int)$p['id'],
        'name' => (string)$p['name'],
        'address' => trim(((string)($p['address_line1'] ?? '')) . ', ' . ((string)($p['city'] ?? ''))),
        'lat' => (float)($p['latitude'] ?? 0),
        'lng' => (float)($p['longitude'] ?? 0),
    ];
}, $pharmacies)), JSON_UNESCAPED_SLASHES) ?>;

const map = L.map('map').setView([6.9271, 79.8612], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const byId = {};
for (const p of pharmacies) {
    if (!p.lat || !p.lng) continue;
    const m = L.marker([p.lat, p.lng]).addTo(map)
      .bindPopup(`<b>${p.name}</b><br>${p.address}`);
    byId[p.id] = m;
}

if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition((pos) => {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;
    document.getElementById('userLatInput').value = lat;
    document.getElementById('userLngInput').value = lng;
    L.circleMarker([lat, lng], { radius: 7, color: '#0b8457' }).addTo(map).bindPopup('Your location');
    map.setView([lat, lng], 12);
    document.getElementById('geoStatus').textContent = 'Location detected';
  }, () => {
    document.getElementById('geoStatus').textContent = 'Location unavailable';
  });
} else {
  document.getElementById('geoStatus').textContent = 'Geolocation unsupported';
}

document.querySelectorAll('.item').forEach((el) => {
  el.addEventListener('mouseenter', () => {
    const id = Number(el.dataset.id || 0);
    if (byId[id]) {
      byId[id].openPopup();
      map.panTo(byId[id].getLatLng());
    }
  });
});
</script>
</body>
</html>
