<?php $base = APP_BASE ?: ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Assignments | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css">
    <style>
        .main{padding:22px;} .card{background:#fff;border-radius:10px;padding:16px;box-shadow:0 8px 20px rgba(0,0,0,.06);margin-bottom:16px;}
        .grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;} select,input{height:38px;padding:0 10px;border:1px solid #cfd7e5;border-radius:8px;}
        .btn{height:38px;border:none;border-radius:8px;background:#1b5ecf;color:#fff;padding:0 14px;cursor:pointer;} table{width:100%;border-collapse:collapse;} th,td{padding:10px;border-bottom:1px solid #eef2f7;text-align:left;}
    </style>
</head>
<body class="admin-body">
<main class="main">
    <h1>Pharmacy Assignments</h1>
    <?php if (!empty($error)): ?><p style="color:#c62828;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <section class="card">
        <h3>Assign Pharmacist</h3>
        <form method="post" class="grid">
            <input type="hidden" name="action" value="assign">
            <select name="pharmacy_id" required>
                <option value="">Select pharmacy</option>
                <?php foreach ($pharmacies as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars((string)$p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="pharmacist_id" required>
                <option value="">Select pharmacist</option>
                <?php foreach ($pharmacists as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars((string)$p['name']) ?> (<?= htmlspecialchars((string)$p['email']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <select name="is_primary">
                <option value="1">Primary</option>
                <option value="0">Secondary</option>
            </select>
            <button class="btn" type="submit">Save Assignment</button>
        </form>
    </section>

    <section class="card">
        <h3>Existing Assignments</h3>
        <table>
            <thead><tr><th>Pharmacist</th><th>Pharmacy</th><th>Role</th><th>Primary</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td><?= htmlspecialchars((string)($a['pharmacist_name'] ?? '')) ?></td>
                    <td><?= htmlspecialchars((string)($a['pharmacy_name'] ?? '')) ?></td>
                    <td><?= htmlspecialchars((string)($a['role'] ?? 'pharmacist')) ?></td>
                    <td><?= ((int)($a['is_primary'] ?? 0) === 1) ? 'Yes' : 'No' ?></td>
                    <td><?= htmlspecialchars((string)($a['status'] ?? 'active')) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="deactivate">
                            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                            <button class="btn" type="submit">Deactivate</button>
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