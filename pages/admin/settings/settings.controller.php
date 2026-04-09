<?php
/**
 * Admin Settings Controller
 */
require_once __DIR__ . '/settings.model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        SettingsModel::update($key, $value);
    }
    $base = APP_BASE ?: '';
    header('Location: ' . $base . '/admin/settings?msg=saved');
    exit;
}

$settings = SettingsModel::getAll();

$data = [
    'settings' => $settings,
];
