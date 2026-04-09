<?php

/**
 * Patient Register Controller
 */
$error = null;

if (Request::isPost()) {
    $name = trim(Request::post('name') ?? '');
    $gender = trim(Request::post('gender') ?? '');
    $emergencyContact = trim(Request::post('emergencyContact') ?? '');
    $nic = trim(Request::post('nic') ?? '');
    $email = trim(Request::post('email') ?? '');
    $password = Request::post('password') ?? '';
    $confirmPassword = Request::post('confirmPassword') ?? '';
    $allergies = trim(Request::post('allergies') ?? '');
    $chronic = trim(Request::post('chronic') ?? '');
    $guardianNic = trim(Request::post('guardianNic') ?? '');

    $normalizeNic = static function (string $value): string {
        $value = strtoupper(trim($value));
        return preg_replace('/[\s\-]+/', '', $value) ?? $value;
    };

    $nic = $normalizeNic($nic);
    $guardianNic = $guardianNic !== '' ? $normalizeNic($guardianNic) : '';

    if ($name === '' || $gender === '' || $emergencyContact === '' || $email === '' || $nic === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill all required fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match!';
    } else {
        require_once __DIR__ . '/register.model.php';

        if (RegisterModel::existsByNicOrEmail($nic, $email)) {
            $error = 'This NIC or Email is already registered.';
        } else {
            $ok = RegisterModel::createPatient([
                'name' => $name,
                'gender' => $gender,
                'emergencyContact' => $emergencyContact,
                'nic' => $nic,
                'email' => $email,
                'password' => $password,
                'allergies' => $allergies,
                'chronic' => $chronic,
                'guardianNic' => $guardianNic,
            ]);

            if ($ok) {
                Response::redirect('/login');
            }

            $error = RegisterModel::getLastError() !== ''
                ? 'Something went wrong: ' . RegisterModel::getLastError()
                : 'Something went wrong. Please try again.';
        }
    }
}
