<?php
declare(strict_types=1);

// Load .env
function loadEnv(string $path = null): void
{
    $path = $path ?? dirname(__DIR__, 2) . '/.env';
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

// Constants
define('BASE_URL',       $_ENV['BASE_URL'] ?? 'http://localhost:8000');
define('SESSION_LIFETIME', 86400 * 7); // 7 days
define('BCRYPT_COST',      12);
define('CSRF_TOKEN_LENGTH', 32);
