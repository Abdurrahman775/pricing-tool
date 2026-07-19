<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/helpers/Validator.php';
require_once dirname(__DIR__, 2) . '/models/Project.php';
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
$v->required('pricing_config_id', $body['pricing_config_id'] ?? null)
  ->required('client_name', $body['client_name'] ?? '')
  ->required('project_name', $body['project_name'] ?? '')
  ->required('screens', $body['screens'] ?? null);

if (!$v->passes()) {
    Response::errors($v->errors());
}

if (!is_array($body['screens']) || count($body['screens']) === 0) {
    Response::error('At least one screen is required', 422);
}

$projectId = Project::create((int) $_SESSION['user_id'], $body);

// Audit
$stmt = getDB()->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $_SESSION['user_id'],
    'action'  => 'project.create',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['project_id' => $projectId, 'name' => $body['project_name']]),
]);

Response::success(['project_id' => $projectId], 201);
