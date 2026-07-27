<?php
declare(strict_types=1);

/**
 * Manual Config Flow Test
 * Run: php tests/test_config_flow.php
 * Requires: dev server running on localhost:8000, logged-in session cookie
 *
 * Tests:
 * 1. List configs for a logged-in user
 * 2. Get a specific config with full details
 * 3. Duplicate a config
 * 4. Verify config export returns valid JSON
 */

$BASE = 'http://localhost:8000';
$pass = 0;
$fail = 0;
$cookieJar = '/tmp/test_config_cookies.txt';
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

function request(string $method, string $url, array $data = []): array
{
    global $cookieJar, $csrfToken;
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
    ];
    $headers = ['Content-Type: application/json'];
    if ($method === 'POST') {
        $opts[CURLOPT_POST] = true;
        $headers[] = 'X-CSRF-Token: ' . $csrfToken;
        $opts[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => json_decode($body, true)];
}

function getConfigId(array $list): int
{
    return (int) ($list['body']['data']['configs'][0]['id'] ?? 0);
}

// First register/login to get a valid session
$csrfToken = fetchCsrf();
$testEmail = 'config_test_' . time() . '@example.com';
$regRes = request('POST', "{$BASE}/api/auth/register.php", [
    'name' => 'Config Tester',
    'email' => $testEmail,
    'password' => 'TestPass123!',
]);
// We have a session cookie now

echo "Config Flow Test\n";
echo str_repeat('-', 40) . "\n";

// 1. List configs
test('List configs returns array', function () use ($BASE, $regRes) {
    $res = request('GET', "{$BASE}/api/config/list_configs.php");
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert($res['body']['success'] === true, 'success should be true');
    assert(is_array($res['body']['data']['configs']), 'configs should be an array');
    assert(count($res['body']['data']['configs']) > 0, 'should have at least 1 config (from default template)');
});

// 2. Get full config
test('Get full config with children', function () use ($BASE) {
    $list = request('GET', "{$BASE}/api/config/list_configs.php");
    $configId = getConfigId($list);
    assert($configId > 0, 'valid config id from list');

    $res = request('GET', "{$BASE}/api/config/get_config.php?id={$configId}");
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert($res['body']['success'] === true, 'success should be true');
    assert(isset($res['body']['data']['config']['complexity_tiers']), 'should have complexity_tiers');
    assert(isset($res['body']['data']['config']['integrations']), 'should have integrations');
    assert(count($res['body']['data']['config']['complexity_tiers']) >= 1, 'should have at least 1 tier');
});

// 3. Duplicate config
test('Duplicate config', function () use ($BASE) {
    $list = request('GET', "{$BASE}/api/config/list_configs.php");
    $configId = getConfigId($list);
    assert($configId > 0, 'valid config id from list');

    $res = request('POST', "{$BASE}/api/config/duplicate_config.php", [
        'config_id' => $configId,
        'name' => 'Copy - ' . time(),
    ]);
    assert($res['code'] === 201, "Expected 201, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');
    assert(isset($res['body']['data']['config_id']), 'should return new config_id');
});

// 4. Get nonexistent config
test('Get nonexistent config returns 404', function () use ($BASE) {
    $res = request('GET', "{$BASE}/api/config/get_config.php?id=999999");
    assert($res['code'] === 404, "Expected 404, got {$res['code']}");
});

// 5. Duplicate nonexistent config
test('Duplicate nonexistent config returns 404', function () use ($BASE) {
    $res = request('POST', "{$BASE}/api/config/duplicate_config.php", [
        'config_id' => 999999,
        'name' => 'Ghost',
    ]);
    assert($res['code'] === 404, "Expected 404, got {$res['code']}");
});

// 6. Save/update a config
test('Save a new config', function () use ($BASE) {
    $res = request('POST', "{$BASE}/api/config/save_config.php", [
        'name' => 'Test Config ' . time(),
        'currency' => '$',
        'base_rate' => 50000,
        'complexity_tiers' => [
            ['name' => 'Basic', 'multiplier' => 1.0],
            ['name' => 'Pro', 'multiplier' => 2.5],
        ],
        'integrations' => [
            ['name' => 'API', 'points' => 5],
        ],
        'platform_multipliers' => [
            ['platform' => 'Web', 'multiplier' => 1.0],
        ],
        'package_tiers' => [
            ['name' => 'Starter', 'multiplier' => 1.0],
            ['name' => 'Enterprise', 'multiplier' => 2.0],
        ],
        'maintenance' => [
            'model' => 'flat',
            'value' => 5000,
        ],
    ]);
    assert($res['code'] === 201, "Expected 201, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');
    assert($res['body']['data']['config_id'] > 0, 'should return positive config_id');
});

echo str_repeat('-', 40) . "\n";
echo "Results: {$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
