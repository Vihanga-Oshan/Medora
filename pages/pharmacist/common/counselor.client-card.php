<?php
$client = $client ?? [];
?>
<div class="client-card">
    <div class="client-card-content">
        <div class="client-card-info">
            <h4><?= htmlspecialchars($client['name'] ?? 'Client') ?></h4>
            <span><?= htmlspecialchars($client['status'] ?? 'Active') ?></span>
            <span>Progress: <?= (int) ($client['progressPercentage'] ?? 0) ?>%</span>
        </div>
        <div class="client-card-buttons">
            <a href="/pharmacist/patient-profile?id=<?= (int) ($client['id'] ?? 0) ?>"><button class="btn-join" type="button">View Profile</button></a>
            <?php if (!empty($client['latestPlanId'])): ?>
                <a href="/pharmacist/medication-plans/view?planId=<?= (int) $client['latestPlanId'] ?>"><button class="btn-join" type="button">View Recovery Plan</button></a>
            <?php else: ?>
                <a href="/pharmacist/medication-plans/create?userId=<?= (int) ($client['id'] ?? 0) ?>"><button class="btn-join" type="button">Create Recovery Plan</button></a>
            <?php endif; ?>
        </div>
    </div>
    <img src="<?= htmlspecialchars($client['avatarUrl'] ?? '/assets/img/avatar.png') ?>" alt="Client avatar" />
</div>
