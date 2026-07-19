<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/models/PricingConfig.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

startSecureSession();
requireAuth();

$configId = (int) ($_GET['id'] ?? 0);
if ($configId <= 0) {
    Response::error('Missing config id', 400);
}

$config = PricingConfig::getFullConfig($configId, (int) $_SESSION['user_id']);
if ($config === null) {
    Response::error('Config not found', 404);
}

Response::success(['config' => $config]);
