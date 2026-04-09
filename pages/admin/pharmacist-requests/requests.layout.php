<?php $base = APP_BASE ?: ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Requests | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css">
    <style>
        .main{padding:22px;} .card{background:#fff;border-radius:10px;padding:16px;box-shadow:0 8px 20px rgba(0,0,0,.06);margin-bottom:16px;}
        table{width:100%;border-collapse:collapse;} th,td{padding:10px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:top;}
        .btn{height:34px;border:none;border-radius:8px;background:#1b5ecf;color:#fff;padding:0 12px;cursor:pointer;} .btn.red{background:#c62828;}
    </style>
</head>
<body class="admin-body">
<main class="main">
    <h1>Pharmacist Requests</h1>
    <?php if (!empty($error)): ?><p style="color:#c62828;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <section class="card">
        <div style="margin-bottom:12px;display:flex;gap:8px;">
            <a class="btn" href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests?status=pending" style="text-decoration:none;line-height:34px;">Pending</a>
            <a class="btn" href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests?status=approved" style="text-decoration:none;line-height:34px;">Approved</a>
            <a class="btn" href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests?status=rejected" style="text-decoration:none;line-height:34px;">Rejected</a>
        </div>

        <table>
            <thead><tr><th>Name</th><th>Email</th><th>License</th><th>Requested Pharmacy</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($requests)): ?>
                <tr><td colspan="6">No requests found.</td></tr>
            <?php endif; ?>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$r['full_name']) ?></td>
                    <td><?= htmlspecialchars((string)$r['email']) ?><br><small><?= htmlspecialchars((string)($r['phone'] ?? '')) ?></small></td>
                    <td><?= htmlspecialchars((string)$r['license_no']) ?></td>
                    <td><?= htmlspecialchars((string)($r['pharmacy_name'] ?? 'Any')) ?></td>
                    <td><?= htmlspecialchars((string)$r['status']) ?><br><small><?= htmlspecialchars((string)($r['note'] ?? '')) ?></small></td>
                    <td>
                        <?php if (($r['status'] ?? '') === 'pending'): ?>
                            <form method="post" style="display:inline; margin-right:6px;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_pharmacist_requests_action')) ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                <button class="btn" type="submit">Approve</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('admin_pharmacist_requests_action')) ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="note" value="Rejected by admin">
                                <button class="btn red" type="submit">Reject</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
