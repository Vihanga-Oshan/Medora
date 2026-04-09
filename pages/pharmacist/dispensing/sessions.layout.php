<?php $activePage = 'sessions'; ?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = 'Counselor Sessions'; $pageStyle = ['counselor/sessions']; require __DIR__ . '/../common/counselor.html.head.php'; ?>
<body>
<main class="main-container theme-counselor">
    <?php require __DIR__ . '/../common/counselor.sidebar.php'; ?>
    <section class="main-content">
        <img src="/assets/img/main-content-head.svg" alt="Main Content Head background" class="main-header-bg-image" />
        <div class="main-content-header"><div class="main-content-header-text"><h2>My Sessions</h2><p>View and manage your sessions</p></div></div>
        <div class="main-content-body">
            <div class="inner-body-content row">
                <div class="column">
                    <button class="btn btn-bg-light-green filter-button" type="button"><i data-lucide="filter" class="filter-icon" stroke-width="1"></i><span>Filter</span></button>
                    <?php require __DIR__ . '/../common/counselor.searchbar.php'; ?>
                </div>
                <div class="column"><span onclick="showSection('sec1')" class="toggle-button active-button" id="toggle1">Upcoming</span><span onclick="showSection('sec2')" class="toggle-button" id="toggle2">History</span></div>
                <section class="toggle-section active-section" id="sec1">
                    <?php $hasUpcoming = false; foreach ($sessions as $session): if (!$session['isUpcoming']) continue; $hasUpcoming = true; $isUpcoming = true; require __DIR__ . '/../common/counselor.session-card.php'; endforeach; ?>
                    <?php if (!$hasUpcoming): ?><div class="no-sessions-message"><p>No upcoming sessions scheduled.</p></div><?php endif; ?>
                </section>
                <section class="toggle-section" id="sec2">
                    <?php $hasHistory = false; foreach ($sessions as $session): if ($session['isUpcoming']) continue; $hasHistory = true; $isUpcoming = false; require __DIR__ . '/../common/counselor.session-card.php'; endforeach; ?>
                    <?php if (!$hasHistory): ?><div class="no-sessions-message"><p>No session history available.</p></div><?php endif; ?>
                </section>
            </div>
        </div>
    </section>
</main>
<script src="/assets/js/counselor/sessions.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
