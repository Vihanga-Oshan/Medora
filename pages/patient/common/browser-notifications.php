<?php
$base = APP_BASE ?: '';
$patientNic = (string)($user['nic'] ?? '');
if ($patientNic === '') {
    return;
}
?>
<script>
window.MedoraBrowserNotifications = {
    baseUrl: <?= json_encode($base) ?>,
    pollUrl: <?= json_encode($base . '/patient/notifications?action=poll') ?>,
    openUrl: <?= json_encode($base . '/patient/notifications') ?>,
    storageKey: <?= json_encode('medora.browserNotifications.' . $patientNic) ?>,
    pollIntervalMs: 15000,
    appName: 'Medora'
};
</script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/patient/browser-notifications.js"></script>
