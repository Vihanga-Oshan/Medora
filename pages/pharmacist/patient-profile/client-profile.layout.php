<?php $activePage = 'clients'; ?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Counselor Client Profile'; $pageStyle = ['counselor/clientProfile']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>
    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="Main Content Head background" class="main-header-bg-image" />
        <div class="main-content-header">
            <div class="main-content-header-text">
                <h2><?= htmlspecialchars($clientProfile['name']) ?></h2>
                <p>Manage <?= htmlspecialchars($clientProfile['name']) ?>'s progress and recovery plan</p>
            </div>
            <div class="card-container">
                <div class="card" style="width: 200px;"><div><p>STREAK</p><h1><?= (int) $clientProfile['streak'] ?></h1></div></div>
                <div class="card milestone-progress-card"><p>PROGRESS</p><span><?= (int) $clientProfile['progressPercentage'] ?>%</span><div class="progress" style="--value: <?= (int) $clientProfile['progressPercentage'] ?>%"><div class="bar"></div><div class="thumb" aria-label="Progress <?= (int) $clientProfile['progressPercentage'] ?> percent"></div></div></div>
            </div>
        </div>
        <div class="main-content-body">
            <div class="inner-body-content row">
                <div class="client-card">
                    <div class="client-card-content"><div class="client-card-info"><h4><?= htmlspecialchars($clientProfile['name']) ?></h4><span><?= htmlspecialchars($clientProfile['status']) ?></span></div></div>
                    <img src="<?= htmlspecialchars($clientProfile['avatarUrl']) ?>" alt="Client" />
                </div>
                <h3>Recovery Plan</h3>
                <div class="client-card">
                    <div class="client-card-content">
                        <div class="client-card-info"><h4><?= htmlspecialchars($clientProfile['plan']['title'] ?? 'Personalized recovery journey') ?></h4><span><?= htmlspecialchars($clientProfile['plan']['status'] ?? 'Draft') ?></span></div>
                        <div class="client-card-buttons">
                            <?php if (!empty($clientProfile['plan']['planId'])): ?>
                                <a href="/pharmacist/medication-plans/view?planId=<?= (int) $clientProfile['plan']['planId'] ?>"><button class="btn-join" type="button">View Full Plan</button></a>
                            <?php else: ?>
                                <a href="/pharmacist/medication-plans/create?userId=<?= (int) $clientProfile['id'] ?>"><button class="btn-join" type="button">Create Plan</button></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <img src="/assets/img/plan.png" alt="Plan" />
                </div>
                <h3>Progress</h3>
                <div class="client-progress">
                    <div class="progress-box">
                        <div><span>Streak</span><h3><?= (int) $clientProfile['streak'] ?> Days</h3></div>
                        <div><span>Longest Streak</span><h3><?= (int) $clientProfile['longestStreak'] ?> Days</h3></div>
                        <div><span>Badges</span><h3><?= (int) $clientProfile['badges'] ?></h3></div>
                        <div><span>Recent Slip-ups</span><h3><?= (int) $clientProfile['recentSlipUps'] ?> Triggers</h3></div>
                    </div>
                    <div class="progress-box-2">
                        <div><span>Logged urges</span><h3><?= (int) $clientProfile['loggedUrges'] ?></h3></div>
                        <div><span>Mood Today</span><h3><?= (int) $clientProfile['moodToday'] ?></h3><span>Latest check-in</span></div>
                    </div>
                    <div class="progress-chart"><div><canvas id="lineChart"></canvas></div><div><canvas id="barChart"></canvas></div></div>
                </div>
                <div class="row session-notes"><h3>Session Notes</h3><span><?= htmlspecialchars($clientProfile['sessionNotes']) ?></span><button class="btn-join" type="button">Add Notes</button></div>
            </div>
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script src="/assets/js/counselor/clientProfile.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
