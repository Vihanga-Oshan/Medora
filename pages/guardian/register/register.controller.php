<?php

/**
 * Guardian Register Controller
 */
$error = null;

if (Request::isPost()) {
    $name = trim(Request::post('g_name') ?? '');
    $nic = trim(Request::post('nic') ?? '');
    $contactNumber = trim(Request::post('contact_number') ?? '');
    $email = trim(Request::post('email') ?? '');
    $password = Request::post('password') ?? '';
    $agree = Request::post('agree') ?? null;

    if ($name === '' || $nic === '' || $contactNumber === '' || $email === '' || $password === '' || $agree === null) {
        $error = 'Please fill all required fields and accept the privacy policy.';
    } else {
        require_once __DIR__ . '/register.model.php';

        if (RegisterModel::existsByNicOrEmail($nic, $email)) {
            $error = 'This NIC or Email is already registered.';
        } else {
            $ok = RegisterModel::createGuardian([
                'name' => $name,
                'nic' => $nic,
                'contactNumber' => $contactNumber,
                'email' => $email,
                'password' => $password,
            ]);

            if ($ok) {
                Response::redirect('/guardian/login');
            }

            $error = RegisterModel::getLastError() !== ''
                ? 'Something went wrong: ' . RegisterModel::getLastError()
                : 'Something went wrong. Please try again.';
        }
    }
}
