<?php
$base = APP_BASE ?: '';
$navItems = [
    ['Dashboard', $base . '/pharmacist/dashboard', 'home', 'dashboard'],
    ['Patients', $base . '/pharmacist/patients', 'heart-pulse', 'clients'],
    ['Dispensing', $base . '/pharmacist/dispensing', 'video', 'sessions'],
    ['Medication Plans', $base . '/pharmacist/medication-plans', 'clipboard-plus', 'recovery'],
];
?>
<section class="sidebar">
    <div class="logo-container">
        <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.svg" alt="Logo" class="logo" />
        <h1 class="logo-text">Med<br />ora</h1>
    </div>

    <div class="nav">
        <?php foreach ($navItems as [$label, $href, $icon, $pageKey]): ?>
            <a href="<?= htmlspecialchars($href) ?>" class="sidebar-nav-link">
                <div class="sidebar-item <?= (isset($activePage) && $activePage === $pageKey) ? 'sidebar-item--active' : '' ?>">
                    <i data-lucide="<?= htmlspecialchars($icon) ?>" class="sidebar-icon" stroke-width="1"></i>
                    <span class="sidebar-text"><?= htmlspecialchars($label) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="user-info" id="counselorInfoClick">
        <img src="<?= htmlspecialchars(($currentPharmacist['profilePictureUrl'] ?? $currentCounselor['profilePictureUrl'] ?? ($base . '/assets/img/avatar.png'))) ?>" alt="Pharmacist Icon" class="user-icon" />
        <div class="user-details">
            <span class="user-name"><?= htmlspecialchars(explode(' ', $currentPharmacist['displayName'] ?? $currentCounselor['displayName'] ?? 'Pharmacist')[0]) ?></span>
            <span class="user-role"><?= htmlspecialchars($currentPharmacist['role'] ?? $currentCounselor['title'] ?? 'Pharmacist') ?></span>
        </div>
        <div class="user-menu-container">
            <i data-lucide="chevron-down" class="dropdown-icon" stroke-width="1"></i>
            <div class="user-menu-dropdown" id="counselorMenuDropdown">
                <button class="menu-option" id="editCounselorProfileBtn" type="button">
                    <i data-lucide="user" stroke-width="1"></i>
                    <span>Edit Profile</span>
                </button>
                <button class="menu-option" id="counselorLogoutBtn" type="button">
                    <i data-lucide="log-out" stroke-width="1"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</section>

<form id="counselorLogoutForm" method="POST" action="<?= htmlspecialchars($base) ?>/pharmacist/logout" style="display: none;"></form>

<?php require_once __DIR__ . '/counselor-profile-modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const userInfo = document.getElementById('counselorInfoClick');
    const dropdown = document.getElementById('counselorMenuDropdown');
    const editBtn = document.getElementById('editCounselorProfileBtn');
    const logoutBtn = document.getElementById('counselorLogoutBtn');
    const logoutForm = document.getElementById('counselorLogoutForm');

    userInfo?.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdown?.classList.toggle('show');
    });

    editBtn?.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdown?.classList.remove('show');
        if (typeof openCounselorProfileModal === 'function') {
            openCounselorProfileModal();
        }
    });

    logoutBtn?.addEventListener('click', (event) => {
        event.stopPropagation();
        logoutForm?.submit();
    });

    document.addEventListener('click', (event) => {
        if (userInfo && dropdown && !userInfo.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
});
</script>
