<?php
$base = APP_BASE ?: '';
?>
<footer class="patient-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4>Medora</h4>
            <p>Your trusted assistant for smart prescription tracking and medication reminders.</p>
        </div>

        <div class="footer-links-row">
            <a href="<?= htmlspecialchars($base) ?>/">Help Center</a>
            <a href="<?= htmlspecialchars($base) ?>/">Contact Us</a>
            <a href="<?= htmlspecialchars($base) ?>/">Privacy Policy</a>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 Medora. All rights reserved.</p>
    </div>
</footer>

<?php require_once __DIR__ . '/browser-notifications.php'; ?>
