<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/models/PricingConfig.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

startSecureSession();
requireAuth();

$configs = PricingConfig::listByUser((int) $_SESSION['user_id']);
Response::success(['configs' => $configs]);
