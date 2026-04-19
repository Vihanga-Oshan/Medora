<?php
/**
 * Upload Prescription Layout
 * Ported from: upload-prescription.jsp (upload form section)
 */
?>
<?php $base = APP_BASE ?: ''; ?>
<?php $cssVer = time(); ?>
<?php
$showSuccessModal = (($_GET['msg'] ?? '') === 'uploaded');
?>
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
    <style>
        .upload-success-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            padding: 16px;
        }

        .upload-success-modal-card {
            background: #fff;
            border-radius: 12px;
            width: min(420px, 100%);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            padding: 24px;
            text-align: center;
        }

        .upload-success-modal-card h3 {
            margin: 0 0 10px;
            font-size: 1.4rem;
            color: #1f2a37;
        }

        .upload-success-modal-card p {
            margin: 0 0 18px;
            color: #4b5563;
        }

        .upload-success-modal-card button {
            border: 0;
            border-radius: 8px;
            background: #1677c6;
            color: #fff;
            padding: 10px 18px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
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

            <form action="<?= htmlspecialchars($base) ?>/patient/prescriptions/upload" method="post"
                enctype="multipart/form-data">
                <label for="prescriptionFile" class="upload-area" id="uploadZone">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#6c757d" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <polyline points="17 8 12 3 7 8" stroke="#6c757d" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <line x1="12" y1="3" x2="12" y2="15" stroke="#6c757d" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <span>Click to upload or drag and drop</span>
                    <span class="small">PDF, PNG, JPG &mdash; up to 10MB</span>
                    <input type="file" name="prescription_file" id="prescriptionFile" accept=".pdf,.jpg,.jpeg,.png"
                        required hidden>
                </label>
                <div id="prescriptionPreview" class="preview-wrapper"></div>
                <p id="uploadError" class="error" style="display:none;"></p>

                <div class="request-options">
                    <label class="option-check">
                        <input type="checkbox" name="wants_medicine_order" value="1">
                        <span>Order medicine</span>
                    </label>
                    <label class="option-check">
                        <input type="checkbox" name="wants_schedule" value="1">
                        <span>Schedule medicine too</span>
                    </label>
                </div>

                <div class="billing-panel" id="billingPanel">
                    <div class="billing-header">
                        <h3>Billing and Delivery Details</h3>
                        <p>Provide these details if the pharmacy should arrange the medicine order for you.</p>
                    </div>
                    <div class="billing-grid">
                        <label class="field-group">
                            <span>Billing Name</span>
                            <input type="text" name="billing_name" required>
                        </label>
                        <label class="field-group">
                            <span>Phone Number</span>
                            <input type="text" name="billing_phone" required>
                        </label>
                        <label class="field-group">
                            <span>Email</span>
                            <input type="email" name="billing_email" required>
                        </label>
                        <label class="field-group">
                            <span>Collection Method</span>
                            <select name="delivery_method" id="deliveryMethod">
                                <option value="PICKUP">Pick up from pharmacy</option>
                            </select>
                        </label>
                        <label class="field-group field-group-wide" id="billingAddressGroup">
                            <span>Address</span>
                            <textarea name="billing_address" rows="3"></textarea>
                        </label>
                        <label class="field-group">
                            <span>City</span>
                            <input type="text" name="billing_city">
                        </label>
                        <label class="field-group field-group-wide">
                            <span>Notes</span>
                            <textarea name="billing_notes" rows="3"></textarea>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">&#128196; Upload Prescription</button>
                    <a href="<?= htmlspecialchars($base) ?>/patient/prescriptions" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <?php if ($showSuccessModal): ?>
        <div class="upload-success-modal" id="uploadSuccessModal" role="dialog" aria-modal="true"
            aria-labelledby="uploadSuccessTitle">
            <div class="upload-success-modal-card">
                <h3 id="uploadSuccessTitle">Upload Successful</h3>
                <p>Your prescription has been uploaded.</p>
                <button type="button" id="uploadSuccessOk">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        (function () {
            const zone = document.getElementById('uploadZone');
            const input = document.getElementById('prescriptionFile');
            const preview = document.getElementById('prescriptionPreview');
            const errEl = document.getElementById('uploadError');
            const wantsOrder = document.querySelector('input[name="wants_medicine_order"]');
            const billingPanel = document.getElementById('billingPanel');
            const deliveryMethod = document.getElementById('deliveryMethod');
            const billingAddressGroup = document.getElementById('billingAddressGroup');
            const billingName = document.querySelector('input[name="billing_name"]');
            const billingPhone = document.querySelector('input[name="billing_phone"]');
            const billingEmail = document.querySelector('input[name="billing_email"]');
            const billingAddress = document.querySelector('textarea[name="billing_address"]');
            const MAX = 10 * 1024 * 1024;

            function showError(msg) { errEl.textContent = msg; errEl.style.display = 'block'; }
            function clearError() { errEl.textContent = ''; errEl.style.display = 'none'; }

            function renderPreview(file) {
                if (file.size > MAX) { showError('File is too large. Max 10MB.'); return; }
                clearError();
                preview.innerHTML = '';
                const container = document.createElement('div');
                container.className = 'preview-container';
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        container.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><p class="file-name">' + file.name + '</p>';
                        preview.appendChild(container);
                    };
                    reader.readAsDataURL(file);
                } else {
                    container.innerHTML = '<div class="pdf-preview"><div class="pdf-icon">PDF</div><p class="file-name">' + file.name + '</p></div>';
                    preview.appendChild(container);
                }
            }

            input.addEventListener('change', e => { if (e.target.files[0]) renderPreview(e.target.files[0]); });

            ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('drag-over'); }));
            ['dragleave', 'drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('drag-over'); }));
            zone.addEventListener('drop', e => {
                const file = e.dataTransfer?.files[0];
                if (file) { try { input.files = e.dataTransfer.files; } catch (err) { } renderPreview(file); }
            });

            function syncBillingUi() {
                const ordering = wantsOrder && wantsOrder.checked;
                const delivery = deliveryMethod && deliveryMethod.value === 'DELIVERY';
                if (billingPanel) {
                    billingPanel.classList.toggle('is-hidden', !ordering);
                }
                if (billingAddressGroup) {
                    billingAddressGroup.classList.toggle('is-hidden', !ordering || !delivery);
                }
                if (billingName) {
                    billingName.required = ordering;
                }
                if (billingPhone) {
                    billingPhone.required = ordering;
                }
                if (billingEmail) {
                    billingEmail.required = ordering;
                }
                if (billingAddress) {
                    billingAddress.required = ordering && delivery;
                }
            }

            if (wantsOrder) wantsOrder.addEventListener('change', syncBillingUi);
            if (deliveryMethod) deliveryMethod.addEventListener('change', syncBillingUi);
            syncBillingUi();
        })();

        (function () {
            const modal = document.getElementById('uploadSuccessModal');
            if (!modal) {
                return;
            }

            const closeAndRefresh = function () {
                window.location.replace(window.location.pathname);
            };

            const okBtn = document.getElementById('uploadSuccessOk');
            if (okBtn) {
                okBtn.addEventListener('click', closeAndRefresh);
            }

            window.setTimeout(closeAndRefresh, 1400);
        })();
    </script>

    <?php require_once __DIR__ . '/../../common/patient.footer.php'; ?>
</body>

</html>