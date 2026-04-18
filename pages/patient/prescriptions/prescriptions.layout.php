<?php
/**
 * Prescriptions List Layout
 * Ported from: upload-prescription.jsp (the listing section)
 */
$prescriptions = $data['prescriptions'];
$base          = APP_BASE ?: '';
$cssVer        = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage and view your uploaded prescriptions on Medora">
    <title>My Prescriptions | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/prescriptions.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>

<?php require_once __DIR__ . '/../common/patient.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">My Prescriptions</h1>
    <p class="section-subtitle">Upload and manage your medical prescriptions</p>

    <div class="card">
        <h2 class="card-title">Upload New Prescription</h2>
        <p class="card-subtitle">Upload a clear image or PDF of your prescription for pharmacist validation</p>

        <form action="<?= htmlspecialchars($base) ?>/patient/prescriptions/upload" method="post" enctype="multipart/form-data">
            <label for="prescriptionFile" class="upload-area" id="uploadZone">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="17 8 12 3 7 8" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="3" x2="12" y2="15" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Click to upload or drag and drop</span>
                <span class="small">PDF, PNG, JPG up to 10MB</span>
                <input type="file" name="prescription_file" id="prescriptionFile" accept=".pdf,.jpg,.jpeg,.png" required hidden>
            </label>

            <div id="prescriptionPreview" class="preview-wrapper"></div>
            <p id="uploadError" class="error" style="display:none;"></p>

            <button type="submit" class="btn btn-upload">Upload Prescription</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">My Uploaded Prescriptions</h3>
        <?php if (empty($prescriptions)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#128450;</div>
                <p>No prescriptions uploaded yet</p>
            </div>
        <?php else: ?>
            <div class="prescription-list">
                <?php foreach ($prescriptions as $p): ?>
                    <div class="prescription-tile">
                        <a href="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)$p['id'] ?>"
                           target="_blank" class="prescription-thumb">
                            <?php $isPdf = str_ends_with(strtolower($p['file_name']), '.pdf'); ?>
                            <?php if ($isPdf): ?>
                                <div class="pdf-icon">PDF</div>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($base) ?>/prescriptions/file?id=<?= (int)$p['id'] ?>"
                                     alt="<?= htmlspecialchars($p['file_name']) ?>">
                            <?php endif; ?>
                        </a>
                        <div class="prescription-meta">
                            <div class="prescription-name-date">
                                <div class="prescription-name"><?= htmlspecialchars($p['file_name']) ?></div>
                                <div class="prescription-date"><?= htmlspecialchars($p['formatted_upload_date']) ?></div>
                            </div>
                            <div class="prescription-actions">
                                <a href="<?= htmlspecialchars($base) ?>/patient/prescriptions/edit?id=<?= (int)$p['id'] ?>" class="prescription-link edit">Edit</a>
                                <button class="prescription-link delete" type="button"
                                        onclick="confirmDelete(<?= (int)$p['id'] ?>)">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal hidden" id="deleteModal">
    <div class="modal-content">
        <p>Are you sure you want to delete this prescription?</p>
        <form id="deleteForm" method="post" action="<?= htmlspecialchars($base) ?>/patient/prescriptions/delete">
            <input type="hidden" name="id" id="prescriptionIdToDelete">
            <div class="modal-actions">
                <button type="submit" class="delete-btn">Yes, Delete</button>
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('prescriptionIdToDelete').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

(function () {
    const zone = document.getElementById('uploadZone');
    const input = document.getElementById('prescriptionFile');
    const preview = document.getElementById('prescriptionPreview');
    const errEl = document.getElementById('uploadError');
    const max = 10 * 1024 * 1024;

    if (!zone || !input || !preview || !errEl) {
        return;
    }

    function showError(msg) {
        errEl.textContent = msg;
        errEl.style.display = 'block';
    }

    function clearError() {
        errEl.textContent = '';
        errEl.style.display = 'none';
    }

    function renderPreview(file) {
        if (file.size > max) {
            showError('File is too large. Max 10MB.');
            return;
        }
        clearError();
        preview.innerHTML = '';
        const container = document.createElement('div');
        container.className = 'preview-container';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                container.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><p class="file-name">' + file.name + '</p>';
                preview.appendChild(container);
            };
            reader.readAsDataURL(file);
        } else {
            container.innerHTML = '<div class="pdf-preview"><div class="pdf-icon">PDF</div><p class="file-name">' + file.name + '</p></div>';
            preview.appendChild(container);
        }
    }

    input.addEventListener('change', function (e) {
        if (e.target.files[0]) {
            renderPreview(e.target.files[0]);
        }
    });

    ['dragenter', 'dragover'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
    });

    ['dragleave', 'drop'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) {
            e.preventDefault();
            zone.classList.remove('drag-over');
        });
    });

    zone.addEventListener('drop', function (e) {
        const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
        if (file) {
            try {
                input.files = e.dataTransfer.files;
            } catch (err) {}
            renderPreview(file);
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../common/patient.footer.php'; ?>
</body>
</html>
