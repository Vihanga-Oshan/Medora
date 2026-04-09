<?php
$_pageStyles = [];
if (!empty($pageStyle)) {
    $_pageStyles = is_array($pageStyle) ? $pageStyle : [$pageStyle];
}
$base = APP_BASE ?: '';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Medora') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/common.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/sidebar.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/pharmacist/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <?php foreach ($_pageStyles as $_css): ?>
        <?php $_cssPath = __DIR__ . '/../../../public/assets/css/' . $_css . '.css'; ?>
        <?php if (file_exists($_cssPath)): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/<?= htmlspecialchars($_css) ?>.css">
        <?php endif; ?>
    <?php endforeach; ?>
</head>