<?php
/**
 * Edit Prescription Layout
 * Ported from: edit-prescription.jsp
 */
$isPdf = str_ends_with(strtolower($prescription['file_name']), '.pdf');
$base  = APP_BASE ?: '';
$cssVer = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Prescription | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/prescriptions.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>

<?php require_once __DIR__ . '/../../common/patient.navbar.php'; ?>

<main class="container">
    <p class="section-subtitle">Update the display name of your uploaded prescription</p>

    <div class="edit-layout">
        <!-- File preview -->
        <div class="edit-preview">
            <div class="preview-box">
                <a href="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)$prescription['id'] ?>" target="_blank">
                    <?php if ($isPdf): ?>
                        <div class="pdf-icon">PDF</div>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)$prescription['id'] ?>"
                             alt="<?= htmlspecialchars($prescription['file_name']) ?>">
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="edit-form card">
            <h3 class="card-title">Edit File Name</h3>
            <p class="card-subtitle">This name appears under the prescription in your list.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($base) ?>/patient/prescriptions/edit">
                <input type="hidden" name="id" value="<?= (int)$prescription['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('patient_prescription_edit')) ?>">
                <div class="form-group">
                    <label for="fileName">Prescription Name</label>
                    <input type="text" id="fileName" name="file_name"
                           value="<?= htmlspecialchars($prescription['file_name']) ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="<?= htmlspecialchars($base) ?>/patient/prescriptions" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../common/patient.footer.php'; ?>

</body>
</html>
