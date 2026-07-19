<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/helpers/PricingEngine.php';
require_once dirname(__DIR__, 2) . '/models/Project.php';
require_once dirname(__DIR__, 2) . '/models/PricingConfig.php';
require_once dirname(__DIR__, 2) . '/models/QuoteVersion.php';
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

if ($project['status'] !== 'draft') {
    Response::error('Project is already locked', 422);
}

// Load config for pricing
$config = PricingConfig::getFullConfig((int) $project['pricing_config_id'], (int) $_SESSION['user_id']);
if ($config === null) {
    Response::error('Pricing config not found', 404);
}

// Rebuild screen/integration/platform data from project
$screens = [];
foreach ($project['screens'] as $s) {
    $screens[] = [
        'name'               => $s['name'],
        'complexity_tier_id' => $s['complexity_tier_id'],
        'notes'              => $s['notes'] ?? '',
    ];
}

$integrationIds = [];
foreach ($project['integrations'] as $int) {
    if ($int['integration_id']) {
        $integrationIds[] = (int) $int['integration_id'];
    } elseif ($int['custom_name']) {
        $integrationIds[] = [
            'custom_name'   => $int['custom_name'],
            'custom_points' => (int) ($int['custom_points'] ?? 0),
        ];
    }
}

$platforms = $project['platforms'] ?? [];

// Get maintenance from config (stored in project notes as JSON, or use default)
$maintenance = null;
$configMaint = $config['maintenance'] ?? null;
if ($configMaint) {
    $maintenance = [
        'model' => $configMaint['model'] ?? 'percentage',
        'value' => (float) ($configMaint['value'] ?? 15),
    ];
}

$breakdown = PricingEngine::calculate($screens, $integrationIds, $platforms, $maintenance, $config);

// Create quote version
$versionId = QuoteVersion::create($projectId, $breakdown);

// Update project status to locked
$db = getDB();
$stmt = $db->prepare('UPDATE projects SET status = :status WHERE id = :id');
$stmt->execute(['status' => 'locked', 'id' => $projectId]);

// Audit
$stmt = $db->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $_SESSION['user_id'],
    'action'  => 'project.lock',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['project_id' => $projectId, 'version_id' => $versionId]),
]);

Response::success([
    'project_id'        => $projectId,
    'version_id'        => $versionId,
    'status'            => 'locked',
    'breakdown'         => $breakdown,
]);
