<?php
/**
 * Upload Prescription Layout
 * Ported from: upload-prescription.jsp (upload form section)
 */
?>
<?php $base = APP_BASE ?: ''; ?>
<?php $cssVer = time(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Upload a new prescription to Medora for pharmacist validation">
    <title>Upload Prescription | Medora</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/main.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/components/header.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/prescriptions.css?v=<?= $cssVer ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/patient/footer.css?v=<?= $cssVer ?>">
</head>
<body>

<?php require_once __DIR__ . '/../../common/patient.navbar.php'; ?>

<main class="container">
    <h1 class="section-title">Upload Prescription</h1>
    <p class="section-subtitle">Upload a clear image or PDF for pharmacist validation</p>

    <div class="card">
        <h2 class="card-title">Upload New Prescription</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($base) ?>/patient/prescriptions/upload" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token('patient_prescription_upload')) ?>">
            <label for="prescriptionFile" class="upload-area" id="uploadZone">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="17 8 12 3 7 8" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="3" x2="12" y2="15" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Click to upload or drag and drop</span>
                <span class="small">PDF, PNG, JPG &mdash; up to 10MB</span>
                <input type="file" name="prescription_file" id="prescriptionFile"
                       accept=".pdf,.jpg,.jpeg,.png" required hidden>
            </label>
            <div id="prescriptionPreview" class="preview-wrapper"></div>
            <p id="uploadError" class="error" style="display:none;"></p>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">&#128196; Upload Prescription</button>
                <a href="<?= htmlspecialchars($base) ?>/patient/prescriptions" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
(function(){
    const zone      = document.getElementById('uploadZone');
    const input     = document.getElementById('prescriptionFile');
    const preview   = document.getElementById('prescriptionPreview');
    const errEl     = document.getElementById('uploadError');
    const MAX       = 10 * 1024 * 1024;

    function showError(msg){ errEl.textContent = msg; errEl.style.display = 'block'; }
    function clearError(){ errEl.textContent = ''; errEl.style.display = 'none'; }

    function renderPreview(file){
        if(file.size > MAX){ showError('File is too large. Max 10MB.'); return; }
        clearError();
        preview.innerHTML = '';
        const container = document.createElement('div');
        container.className = 'preview-container';
        if(file.type.startsWith('image/')){
            const reader = new FileReader();
            reader.onload = e => {
                container.innerHTML = '<img src="'+e.target.result+'" alt="Preview"><p class="file-name">'+file.name+'</p>';
                preview.appendChild(container);
            };
            reader.readAsDataURL(file);
        } else {
            container.innerHTML = '<div class="pdf-preview"><div class="pdf-icon">PDF</div><p class="file-name">'+file.name+'</p></div>';
            preview.appendChild(container);
        }
    }

    input.addEventListener('change', e => { if(e.target.files[0]) renderPreview(e.target.files[0]); });

    ['dragenter','dragover'].forEach(ev => zone.addEventListener(ev, e=>{ e.preventDefault(); zone.classList.add('drag-over'); }));
    ['dragleave','drop'].forEach(ev => zone.addEventListener(ev, e=>{ e.preventDefault(); zone.classList.remove('drag-over'); }));
    zone.addEventListener('drop', e => {
        const file = e.dataTransfer?.files[0];
        if(file){ try{ input.files = e.dataTransfer.files; }catch(err){} renderPreview(file); }
    });
})();
</script>

<?php require_once __DIR__ . '/../../common/patient.footer.php'; ?>
</body>
</html>
