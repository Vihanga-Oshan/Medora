<?php

$errorMessage = null;
if (Request::isPost()) {
    $result = CounselorApplicationsModel::submit($_POST);
    if (!empty($result['ok'])) {
        Response::redirect('/counselor/application-success');
    }
    $errorMessage = $result['error'] ?? 'Error submitting application. Please try again.';
}
