<?php

/**
 * Guardian Register Controller
 */
$error = null;

if (!function_exists('guardianRegisterWriteLog')) {
    function guardianRegisterWriteLog(string $file, string $level, string $message, array $context = []): void
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 3);
        $logDir = $rootDir . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($context, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        @file_put_contents($logDir . '/guardian-register-debug.log', $line, FILE_APPEND | LOCK_EX);
    }
}

if (Request::isPost()) {
    $name = trim(Request::post('g_name') ?? '');
    $nic = trim(Request::post('nic') ?? '');
    $contactNumber = trim(Request::post('contact_number') ?? '');
    $email = trim(Request::post('email') ?? '');
    $password = Request::post('password') ?? '';
    $agree = Request::post('agree') ?? null;

    $normalizeNic = static function (string $value): string {
        $value = strtoupper(trim($value));
        return preg_replace('/[\s\-]+/', '', $value) ?? $value;
    };

    $nic = $normalizeNic($nic);
    guardianRegisterWriteLog('guardian-register-debug.log', 'DEBUG', 'Guardian registration request received.', [
        'nic_suffix' => substr($nic, -4),
        'email' => $email,
    ]);

    if ($name === '' || $nic === '' || $contactNumber === '' || $email === '' || $password === '' || $agree === null) {
        $error = 'Please fill all required fields and accept the privacy policy.';
        guardianRegisterWriteLog('guardian-register-debug.log', 'ERROR', 'Guardian registration validation failed.', [
            'nic_empty' => $nic === '' ? 1 : 0,
            'email_empty' => $email === '' ? 1 : 0,
            'password_empty' => $password === '' ? 1 : 0,
            'agree_missing' => $agree === null ? 1 : 0,
        ]);
    } else {
        require_once __DIR__ . '/register.model.php';

        if (RegisterModel::existsByNicOrEmail($nic, $email)) {
            $error = 'This NIC or Email is already registered.';
            guardianRegisterWriteLog('guardian-register-debug.log', 'ERROR', 'Guardian already exists on register.', [
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
                guardianRegisterWriteLog('guardian-register-debug.log', 'DEBUG', 'Guardian registration successful.', [
                    'nic_suffix' => substr($nic, -4),
                    'email' => $email,
                ]);
                Response::redirect('/guardian/login');
            }

            guardianRegisterWriteLog('guardian-register-debug.log', 'ERROR', 'Guardian registration failed in model.', [
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
