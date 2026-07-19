<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/config/app.php';

/**
 * Require authenticated session. Sends 401 JSON and exits if not logged in.
 */
function requireAuth(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }
}

/**
 * Verify CSRF token from X-CSRF-Token header against session token.
 * Call on every POST/PUT/DELETE endpoint.
 */
function requireCsrfToken(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if ($headerToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $headerToken)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Invalid or missing CSRF token']);
        exit;
    }
}

/**
 * Generate and store a CSRF token in the session. Returns the token.
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Start a secure session.
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }

    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    // Regenerate session ID periodically to prevent fixation
    if (empty($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
        session_regenerate_id(true);
    } elseif (time() - $_SESSION['_created'] > 3600) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}
