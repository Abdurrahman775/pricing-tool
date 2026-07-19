<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/models/Project.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

startSecureSession();
requireAuth();

$projectId = (int) ($_GET['id'] ?? 0);
if ($projectId <= 0) {
    Response::error('Missing project id', 400);
}

$project = Project::getFull($projectId, (int) $_SESSION['user_id']);
if ($project === null) {
    Response::error('Project not found', 404);
}

Response::success(['project' => $project]);
