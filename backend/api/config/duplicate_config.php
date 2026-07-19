<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/helpers/Validator.php';
require_once dirname(__DIR__, 2) . '/models/PricingConfig.php';
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

$v = new Validator();
$v->required('config_id', $body['config_id'] ?? null)
  ->required('name', $body['name'] ?? '')
  ->minLength('name', $body['name'] ?? '', 1)
  ->maxLength('name', $body['name'] ?? '', 255);

if (!$v->passes()) {
    Response::errors($v->errors());
}

$newId = PricingConfig::duplicate(
    (int) $body['config_id'],
    (int) $_SESSION['user_id'],
    trim($body['name'])
);

if ($newId === null) {
    Response::error('Source config not found', 404);
}

// Audit
$stmt = getDB()->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $_SESSION['user_id'],
    'action'  => 'config.duplicate',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['source_id' => (int) $body['config_id'], 'new_id' => $newId, 'name' => $body['name']]),
]);

Response::success(['config_id' => $newId], 201);
