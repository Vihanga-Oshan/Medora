<?php $activePage = 'recovery'; ?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Recovery Plans'; $pageStyle = ['counselor/recoveryPlans']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>
    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="Main Content Head background" class="main-header-bg-image" />
        <div class="main-content-header"><div class="main-content-header-text"><h2>Recovery Plans</h2><p>View and manage your recovery plans</p></div></div>
        <div class="main-content-body">
            <div class="inner-body-content">
                <div class="plans-header"><h3>Created Plans</h3><a href="<?= htmlspecialchars($base) ?>/pharmacist/medication-plans/create"><button class="btn btn-primary" type="button">+ Create New Plan</button></a></div>
                <div class="plans-grid">
                    <?php if (empty($plans)): ?>
                        <div class="empty-state"><p>No recovery plans created yet.</p><p>Click "Create New Plan" to get started.</p></div>
                    <?php else: ?>
                        <?php foreach ($plans as $plan): ?>
                            <div class="plan-card">
                                <div class="plan-card-content">
                                    <h4 class="plan-title"><?= htmlspecialchars($plan['title']) ?></h4>
                                    <span class="plan-status status-<?= htmlspecialchars($plan['status']) ?>"><?= htmlspecialchars($plan['status']) ?></span>
                                    <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>
                                    <div class="plan-progress"><div class="progress-bar-small"><div class="progress-fill-small" style="width: <?= (int) $plan['progressPercentage'] ?>%;"></div></div><span class="progress-text"><?= (int) $plan['progressPercentage'] ?>%</span></div>
                                </div>
                                <div class="plan-card-actions">
                                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/medication-plans/view?planId=<?= (int) $plan['planId'] ?>"><button class="btn-view" type="button">View Plan</button></a>
                                    <a href="<?= htmlspecialchars($base) ?>/pharmacist/medication-plans/delete?planId=<?= (int) $plan['planId'] ?>" onclick="return confirm('Are you sure you want to delete this plan?');"><button class="btn-delete-plan" type="button">Delete</button></a>
                                </div>
                                <img src="<?= htmlspecialchars($base) ?>/assets/img/plan.png" alt="Plan" class="plan-image" />
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
