<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/helpers/Validator.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/models/PricingConfig.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

startSecureSession();
requireCsrfToken();

// Parse JSON body
$body = json_decode(file_get_contents('php://input'), true);
if ($body === null) {
    Response::error('Invalid JSON body', 400);
}

// Validate
$v = new Validator();
$v->required('name', $body['name'] ?? '')
  ->required('email', $body['email'] ?? '')
  ->email('email', $body['email'] ?? '')
  ->required('password', $body['password'] ?? '')
  ->minLength('password', $body['password'] ?? '', 8);

if (!$v->passes()) {
    Response::errors($v->errors());
}

// Check if email already exists
$existing = User::findByEmail($body['email']);
if ($existing !== null) {
    Response::error('Email already registered', 409);
}

// Create user
$userId = User::create([
    'name'     => trim($body['name']),
    'email'    => strtolower(trim($body['email'])),
    'password' => $body['password'],
]);

// Log audit
$stmt = getDB()->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:user_id, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'user_id' => $userId,
    'action'  => 'user.register',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['email' => $body['email']]),
]);

// Create default pricing config for new user
PricingConfig::createFromTemplate($userId);

// Auto-login: set session
$_SESSION['user_id'] = $userId;
session_regenerate_id(true);

Response::success([
    'user' => [
        'id'    => $userId,
        'name'  => trim($body['name']),
        'email' => strtolower(trim($body['email'])),
    ],
], 201);
