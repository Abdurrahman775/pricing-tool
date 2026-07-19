<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

startSecureSession();
requireAuth();
requireCsrfToken();

$body = json_decode(file_get_contents('php://input'), true);
if ($body === null) {
    Response::error('Invalid JSON body', 400);
}

$userId = (int) $_SESSION['user_id'];
$action = $body['action'] ?? '';

if ($action === 'profile') {
    $name = trim($body['name'] ?? '');
    if ($name === '') {
        Response::error('Name is required', 422);
    }
    User::updateProfile($userId, $name);

    $stmt = getDB()->prepare(
        'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
         VALUES (:uid, :action, :ip, :ua, :details)'
    );
    $stmt->execute([
        'uid'     => $userId,
        'action'  => 'profile.update',
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'details' => json_encode(['name' => $name]),
    ]);

    Response::success(['name' => $name]);
} elseif ($action === 'password') {
    $current = $body['current_password'] ?? '';
    $newPass = $body['new_password'] ?? '';

    if ($current === '' || $newPass === '') {
        Response::error('Current password and new password are required', 422);
    }
    if (strlen($newPass) < 8) {
        Response::error('New password must be at least 8 characters', 422);
    }

    if (User::updatePassword($userId, $current, $newPass)) {
        $stmt = getDB()->prepare(
            'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
             VALUES (:uid, :action, :ip, :ua, :details)'
        );
        $stmt->execute([
            'uid'     => $userId,
            'action'  => 'password.update',
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'details' => '{}',
        ]);
        Response::success([]);
    } else {
        Response::error('Current password is incorrect', 422);
    }
} else {
    Response::error('Invalid action', 400);
}
