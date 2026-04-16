<?php

/**
 * Patient Register Controller
 */
require_once ROOT . '/core/InputValidator.php';

$error = null;

if (Request::isPost()) {
    $name = trim(Request::post('name') ?? '');
    $gender = trim(Request::post('gender') ?? '');
    $emergencyContact = trim(Request::post('emergencyContact') ?? '');
    $nic = trim(Request::post('nic') ?? '');
    $email = InputValidator::normalizeEmail((string) (Request::post('email') ?? ''));
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
    } elseif (!InputValidator::isValidNic($nic)) {
        $error = 'Please enter a valid NIC number.';
    } elseif (!InputValidator::isValidPhone($emergencyContact)) {
        $error = 'Please enter a valid emergency contact number.';
    } elseif ($guardianNic !== '' && !InputValidator::isValidNic($guardianNic)) {
        $error = 'Please enter a valid guardian NIC number.';
    } elseif (!InputValidator::isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (($passwordError = InputValidator::passwordError($password)) !== null) {
        $error = $passwordError;
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
