<?php
$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pharmacies | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css">
    <style>
        .main{padding:22px;} .card{background:#fff;border-radius:10px;padding:16px;box-shadow:0 8px 20px rgba(0,0,0,.06);margin-bottom:16px;}
        .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;} .grid input{height:38px;padding:0 10px;border:1px solid #cfd7e5;border-radius:8px;}
        .btn{height:38px;border:none;border-radius:8px;background:#1b5ecf;color:#fff;padding:0 14px;cursor:pointer;} table{width:100%;border-collapse:collapse;} th,td{padding:10px;border-bottom:1px solid #eef2f7;text-align:left;}
    </style>
</head>
<body class="admin-body">
<main class="main">
    <h1>Pharmacies</h1>
    <?php if (!empty($error)): ?><p style="color:#c62828;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <section class="card">
        <h3>Add Pharmacy</h3>
        <form method="post" class="grid">
            <input type="hidden" name="action" value="create">
            <input name="name" placeholder="Pharmacy name" required>
            <input name="address_line1" placeholder="Address" required>
            <input name="city" placeholder="City" required>
            <input name="district" placeholder="District">
            <input name="latitude" placeholder="Latitude" required>
            <input name="longitude" placeholder="Longitude" required>
            <input name="phone" placeholder="Phone">
            <input name="email" placeholder="Email">
            <button class="btn" type="submit">Create Pharmacy</button>
        </form>
    </section>

    <section class="card">
        <h3>All Pharmacies</h3>
        <table>
            <thead><tr><th>Name</th><th>Address</th><th>City</th><th>Lat/Lng</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($pharmacies as $p): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$p['name']) ?></td>
                    <td><?= htmlspecialchars((string)$p['address_line1']) ?></td>
                    <td><?= htmlspecialchars((string)$p['city']) ?></td>
                    <td><?= htmlspecialchars((string)$p['latitude']) ?>, <?= htmlspecialchars((string)$p['longitude']) ?></td>
                    <td><?= htmlspecialchars((string)$p['status']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="btn" type="submit">Toggle</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>