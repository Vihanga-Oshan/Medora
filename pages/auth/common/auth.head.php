<?php

/**
 * Auth Head Component — pages/auth/common/auth.head.php
 *
 * Usage: include at the top of auth layout files.
 * Expects variables before include:
 *   $pageTitle (string)
 *   $authCss   (string|array) paths relative to /assets/css/
 */

$_authCssFiles = [];
if (!empty($authCss)) {
    $_authCssFiles = is_array($authCss) ? $authCss : [$authCss];
}

$base = APP_BASE ?: '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Medora') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/common.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/auth.css">

    <?php foreach ($_authCssFiles as $_css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/<?= htmlspecialchars($_css) ?>">
    <?php endforeach; ?>
</head>
