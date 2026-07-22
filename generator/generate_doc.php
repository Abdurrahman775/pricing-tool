<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/helpers/Response.php';
require_once dirname(__DIR__) . '/backend/models/Project.php';
require_once dirname(__DIR__) . '/backend/models/QuoteVersion.php';
require_once dirname(__DIR__) . '/security/middleware.php';
require_once dirname(__DIR__) . '/security/ratelimit.php';
require_once __DIR__ . '/DocxGenerator.php';
require_once __DIR__ . '/AiClient.php';
require_once __DIR__ . '/AiPrompts.php';

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
$docType   = $body['doc_type'] ?? '';
$packageName  = $body['package_name'] ?? '';
$packagePrice = (float) ($body['package_price'] ?? 0);

if ($projectId <= 0 || !in_array($docType, ['proposal', 'prd', 'documentation'], true)) {
    Response::error('Missing or invalid fields: project_id, doc_type (proposal|prd|documentation)', 400);
}

$userId = (int) $_SESSION['user_id'];
if (!checkRateLimit("user_{$userId}", 'doc_generation', 30, 3600)) {
    http_response_code(429);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Document generation rate limit exceeded. Maximum 30 per hour.']);
    exit;
}

$project = Project::getFull($projectId, $userId);
if ($project === null) {
    Response::error('Project not found', 404);
}

$version = QuoteVersion::getLatest($projectId, $userId);

if ($version && $packageName) {
    QuoteVersion::selectPackage($version['id'], $projectId, $userId, $packageName, $packagePrice);
    $version['selected_package'] = $packageName;
    $version['package_price'] = $packagePrice;
}

if (!$version) {
    Response::error('No quote version found. Lock the project first.', 422);
}

$breakdown = json_decode($version['pricing_breakdown'] ?? '{}', true) ?: [];
$currency = $project['latest_version'] ? ($breakdown['currency'] ?? $project['currency'] ?? '₦') : '₦';

$templateData = [
    'projectName'   => $project['project_name'],
    'clientName'    => $project['client_name'],
    'packageName'   => $version['selected_package'] ?? $packageName,
    'packagePrice'  => $version['package_price'] ?? $packagePrice,
    'currency'      => $currency,
    'breakdown'     => $breakdown,
    'versionNumber' => $version['version_number'] ?? 1,
    'date'          => date('F j, Y'),
];

$ai = new AiClient();
$aiGenerated = false;
$docx = null;

if ($ai->isConfigured()) {
    try {
        $promptBuilders = [
            'proposal'      => ['system' => 'proposalSystemPrompt', 'user' => 'buildProposalPrompt'],
            'prd'           => ['system' => 'prdSystemPrompt', 'user' => 'buildPrdPrompt'],
            'documentation' => ['system' => 'documentationSystemPrompt', 'user' => 'buildDocumentationPrompt'],
        ];
        $builder = $promptBuilders[$docType];
        $systemPrompt = AiPrompts::{$builder['system']}();
        $userPrompt = AiPrompts::{$builder['user']}($templateData);

        $aiJson = $ai->generate($systemPrompt, $userPrompt);
        $aiData = json_decode($aiJson, true, 512, JSON_THROW_ON_ERROR);
        $aiData['project'] = $templateData['projectName'];

        $tmpJson = tempnam(sys_get_temp_dir(), 'ai_doc_') . '.json';
        file_put_contents($tmpJson, json_encode($aiData));

        $scriptPath = __DIR__ . '/build_docx.js';
        $outputPath = tempnam(sys_get_temp_dir(), 'ai_docx_') . '.docx';

        $cmd = escapeshellcmd("node $scriptPath $tmpJson $outputPath") . ' 2>/dev/null';
        $resultPath = trim(shell_exec($cmd));

        unlink($tmpJson);

        if ($resultPath && file_exists($resultPath)) {
            $docx = file_get_contents($resultPath);
            unlink($resultPath);
            $aiGenerated = true;
        }
    } catch (Throwable $e) {
        error_log('AI generation failed, falling back to template: ' . $e->getMessage());
    }
}

if ($docx === null) {
    $docxTemplate = __DIR__ . "/templates/{$docType}_docx.php";
    $templateFile = file_exists($docxTemplate) ? $docxTemplate : __DIR__ . "/templates/{$docType}.php";
    if (!file_exists($templateFile)) {
        Response::error("Template not found: {$docType}", 500);
    }
    extract($templateData);
    ob_start();
    require $templateFile;
    $html = ob_get_clean();

    try {
        ob_start();
        try {
            $gen = new DocxGenerator();
            $docx = $gen->generate($html);
        } finally {
            ob_end_clean();
        }
    } catch (Throwable $e) {
        Response::error('Document generation failed: ' . $e->getMessage(), 500);
    }
}

$db = getDB();
$stmt = $db->prepare(
    'INSERT INTO audit_log (user_id, action, ip_address, user_agent, details)
     VALUES (:uid, :action, :ip, :ua, :details)'
);
$stmt->execute([
    'uid'     => $userId,
    'action'  => 'doc.generate',
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'details' => json_encode([
        'project_id'   => $projectId,
        'doc_type'     => $docType,
        'package'      => $templateData['packageName'],
        'ai_generated' => $aiGenerated,
    ]),
]);

$filename = preg_replace('/[^a-z0-9]/i', '_', $project['project_name']) . "_{$docType}.docx";
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($docx));
echo $docx;
exit;
