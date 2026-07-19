<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
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
$versionId = (int) ($body['version_id'] ?? 0);
$packageName = trim($body['package_name'] ?? '');
$packagePrice = (float) ($body['package_price'] ?? 0);

if ($projectId <= 0 || $versionId <= 0 || $packageName === '') {
    Response::error('Missing required fields: project_id, version_id, package_name', 400);
}

QuoteVersion::selectPackage($versionId, $projectId, (int) $_SESSION['user_id'], $packageName, $packagePrice);

// Audit
$db = getDB();
$stmt = $db->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $_SESSION['user_id'],
    'action'  => 'quote.select_package',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode([
        'project_id'    => $projectId,
        'version_id'    => $versionId,
        'package_name'  => $packageName,
        'package_price' => $packagePrice,
    ]),
]);

Response::success(['selected' => true]);
