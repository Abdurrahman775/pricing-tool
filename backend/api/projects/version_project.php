<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
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

$projectId = (int) ($body['project_id'] ?? 0);
if ($projectId <= 0) {
    Response::error('Missing project_id', 400);
}

$project = Project::getFull($projectId, (int) $_SESSION['user_id']);
if ($project === null) {
    Response::error('Project not found', 404);
}

if ($project['status'] !== 'locked') {
    Response::error('Only locked projects can be versioned', 422);
}

// Clone project as new draft
$db = getDB();

$stmt = $db->prepare(
    'INSERT INTO projects (user_id, pricing_config_id, client_name, project_name, notes, status)
     VALUES (:uid, :config_id, :client, :project, :notes, :status)'
);
$stmt->execute([
    'uid'       => $_SESSION['user_id'],
    'config_id' => (int) $project['pricing_config_id'],
    'client'    => $project['client_name'],
    'project'   => $project['project_name'] . ' (v2)',
    'notes'     => $project['notes'] ?? '',
    'status'    => 'draft',
]);
$newProjectId = (int) $db->lastInsertId();

// Clone screens
if (!empty($project['screens'])) {
    $stmt = $db->prepare(
        'INSERT INTO project_screens (project_id, name, complexity_tier_id, notes)
         VALUES (:pid, :name, :tier_id, :notes)'
    );
    foreach ($project['screens'] as $s) {
        $stmt->execute([
            'pid'     => $newProjectId,
            'name'    => $s['name'],
            'tier_id' => (int) $s['complexity_tier_id'],
            'notes'   => $s['notes'] ?? '',
        ]);
    }
}

// Clone integrations
if (!empty($project['integrations'])) {
    $stmt = $db->prepare(
        'INSERT INTO project_integrations (project_id, integration_id, custom_name, custom_points)
         VALUES (:pid, :iid, :cname, :cpts)'
    );
    foreach ($project['integrations'] as $int) {
        $stmt->execute([
            'pid'   => $newProjectId,
            'iid'   => $int['integration_id'] ? (int) $int['integration_id'] : null,
            'cname' => $int['custom_name'],
            'cpts'  => $int['custom_points'] ? (int) $int['custom_points'] : null,
        ]);
    }
}

// Clone platforms
if (!empty($project['platforms'])) {
    $stmt = $db->prepare(
        'INSERT INTO project_platforms (project_id, platform) VALUES (:pid, :platform)'
    );
    foreach ($project['platforms'] as $p) {
        $stmt->execute(['pid' => $newProjectId, 'platform' => $p]);
    }
}

// Audit
$stmt = $db->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $_SESSION['user_id'],
    'action'  => 'project.version',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['original_project_id' => $projectId, 'new_project_id' => $newProjectId]),
]);

Response::success(['project_id' => $newProjectId], 201);
