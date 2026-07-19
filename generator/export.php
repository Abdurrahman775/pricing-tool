<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/helpers/Response.php';
require_once dirname(__DIR__) . '/backend/models/PricingConfig.php';
require_once dirname(__DIR__) . '/security/middleware.php';

startSecureSession();
requireAuth();
requireCsrfToken();

$configId = (int) ($_GET['id'] ?? 0);
if ($configId <= 0) {
    // POST body
    $body = json_decode(file_get_contents('php://input'), true);
    $configId = (int) ($body['config_id'] ?? 0);
}

if ($configId <= 0) {
    Response::error('Missing config id', 400);
}

$config = PricingConfig::getFullConfig($configId, (int) $_SESSION['user_id']);
if ($config === null) {
    Response::error('Config not found', 404);
}

// Strip internal fields
unset($config['id'], $config['user_id'], $config['is_default'], $config['created_at'], $config['updated_at']);
foreach (['complexity_tiers', 'integrations', 'platform_multipliers', 'package_tiers'] as $key) {
    if (isset($config[$key])) {
        foreach ($config[$key] as &$item) {
            unset($item['id'], $item['pricing_config_id'], $item['created_at']);
        }
    }
}
if (isset($config['maintenance'])) {
    unset($config['maintenance']['id'], $config['maintenance']['pricing_config_id'], $config['maintenance']['created_at']);
}

// Audit
$stmt = getDB()->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $_SESSION['user_id'],
    'action'  => 'config.export',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['config_id' => $configId]),
]);

// Output as downloadable JSON
$json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$filename = 'pricing_config_' . preg_replace('/[^a-z0-9]/i', '_', $config['name']) . '.json';

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $json;
exit;
