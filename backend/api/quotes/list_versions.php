<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/models/QuoteVersion.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

startSecureSession();
requireAuth();

$projectId = (int) ($_GET['project_id'] ?? 0);
if ($projectId <= 0) {
    Response::error('Missing project_id', 400);
}

$versions = QuoteVersion::getByProject($projectId, (int) $_SESSION['user_id']);
Response::success(['versions' => $versions]);
