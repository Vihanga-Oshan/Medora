<?php

/**
 * Guardian Register Controller
 */
require_once ROOT . '/core/InputValidator.php';
require_once ROOT . '/core/AppLogger.php';

$error = null;

if (Request::isPost()) {
    $name = trim(Request::post('g_name') ?? '');
    $nic = trim(Request::post('nic') ?? '');
    $contactNumber = trim(Request::post('contact_number') ?? '');
    $email = InputValidator::normalizeEmail((string) (Request::post('email') ?? ''));
    $password = Request::post('password') ?? '';
    $confirmPassword = Request::post('confirm_password') ?? '';
    $agree = Request::post('agree') ?? null;

    $normalizeNic = static function (string $value): string {
        $value = strtoupper(trim($value));
        return preg_replace('/[\s\-]+/', '', $value) ?? $value;
    };

    $nic = $normalizeNic($nic);
    AppLogger::write('guardian-register-debug.log', 'DEBUG', 'Guardian registration request received.', [
        'nic_suffix' => substr($nic, -4),
        'email' => $email,
    ]);

    if ($name === '' || $nic === '' || $contactNumber === '' || $email === '' || $password === '' || $confirmPassword === '' || $agree === null) {
        $error = 'Please fill all required fields and accept the privacy policy.';
        AppLogger::write('guardian-register-debug.log', 'ERROR', 'Guardian registration validation failed.', [
            'nic_empty' => $nic === '' ? 1 : 0,
            'email_empty' => $email === '' ? 1 : 0,
            'password_empty' => $password === '' ? 1 : 0,
            'confirm_password_empty' => $confirmPassword === '' ? 1 : 0,
            'agree_missing' => $agree === null ? 1 : 0,
        ]);
    } elseif (!InputValidator::isValidNic($nic)) {
        $error = 'Please enter a valid NIC number.';
    } elseif (!InputValidator::isValidPhone($contactNumber)) {
        $error = 'Please enter a valid contact number.';
    } elseif (!InputValidator::isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (($passwordError = InputValidator::passwordError($password)) !== null) {
        $error = $passwordError;
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        require_once __DIR__ . '/register.model.php';

        if (RegisterModel::existsByNicOrEmail($nic, $email)) {
            $error = 'This NIC or Email is already registered.';
            AppLogger::write('guardian-register-debug.log', 'ERROR', 'Guardian already exists on register.', [
                'nic_suffix' => substr($nic, -4),
                'email' => $email,
            ]);
        } else {
            $ok = RegisterModel::createGuardian([
                'name' => $name,
                'nic' => $nic,
                'contactNumber' => $contactNumber,
                'email' => $email,
                'password' => $password,
            ]);

            if ($ok) {
                AppLogger::write('guardian-register-debug.log', 'DEBUG', 'Guardian registration successful.', [
                    'nic_suffix' => substr($nic, -4),
                    'email' => $email,
                ]);
                Response::redirect('/guardian/login');
            }

            AppLogger::write('guardian-register-debug.log', 'ERROR', 'Guardian registration failed in model.', [
                'nic_suffix' => substr($nic, -4),
                'email' => $email,
                'model_error' => RegisterModel::getLastError(),
            ]);
            $error = RegisterModel::getLastError() !== ''
                ? 'Something went wrong: ' . RegisterModel::getLastError()
                : 'Something went wrong. Please try again.';
        }
    }
}
