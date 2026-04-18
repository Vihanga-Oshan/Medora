<?php $base = APP_BASE ?: ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Requests | Medora Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/admin/admin-style.css?v=6">
</head>
<body class="admin-body">
<aside class="sidebar">
    <div class="logo">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Medora" onerror="this.style.display='none'">
        <span>Medora Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="<?= htmlspecialchars($base) ?>/admin/dashboard"><i>&#128202;</i> Dashboard</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacists"><i>&#128138;</i> Pharmacists</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacies"><i>&#127973;</i> Pharmacies</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/pharmacy-assignments"><i>&#128279;</i> Assignments</a></li>
        <li class="active"><a href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests"><i>&#128221;</i> Requests</a></li>
        <li><a href="<?= htmlspecialchars($base) ?>/admin/settings"><i>&#9881;</i> Settings</a></li>
    </ul>
        <div class="admin-profile js-admin-profile">
        <button type="button" class="admin-profile-trigger" aria-haspopup="true" aria-expanded="false">
            <div class="profile-icon">AD</div>
            <div class="profile-info">
                <div class="name"><?= htmlspecialchars($adminEmail ?? ($user['email'] ?? 'admin@medora.com')) ?></div>
            </div>
        </button>
        <div class="admin-profile-menu" role="menu" hidden>
            <div class="admin-profile-menu-email"><?= htmlspecialchars($adminEmail ?? 'admin@medora.com') ?></div>
            <form method="post" action="<?= htmlspecialchars($base) ?>/admin/logout">
                <button type="submit" class="admin-profile-menu-logout">Logout</button>
            </form>
        </div>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <span>&#128269;</span>
            <input id="admin-global-search" type="text" placeholder="Search this page..." autocomplete="off" />
        </div>
    </header>

    <section class="section-container">
        <div class="section-header">
            <div>
                <h1>Pharmacist Requests</h1>
                <p>Review, approve, and reject incoming pharmacist account requests</p>
            </div>
        </div>
        <?php if (!empty($error)): ?><p class="error-text"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <section class="panel-card requests-card">
            <div class="filter-pills">
                <a class="btn btn-small<?= $statusFilter === 'pending' ? ' btn-primary' : ' btn-muted' ?>" href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests?status=pending">Pending</a>
                <a class="btn btn-small<?= $statusFilter === 'approved' ? ' btn-primary' : ' btn-muted' ?>" href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests?status=approved">Approved</a>
                <a class="btn btn-small<?= $statusFilter === 'rejected' ? ' btn-primary' : ' btn-muted' ?>" href="<?= htmlspecialchars($base) ?>/admin/pharmacist-requests?status=rejected">Rejected</a>
            </div>

            <table class="data-table">
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
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <button class="btn btn-primary btn-small" type="submit">Approve</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <input type="hidden" name="note" value="Rejected by admin">
                                    <button class="btn btn-danger btn-small" type="submit">Reject</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </section>
</main>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-search.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/admin/admin-profile-menu.js?v=6"></script>
</body>
</html>





