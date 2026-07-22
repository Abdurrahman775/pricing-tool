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

$userId = (int) $_SESSION['user_id'];
$db = getDB();

$v = new Validator();
$v->required('name', $body['name'] ?? '')
  ->required('currency', $body['currency'] ?? '')
  ->required('base_rate', $body['base_rate'] ?? null)
  ->numeric('base_rate', $body['base_rate'] ?? '');

if (!$v->passes()) {
    Response::errors($v->errors());
}

$isNew = empty($body['id']);
$configId = $isNew ? 0 : (int) $body['id'];

if (!$isNew) {
    // Verify ownership
    $stmt = $db->prepare('SELECT id FROM pricing_configs WHERE id = :id AND user_id = :uid');
    $stmt->execute(['id' => $configId, 'uid' => $userId]);
    if (!$stmt->fetch()) {
        Response::error('Config not found', 404);
    }
}

if ($isNew) {
    $stmt = $db->prepare(
        'INSERT INTO pricing_configs (user_id, name, currency, base_rate) VALUES (:uid, :name, :currency, :base_rate)'
    );
    $stmt->execute([
        'uid'      => $userId,
        'name'     => trim($body['name']),
        'currency' => trim($body['currency']),
        'base_rate'=> (float) $body['base_rate'],
    ]);
    $configId = (int) $db->lastInsertId();
} else {
    $stmt = $db->prepare(
        'UPDATE pricing_configs SET name = :name, currency = :currency, base_rate = :base_rate WHERE id = :id'
    );
    $stmt->execute([
        'name'      => trim($body['name']),
        'currency'  => trim($body['currency']),
        'base_rate' => (float) $body['base_rate'],
        'id'        => $configId,
    ]);
}

// Save child records: delete all existing, then re-insert
// Screen templates must be deleted before complexity_tiers (FK dependency), and
// re-inserted after (since tiers get fresh ids below, resolved by name).
$db->prepare('DELETE FROM screen_templates WHERE pricing_config_id = :cid')->execute(['cid' => $configId]);

// Complexity tiers
$db->prepare('DELETE FROM complexity_tiers WHERE pricing_config_id = :cid')->execute(['cid' => $configId]);
$tierIdByName = [];
if (!empty($body['complexity_tiers']) && is_array($body['complexity_tiers'])) {
    $stmt = $db->prepare('INSERT INTO complexity_tiers (pricing_config_id, name, multiplier, sort_order) VALUES (:cid, :name, :mul, :so)');
    foreach ($body['complexity_tiers'] as $i => $tier) {
        if (!empty($tier['name'])) {
            $stmt->execute(['cid' => $configId, 'name' => $tier['name'], 'mul' => (float) ($tier['multiplier'] ?? 1.0), 'so' => $i + 1]);
            $tierIdByName[$tier['name']] = (int) $db->lastInsertId();
        }
    }
}

// Screen templates (reference tiers by name since tier ids are recreated on every save)
if (!empty($body['screen_templates']) && is_array($body['screen_templates'])) {
    $stmt = $db->prepare('INSERT INTO screen_templates (pricing_config_id, name, complexity_tier_id, sort_order) VALUES (:cid, :name, :tier_id, :so)');
    foreach ($body['screen_templates'] as $i => $st) {
        $tierId = $tierIdByName[$st['complexity_tier_name'] ?? ''] ?? null;
        if (!empty($st['name']) && $tierId) {
            $stmt->execute(['cid' => $configId, 'name' => $st['name'], 'tier_id' => $tierId, 'so' => $i + 1]);
        }
    }
}

// Integrations
$db->prepare('DELETE FROM integrations WHERE pricing_config_id = :cid')->execute(['cid' => $configId]);
if (!empty($body['integrations']) && is_array($body['integrations'])) {
    $stmt = $db->prepare('INSERT INTO integrations (pricing_config_id, name, points, sort_order) VALUES (:cid, :name, :pts, :so)');
    foreach ($body['integrations'] as $i => $int) {
        if (!empty($int['name'])) {
            $stmt->execute(['cid' => $configId, 'name' => $int['name'], 'pts' => (int) ($int['points'] ?? 0), 'so' => $i + 1]);
        }
    }
}

// Platform multipliers
$db->prepare('DELETE FROM platform_multipliers WHERE pricing_config_id = :cid')->execute(['cid' => $configId]);
if (!empty($body['platform_multipliers']) && is_array($body['platform_multipliers'])) {
    $stmt = $db->prepare('INSERT INTO platform_multipliers (pricing_config_id, platform, multiplier) VALUES (:cid, :platform, :mul)');
    foreach ($body['platform_multipliers'] as $pm) {
        if (!empty($pm['platform'])) {
            $stmt->execute(['cid' => $configId, 'platform' => $pm['platform'], 'mul' => (float) ($pm['multiplier'] ?? 1.0)]);
        }
    }
}

// Package tiers
$db->prepare('DELETE FROM package_tiers WHERE pricing_config_id = :cid')->execute(['cid' => $configId]);
if (!empty($body['package_tiers']) && is_array($body['package_tiers'])) {
    $stmt = $db->prepare('INSERT INTO package_tiers (pricing_config_id, name, multiplier, sort_order) VALUES (:cid, :name, :mul, :so)');
    foreach ($body['package_tiers'] as $i => $pkg) {
        if (!empty($pkg['name'])) {
            $stmt->execute(['cid' => $configId, 'name' => $pkg['name'], 'mul' => (float) ($pkg['multiplier'] ?? 1.0), 'so' => $i + 1]);
        }
    }
}

// Maintenance settings
$db->prepare('DELETE FROM maintenance_settings WHERE pricing_config_id = :cid')->execute(['cid' => $configId]);
if (!empty($body['maintenance'])) {
    $m = $body['maintenance'];
    $stmt = $db->prepare('INSERT INTO maintenance_settings (pricing_config_id, model, value) VALUES (:cid, :model, :val)');
    $stmt->execute([
        'cid'   => $configId,
        'model' => ($m['model'] ?? 'percentage') === 'flat' ? 'flat' : 'percentage',
        'val'   => (float) ($m['value'] ?? 15.0),
    ]);
}

// Audit
$stmt = $db->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $userId,
    'action'  => $isNew ? 'config.create' : 'config.update',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode(['config_id' => $configId, 'name' => $body['name']]),
]);

Response::success(['config_id' => $configId], $isNew ? 201 : 200);
