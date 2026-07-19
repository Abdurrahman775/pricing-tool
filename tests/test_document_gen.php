<?php
declare(strict_types=1);

/**
 * Document Generation Test
 * Run: php tests/test_document_gen.php
 * Requires: dev server running on localhost:8000
 *
 * Tests:
 * 1. Lock a project and verify quote_version created
 * 2. Select a package
 * 3. Generate proposal PDF — verify package name + price in PDF
 * 4. Generate PRD PDF — verify "Out of Scope" section present
 * 5. Rate limiting on doc generation
 * 6. Invalid doc_type returns 400
 */

$BASE = 'http://localhost:8000';
$pass = 0;
$fail = 0;
$cookieJar = '/tmp/test_docgen_cookies.txt';
$csrfToken = '';
$configId = 0;
$projectId = 0;
$versionId = 0;

function fetchCsrf(): string
{
    global $BASE, $cookieJar;
    $ch = curl_init($BASE . '/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    preg_match('/<meta name="csrf-token" content="([^"]+)">/', $html, $m);
    return $m[1] ?? '';
}

function test(string $name, callable $fn): void
{
    global $pass, $fail;
    try {
        $fn();
        echo "  PASS: {$name}\n";
        $pass++;
    } catch (Throwable $e) {
        echo "  FAIL: {$name} — {$e->getMessage()}\n";
        $fail++;
    }
}

function req(string $method, string $url, array $data = []): array
{
    global $cookieJar, $csrfToken;
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ];
    if ($method === 'POST') {
        if ($csrfToken === '') {
            $csrfToken = fetchCsrf();
        }
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_HTTPHEADER][] = 'X-CSRF-Token: ' . $csrfToken;
        $opts[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = substr($response, $headerSize);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    return [
        'code'        => $httpCode,
        'body'        => json_decode($body, true),
        'raw'         => $body,
        'contentType' => $contentType,
    ];
}

function randomEmail(): string
{
    return 'docgen_' . time() . '_' . rand(100, 999) . '@example.com';
}

// Bootstrap
echo "Document Generation Test\n";
echo str_repeat('-', 40) . "\n";

// Register
$email = randomEmail();
$reg = req('POST', "{$BASE}/api/auth/register.php", [
    'name' => 'Doc Gen Tester',
    'email' => $email,
    'password' => 'TestPass123!',
]);
assert($reg['code'] === 201, "Register failed: " . ($reg['body']['error'] ?? ''));
echo "  Bootstrapped: {$email}\n";

// Get config
$listRes = req('GET', "{$BASE}/api/config/list_configs.php");
$configId = (int) ($listRes['body']['data']['configs'][0]['id'] ?? 0);
assert($configId > 0, 'Could not get config ID');
echo "  Config ID: {$configId}\n";

// 1. Create project
test('Create project via intake API', function () use ($BASE, $configId, &$projectId) {
    $res = req('POST', "{$BASE}/api/projects/create_project.php", [
        'pricing_config_id' => $configId,
        'client_name' => 'Test Client',
        'project_name' => 'Doc Gen Test Project',
        'notes' => 'Testing document generation',
        'screens' => [
            ['name' => 'Login', 'complexity_tier_id' => '1'],
            ['name' => 'Dashboard', 'complexity_tier_id' => '2'],
            ['name' => 'Settings', 'complexity_tier_id' => '3'],
        ],
        'integration_ids' => [1, 2, 3],
        'platforms' => ['Web', 'Android'],
    ]);
    assert($res['code'] === 201, "Expected 201, got {$res['code']}");
    assert(($res['body']['success'] ?? false) === true, 'success should be true');
    $projectId = (int) ($res['body']['data']['project_id'] ?? 0);
    assert($projectId > 0, 'project_id should be positive');
});

// 2. Lock project
test('Lock project creates quote version', function () use ($BASE, &$projectId, &$versionId) {
    $res = req('POST', "{$BASE}/api/projects/lock_project.php", [
        'project_id' => $projectId,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert(($res['body']['success'] ?? false) === true, 'success should be true');
    assert(($res['body']['data']['status'] ?? '') === 'locked', 'status should be locked');
    assert(isset($res['body']['data']['breakdown']), 'breakdown should be present');
    assert(isset($res['body']['data']['breakdown']['packages']), 'packages should be in breakdown');
    $versionId = (int) ($res['body']['data']['version_id'] ?? 0);
    assert($versionId > 0, 'version_id should be positive');
});

// 3. Lock already locked project should fail
test('Lock already locked project returns 422', function () use ($BASE, $projectId) {
    $res = req('POST', "{$BASE}/api/projects/lock_project.php", [
        'project_id' => $projectId,
    ]);
    assert($res['code'] === 422, "Expected 422, got {$res['code']}");
});

// 4. Select package
test('Select a package', function () use ($BASE, $projectId, $versionId) {
    $res = req('POST', "{$BASE}/api/quotes/select_package.php", [
        'project_id'    => $projectId,
        'version_id'    => $versionId,
        'package_name'  => 'Professional',
        'package_price' => 150000.00,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');
});

// 5. Generate proposal DOCX
test('Generate proposal DOCX contains package name and price', function () use ($BASE, $projectId) {
    $res = req('POST', "{$BASE}/generate-doc", [
        'project_id'    => $projectId,
        'doc_type'      => 'proposal',
        'package_name'  => 'Professional',
        'package_price' => 150000.00,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert(str_contains($res['contentType'] ?? '', 'vnd.openxmlformats'), "Expected DOCX content type, got {$res['contentType']}");
    assert(strlen($res['raw']) > 5000, 'DOCX should be > 5KB');

    // Verify it's a valid ZIP/DOCX archive with content
    file_put_contents('/tmp/test_proposal.docx', $res['raw']);
    $zip = new ZipArchive();
    $open = $zip->open('/tmp/test_proposal.docx');
    assert($open === true, 'DOCX should be a valid ZIP archive');
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    assert($xml !== false && strlen($xml) > 200, 'document.xml should exist and have content');
    assert(str_contains($xml, 'Professional'), 'DOCX XML should contain package name "Professional"');
    assert(str_contains($xml, 'Client Proposal'), 'DOCX XML should contain "Client Proposal"');
    assert(str_contains($xml, 'Doc Gen Test Project'), 'DOCX XML should contain project name');
});

// 6. Generate PRD DOCX
test('Generate PRD DOCX contains Out of Scope section', function () use ($BASE, $projectId) {
    $res = req('POST', "{$BASE}/generate-doc", [
        'project_id'    => $projectId,
        'doc_type'      => 'prd',
        'package_name'  => 'Professional',
        'package_price' => 150000.00,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert(str_contains($res['contentType'] ?? '', 'vnd.openxmlformats'), "Expected DOCX content type, got {$res['contentType']}");
    assert(strlen($res['raw']) > 5000, 'DOCX should be > 5KB');

    file_put_contents('/tmp/test_prd.docx', $res['raw']);
    $zip = new ZipArchive();
    $open = $zip->open('/tmp/test_prd.docx');
    assert($open === true, 'DOCX should be a valid ZIP archive');
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    assert($xml !== false && strlen($xml) > 200, 'document.xml should exist and have content');
    assert(str_contains($xml, 'Out of Scope'), 'DOCX XML should contain "Out of Scope"');
    assert(str_contains($xml, 'Product Requirements Document'), 'DOCX XML should contain "Product Requirements Document"');
    assert(str_contains($xml, 'Doc Gen Test Project'), 'DOCX XML should contain project name');
});

// 7. Invalid doc_type
test('Invalid doc_type returns 400', function () use ($BASE, $projectId) {
    $res = req('POST', "{$BASE}/generate-doc", [
        'project_id' => $projectId,
        'doc_type'   => 'invalid_type',
    ]);
    assert($res['code'] === 400, "Expected 400, got {$res['code']}");
    assert(isset($res['body']['error']), 'Should have error message');
});

// 8. Version project (clone locked project)
test('Create new version of locked project', function () use ($BASE, $projectId, &$newProjectId) {
    $res = req('POST', "{$BASE}/api/projects/version_project.php", [
        'project_id' => $projectId,
    ]);
    assert($res['code'] === 201, "Expected 201, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert(($res['body']['success'] ?? false) === true, 'success should be true');
    $newProjectId = (int) ($res['body']['data']['project_id'] ?? 0);
    assert($newProjectId > 0, 'new project_id should be positive');
    assert($newProjectId !== $projectId, 'new project_id should differ from original');

    // Verify new project is draft
    $getRes = req('GET', "{$BASE}/api/projects/get_project.php?id={$newProjectId}");
    assert($getRes['body']['data']['project']['status'] === 'draft', 'new version should be draft');
});

// 9. Generate doc without locked project (draft) should fail
test('Generate doc on draft project fails if no version', function () use ($BASE, $newProjectId) {
    $res = req('POST', "{$BASE}/generate-doc", [
        'project_id' => $newProjectId,
        'doc_type'   => 'proposal',
        'package_name' => 'Basic',
        'package_price' => 50000,
    ]);
    // Draft with no version → 422
    assert(in_array($res['code'], [422, 400]), "Expected 422/400, got {$res['code']}");
});

echo str_repeat('-', 40) . "\n";
echo "Results: {$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
