<?php
/**
 * Upload Prescription Controller
 * Ported from: UploadPrescriptionServlet.java
 * Handles both GET (show form) and POST (save file).
 */
require_once ROOT . '/core/PharmacyOrderSupport.php';

$error = null;

if (Request::isPost()) {
    $formData = [
        'wants_medicine_order' => !empty($_POST['wants_medicine_order']) ? '1' : '0',
        'wants_schedule' => !empty($_POST['wants_schedule']) ? '1' : '0',
        'billing_name' => trim((string) ($_POST['billing_name'] ?? '')),
        'billing_phone' => trim((string) ($_POST['billing_phone'] ?? '')),
        'billing_email' => trim((string) ($_POST['billing_email'] ?? '')),
        'billing_address' => trim((string) ($_POST['billing_address'] ?? '')),
        'billing_city' => trim((string) ($_POST['billing_city'] ?? '')),
        'billing_notes' => trim((string) ($_POST['billing_notes'] ?? '')),
    ];
    $wantsMedicineOrder = $formData['wants_medicine_order'] === '1';
    $wantsSchedule = $formData['wants_schedule'] === '1';

    $billing = [
        'delivery_method' => 'PICKUP',
        'billing_name' => $formData['billing_name'],
        'billing_phone' => $formData['billing_phone'],
        'billing_email' => $formData['billing_email'],
        'billing_address' => $formData['billing_address'],
        'billing_city' => $formData['billing_city'],
        'billing_notes' => $formData['billing_notes'],
    ];

    $billingError = PharmacyOrderSupport::validateBillingData($billing, $wantsMedicineOrder);

    if ($wantsMedicineOrder) {
        PharmacyContext::patientHasSelection((string) ($user['nic'] ?? ''));
    }
    $selectedPharmacyId = PharmacyContext::selectedPharmacyId();
    $file = $_FILES['prescription_file'] ?? null;

    if ($billingError !== null) {
        $error = $billingError;
    } elseif ($wantsMedicineOrder && $selectedPharmacyId <= 0) {
        $error = 'Please select a pharmacy branch before ordering medicine.';
    } elseif (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid file to upload.';
    } elseif ($file['size'] > 10 * 1024 * 1024) {
        $error = 'File is too large. Maximum allowed size is 10MB.';
    } else {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            $error = 'Invalid file type. Only JPG, PNG and PDF are allowed.';
        } else {
            $allowedMime = [
                'jpg' => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'png' => ['image/png'],
                'pdf' => ['application/pdf'],
            ];

            $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
            $mime = $finfo ? (string) finfo_file($finfo, (string) $file['tmp_name']) : '';
            if ($finfo) {
                finfo_close($finfo);
            }
            if ($mime === '' || !in_array($mime, $allowedMime[$ext] ?? [], true)) {
                $error = 'File content type is invalid. Please upload a valid JPG, PNG, or PDF.';
                return;
            }

            $uploadDir = ROOT . '/storage/prescriptions/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0750, true);
            }

            $safeName = uniqid('rx_', true) . '.' . $ext;
            $destPath = $uploadDir . $safeName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $displayName = trim((string) pathinfo((string) $file['name'], PATHINFO_FILENAME));
                $displayName = preg_replace('/[^a-zA-Z0-9\-\._ ]/', '_', $displayName) ?: 'Prescription';
                $saved = PrescriptionsModel::insert(
                    (string) $user['nic'],
                    $displayName . '.' . $ext,
                    $safeName,
                    (string) ($user['name'] ?? 'Patient'),
                    $wantsMedicineOrder,
                    $wantsSchedule,
                    $billing
                );

                if ($saved) {
                    Response::redirect('/patient/prescriptions');
                }

                if (is_file($destPath)) {
                    @unlink($destPath);
                }
                $error = 'The file was uploaded, but the prescription record could not be saved. Please try again.';
            } else {
                $error = 'Failed to save the file. Please try again.';
            }
        }
    }
}
