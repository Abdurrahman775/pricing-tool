<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/helpers/Validator.php';
require_once dirname(__DIR__, 2) . '/helpers/PricingEngine.php';
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
  ->numeric('config_id', $body['config_id'] ?? '');

if (!$v->passes()) {
    Response::errors($v->errors());
}

$config = PricingConfig::getFullConfig((int) $body['config_id'], (int) $_SESSION['user_id']);
if ($config === null) {
    Response::error('Pricing config not found', 404);
}

$screens = $body['screens'] ?? [];
$integrationIds = $body['integration_ids'] ?? [];
$platforms = $body['platforms'] ?? [];
$maintenance = $body['maintenance'] ?? null;

$breakdown = PricingEngine::calculate($screens, $integrationIds, $platforms, $maintenance, $config);

Response::success(['breakdown' => $breakdown]);
