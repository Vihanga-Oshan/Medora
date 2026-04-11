<?php

/**
 * Guardian Login Controller
 */
$error = null;

if (!function_exists('guardianLoginWriteLog')) {
    function guardianLoginWriteLog(string $file, string $level, string $message, array $context = []): void
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 3);
        $logDir = $rootDir . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $safeContext = [];
        foreach ($context as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $safeContext[$k] = $v;
            } else {
                $safeContext[$k] = json_encode($v);
            }
        }

        $line = sprintf(
            "[%s] [%s] %s %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            json_encode($safeContext, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        @file_put_contents($logDir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }
}

if (Request::isPost()) {
    $nic = trim(Request::post('nic') ?? '');
    $password = Request::post('password') ?? '';

    $normalizeNic = static function (string $value): string {
        $value = strtoupper(trim($value));
        return preg_replace('/[\s\-]+/', '', $value) ?? $value;
    };

    $nic = $normalizeNic($nic);
    guardianLoginWriteLog('guardian-login-debug.log', 'DEBUG', 'Guardian login request received.', [
        'nic_suffix' => substr($nic, -4),
        'password_length' => strlen($password),
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    ]);

    if ($nic === '' || $password === '') {
        $error = 'NIC and password are required.';
        guardianLoginWriteLog('guardian-login-error.log', 'ERROR', 'Validation failed: missing NIC or password.', [
            'nic_empty' => $nic === '' ? 1 : 0,
            'password_empty' => $password === '' ? 1 : 0,
        ]);
    } else {
        require_once __DIR__ . '/login.model.php';
        $guardian = LoginModel::findByNic($nic);

        $valid = false;
        if ($guardian !== null) {
            $candidates = [];
            foreach (['password_value', 'password_hash_value'] as $field) {
                $value = (string)($guardian[$field] ?? '');
                if ($value !== '' && !in_array($value, $candidates, true)) {
                    $candidates[] = $value;
                }
            }

            foreach ($candidates as $stored) {
                $strategy = 'plain';
                if (str_starts_with($stored, '$2a$') || str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2b$')) {
                    $strategy = 'bcrypt';
                    if (str_starts_with($stored, '$2a$')) {
                        $stored = '$2y$' . substr($stored, 4);
                    }
                    $valid = password_verify($password, $stored);
                } elseif (preg_match('/^[a-f0-9]{64}$/i', $stored)) {
                    $strategy = 'sha256';
                    // Legacy SHA-256 hashes in older Medora datasets.
                    $valid = hash_equals(strtolower($stored), hash('sha256', $password));
                } else {
                    // Legacy plain-text fallback.
                    $valid = hash_equals((string)$stored, (string)$password);
                }

                guardianLoginWriteLog('guardian-login-debug.log', 'DEBUG', 'Password candidate checked.', [
                    'nic_suffix' => substr($nic, -4),
                    'strategy' => $strategy,
                    'candidate_length' => strlen((string)$stored),
                    'matched' => $valid ? 1 : 0,
                ]);

                if ($valid) {
                    break;
                }
            }
        } else {
            guardianLoginWriteLog('guardian-login-error.log', 'ERROR', 'Guardian account not found for NIC.', [
                'nic_suffix' => substr($nic, -4),
            ]);
        }

        if (!$valid) {
            $error = 'Invalid NIC or password.';
            guardianLoginWriteLog('guardian-login-error.log', 'ERROR', 'Guardian login failed.', [
                'nic_suffix' => substr($nic, -4),
                'reason' => $guardian === null ? 'guardian_not_found' : 'password_mismatch',
            ]);
        } else {
            $displayName = trim((string)($guardian['guardian_name'] ?? ''));
            if ($displayName === '') {
                $displayName = 'Guardian';
            }

            $token = Auth::sign([
                'id' => $guardian['nic'],
                'name' => $displayName,
                'role' => 'guardian',
            ]);

            Auth::setTokenCookie($token, 86400, 'guardian');
            guardianLoginWriteLog('guardian-login-debug.log', 'DEBUG', 'Guardian login successful.', [
                'nic_suffix' => substr((string)$guardian['nic'], -4),
                'display_name' => $displayName,
            ]);
            Response::redirect('/guardian/dashboard');
        }
    }
}
