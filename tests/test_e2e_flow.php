<?php
declare(strict_types=1);

/**
 * End-to-End Flow Test
 * Run: php tests/test_e2e_flow.php
 * Requires: dev server running on localhost:8000
 *
 * Tests the full user journey:
 * 1. Register → Auto-config created
 * 2. Export config as JSON
 * 3. Import config as new config
 * 4. Create project via intake
 * 5. Lock project → quote version created
 * 6. Select package
 * 7. Generate proposal PDF
 * 8. Generate PRD PDF
 * 9. Version the locked project
 * 10. Regenerate document from old version
 */

$BASE = 'http://localhost:8000';
$pass = 0;
$fail = 0;
$cookieJar = '/tmp/test_e2e_cookies.txt';
$csrfToken = '';

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
    return 'e2e_' . time() . '_' . rand(100, 999) . '@example.com';
}

echo "End-to-End Flow Test\n";
echo str_repeat('-', 40) . "\n";

$email = randomEmail();
$configId = 0;
$projectId = 0;
$versionId = 0;

// 1. Register
test('Register new user with auto-config', function () use ($BASE, $email, &$configId) {
    $res = req('POST', "{$BASE}/api/auth/register.php", [
        'name'     => 'E2E Tester',
        'email'    => $email,
        'password' => 'SecurePass123!',
    ]);
    assert($res['code'] === 201, "Expected 201, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert(($res['body']['success'] ?? false) === true, 'success should be true');

    // Get configs to confirm auto-config was created
    $list = req('GET', "{$BASE}/api/config/list_configs.php");
    assert(($list['body']['success'] ?? false) === true, 'list configs should succeed');
    $configs = $list['body']['data']['configs'] ?? [];
    assert(count($configs) > 0, 'should have at least one config');
    $configId = (int) ($configs[0]['id'] ?? 0);
    assert($configId > 0, 'config ID should be positive');
});

// 2. Get config details (verify structure)
test('Get full config details', function () use ($BASE, &$configId) {
    $res = req('GET', "{$BASE}/api/config/get_config.php?id={$configId}");
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    $cfg = $res['body']['data']['config'] ?? [];
    assert(!empty($cfg['complexity_tiers']), 'should have complexity tiers');
    assert(!empty($cfg['integrations']), 'should have integrations');
    assert(!empty($cfg['platform_multipliers']), 'should have platform multipliers');
    assert(!empty($cfg['package_tiers']), 'should have package tiers');
    assert(!empty($cfg['maintenance']), 'should have maintenance settings');
});

// 3. Export config
test('Export config as JSON', function () use ($BASE, $configId) {
    $res = req('POST', "{$BASE}/generator/export.php", ['config_id' => $configId]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert($res['contentType'] === 'application/json; charset=utf-8', 'should be JSON');
    $parsed = json_decode($res['raw'], true);
    assert($parsed !== null, 'export should be valid JSON');
    assert(isset($parsed['name']), 'export should have name');
    assert(isset($parsed['base_rate']), 'export should have base_rate');
});

// 4. Import config (create new from exported data)
test('Import config from JSON data', function () use ($BASE, $configId) {
    // First get the export data to re-import
    $export = req('POST', "{$BASE}/generator/export.php", ['config_id' => $configId]);
    $importData = json_decode($export['raw'], true);
    $importData['name'] = 'Imported Config';

    $res = req('POST', "{$BASE}/generator/import.php", $importData);
    assert(in_array($res['code'], [201, 200]), "Expected 201/200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert(($res['body']['success'] ?? false) === true, 'success should be true');
    assert(($res['body']['data']['imported'] ?? false) === true, 'imported should be true');
});

// 5. Create project (full intake)
test('Create project with screens + integrations + platforms', function () use ($BASE, $configId, &$projectId) {
    $res = req('POST', "{$BASE}/api/projects/create_project.php", [
        'pricing_config_id' => $configId,
        'client_name'       => 'E2E Client',
        'project_name'      => 'E2E Test Project',
        'notes'             => 'Full end-to-end test',
        'screens' => [
            ['name' => 'Login',       'complexity_tier_id' => '1', 'notes' => 'Email + password'],
            ['name' => 'Dashboard',   'complexity_tier_id' => '2', 'notes' => 'Analytics overview'],
            ['name' => 'User Profile','complexity_tier_id' => '2', 'notes' => 'Edit profile'],
            ['name' => 'Reports',     'complexity_tier_id' => '3', 'notes' => 'Complex charts'],
        ],
        'integration_ids' => [1, 2, 3, 4],
        'platforms' => ['Web', 'Android', 'iOS'],
    ]);
    assert($res['code'] === 201, "Expected 201, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    $projectId = (int) ($res['body']['data']['project_id'] ?? 0);
    assert($projectId > 0, 'project_id should be positive');
});

// 6. Get project detail
test('Get project detail with screens', function () use ($BASE, $projectId) {
    $res = req('GET', "{$BASE}/api/projects/get_project.php?id={$projectId}");
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    $proj = $res['body']['data']['project'] ?? [];
    assert($proj['status'] === 'draft', 'project should be draft');
    assert(count($proj['screens']) === 4, 'should have 4 screens');
    assert(count($proj['integrations']) === 4, 'should have 4 integrations');
    assert(count($proj['platforms']) === 3, 'should have 3 platforms');
});

// 7. Lock project
test('Lock project and create quote version', function () use ($BASE, $projectId, &$versionId) {
    $res = req('POST', "{$BASE}/api/projects/lock_project.php", [
        'project_id' => $projectId,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['data']['status'] === 'locked', 'status should be locked');
    $versionId = (int) ($res['body']['data']['version_id'] ?? 0);
    assert($versionId > 0, 'version_id should be positive');

    // Verify breakdown structure
    $bd = $res['body']['data']['breakdown'] ?? [];
    assert(isset($bd['screen_breakdown']), 'breakdown should have screen_breakdown');
    assert(isset($bd['packages']), 'breakdown should have packages');
    assert(count($bd['packages']) === 3, 'should have 3 package tiers');
});

// 8. Select package
test('Select Professional package', function () use ($BASE, $projectId, $versionId) {
    $res = req('POST', "{$BASE}/api/quotes/select_package.php", [
        'project_id'    => $projectId,
        'version_id'    => $versionId,
        'package_name'  => 'Professional',
        'package_price' => 250000.00,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert(($res['body']['success'] ?? false) === true, 'success should be true');

    // Verify package persisted
    $getVer = req('GET', "{$BASE}/api/quotes/list_versions.php?project_id={$projectId}");
    $versions = $getVer['body']['data']['versions'] ?? [];
    assert(count($versions) > 0, 'should have at least one version');
    assert(($versions[0]['selected_package'] ?? '') === 'Professional', 'package should be Professional');
});

// 9. Generate proposal DOCX
test('Generate proposal DOCX with correct content', function () use ($BASE, $projectId) {
    $res = req('POST', "{$BASE}/generate-doc", [
        'project_id'    => $projectId,
        'doc_type'      => 'proposal',
        'package_name'  => 'Professional',
        'package_price' => 250000.00,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert(str_contains($res['contentType'] ?? '', 'vnd.openxmlformats'), "Expected DOCX, got {$res['contentType']}");
    assert(strlen($res['raw']) > 5000, 'DOCX should be > 5KB');

    file_put_contents('/tmp/test_e2e_proposal.docx', $res['raw']);
    $zip = new ZipArchive();
    $open = $zip->open('/tmp/test_e2e_proposal.docx');
    assert($open === true, 'DOCX should be valid ZIP');
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    assert($xml !== false && strlen($xml) > 200, 'document.xml should have content');
    assert(str_contains($xml, 'Client Proposal'), 'should contain Client Proposal');
    assert(str_contains($xml, 'E2E Test Project'), 'should contain project name');
    assert(str_contains($xml, 'E2E Client'), 'should contain client name');
    assert(str_contains($xml, 'Professional'), 'should contain package name');
    assert(str_contains($xml, 'What\'s Included'), 'should contain included section');
    assert(str_contains($xml, 'What\'s Excluded'), 'should contain excluded section');
    assert(str_contains($xml, 'Payment Terms'), 'should contain payment terms');
    assert(str_contains($xml, 'Timeline'), 'should contain timeline');
});

// 10. Generate PRD DOCX
test('Generate PRD DOCX with out-of-scope section', function () use ($BASE, $projectId) {
    $res = req('POST', "{$BASE}/generate-doc", [
        'project_id'    => $projectId,
        'doc_type'      => 'prd',
        'package_name'  => 'Professional',
        'package_price' => 250000.00,
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert(str_contains($res['contentType'] ?? '', 'vnd.openxmlformats'), "Expected DOCX, got {$res['contentType']}");
    assert(strlen($res['raw']) > 5000, 'DOCX should be > 5KB');

    file_put_contents('/tmp/test_e2e_prd.docx', $res['raw']);
    $zip = new ZipArchive();
    $open = $zip->open('/tmp/test_e2e_prd.docx');
    assert($open === true, 'DOCX should be valid ZIP');
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    assert($xml !== false && strlen($xml) > 200, 'document.xml should have content');
    assert(str_contains($xml, 'Product Requirements Document'), 'should contain PRD header');
    assert(str_contains($xml, 'Out of Scope'), 'should contain Out of Scope section');
    assert(str_contains($xml, 'explicitly excluded'), 'should mention excluded items');
    assert(str_contains($xml, 'Screens &amp; Complexity'), 'should contain screens section');
    assert(str_contains($xml, 'Integrations'), 'should contain integrations section');
    assert(str_contains($xml, 'Platforms'), 'should contain platforms section');
    assert(str_contains($xml, 'Pricing Breakdown'), 'should contain pricing breakdown');
    assert(str_contains($xml, 'Login'), 'should contain Login screen');
    assert(str_contains($xml, 'Dashboard'), 'should contain Dashboard screen');
    assert(str_contains($xml, 'Reports'), 'should contain Reports screen');
});

// 11. List versions
test('List quote versions for project', function () use ($BASE, $projectId) {
    $res = req('GET', "{$BASE}/api/quotes/list_versions.php?project_id={$projectId}");
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    $versions = $res['body']['data']['versions'] ?? [];
    assert(count($versions) >= 1, 'should have at least one version');
    assert(($versions[0]['version_number'] ?? 0) >= 1, 'version number should be >= 1');
    assert(($versions[0]['screen_points'] ?? 0) > 0, 'screen_points should be > 0');
});

// 12. Version project
test('Create new version of locked project', function () use ($BASE, $projectId, &$newProjectId) {
    $res = req('POST', "{$BASE}/api/projects/version_project.php", [
        'project_id' => $projectId,
    ]);
    assert($res['code'] === 201, "Expected 201, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    $newProjectId = (int) ($res['body']['data']['project_id'] ?? 0);
    assert($newProjectId > 0, 'new project_id should be positive');

    // Verify cloned data
    $get = req('GET', "{$BASE}/api/projects/get_project.php?id={$newProjectId}");
    $proj = $get['body']['data']['project'] ?? [];
    assert($proj['status'] === 'draft', 'cloned project should be draft');
    assert(count($proj['screens']) === 4, 'cloned screens count should match');
    assert(count($proj['platforms']) === 3, 'cloned platforms count should match');
});

// 13. Audit trail entries exist
test('Audit trail has entries for all actions', function () use ($BASE) {
    // Check that at least audit log table exists by querying directly
    $db = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=pricing_tool;charset=utf8mb4',
        'root', '309612.Aa',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $db->query('SELECT COUNT(*) FROM audit_log');
    $count = (int) $stmt->fetchColumn();
    assert($count > 0, "audit_log should have entries, got {$count}");

    // Verify specific action types exist
    $actions = $db->query("SELECT DISTINCT action FROM audit_log")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['user.register', 'config.export', 'project.create', 'project.lock',
                  'quote.select_package', 'doc.generate', 'project.version'];
    foreach ($required as $action) {
        assert(in_array($action, $actions), "audit_log should contain action: {$action}");
    }
});

echo str_repeat('-', 40) . "\n";
echo "Results: {$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
