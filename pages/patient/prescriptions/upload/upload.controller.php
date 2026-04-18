<?php
/**
 * Upload Prescription Controller
 * Ported from: UploadPrescriptionServlet.java
 * Handles both GET (show form) and POST (save file).
 */
$error      = null;

if (Request::isPost()) {
    $file = $_FILES['prescription_file'] ?? null;

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid file to upload.';
    } elseif ($file['size'] > 10 * 1024 * 1024) {
        $error = 'File is too large. Maximum allowed size is 10MB.';
    } else {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

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
            $mime = $finfo ? (string)finfo_file($finfo, (string)$file['tmp_name']) : '';
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
                $displayName = trim((string)pathinfo((string)$file['name'], PATHINFO_FILENAME));
                $displayName = preg_replace('/[^a-zA-Z0-9\-\._ ]/', '_', $displayName) ?: 'Prescription';
                $saved = PrescriptionsModel::insert(
                    (string)$user['nic'],
                    $displayName . '.' . $ext,
                    $safeName,
                    (string)($user['name'] ?? 'Patient')
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
