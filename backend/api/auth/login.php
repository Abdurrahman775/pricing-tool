<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/helpers/Validator.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';
require_once dirname(__DIR__) . '/../../security/ratelimit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

startSecureSession();
requireCsrfToken();

// Rate limiting by IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!checkRateLimit($ip, 'login', 10, 900)) {
    Response::error('Too many login attempts. Try again in 15 minutes.', 429);
}

$body = json_decode(file_get_contents('php://input'), true);
if ($body === null) {
    Response::error('Invalid JSON body', 400);
}

$v = new Validator();
$v->required('email', $body['email'] ?? '')
  ->email('email', $body['email'] ?? '')
  ->required('password', $body['password'] ?? '');

if (!$v->passes()) {
    Response::errors($v->errors());
}

$email    = strtolower(trim($body['email']));
$password = $body['password'];

$user = User::authenticate($email, $password);

// Log audit
$db = getDB();
$stmt = $db->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:user_id, :action, :ip, :ua, :details)'
);

if ($user === null) {
    $stmt->execute([
        'user_id' => null,
        'action'  => 'user.login.failed',
        'ip'      => $ip,
        'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'details' => json_encode(['email' => $email]),
    ]);
    Response::error('Invalid email or password', 401);
}

// Login success
$_SESSION['user_id'] = $user['id'];
session_regenerate_id(true);

$stmt->execute([
    'user_id' => $user['id'],
    'action'  => 'user.login',
    'ip'      => $ip,
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['email' => $email]),
]);

Response::success([
    'user' => [
        'id'    => (int) $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
    ],
]);
