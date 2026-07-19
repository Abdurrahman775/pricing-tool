<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

startSecureSession();
requireAuth();
requireCsrfToken();

$userId = $_SESSION['user_id'];

// Log audit
$stmt = getDB()->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:user_id, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'user_id' => $userId,
    'action'  => 'user.logout',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => '{}',
]);

// Destroy session
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 86400, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
session_destroy();

Response::success(['message' => 'Logged out successfully']);
