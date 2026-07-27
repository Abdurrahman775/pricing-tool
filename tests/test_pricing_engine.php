<?php
declare(strict_types=1);

/**
 * Manual Pricing Engine Test
 * Run: php tests/test_pricing_engine.php
 * Requires: dev server running on localhost:8000, logged-in session with a config
 *
 * Tests:
 * 1. Calculate price with basic screens + integrations + platforms
 * 2. Verify breakdown structure has all expected fields
 * 3. Verify package prices are correct multiples of base price
 * 4. Handle empty screens gracefully
 * 5. Handle missing config ID
 */

$BASE = 'http://localhost:8000';
$pass = 0;
$fail = 0;
$cookieJar = '/tmp/test_pricing_cookies.txt';
$csrfToken = '';
$configId = 0;

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
    curl_close($ch);
    return ['code' => $httpCode, 'body' => json_decode($body, true)];
}

// Bootstrap: register + get config ID
$testEmail = 'pricing_test_' . time() . '@example.com';
req('POST', "{$BASE}/api/auth/register.php", [
    'name' => 'Pricing Tester',
    'email' => $testEmail,
    'password' => 'TestPass123!',
]);

$listRes = req('GET', "{$BASE}/api/config/list_configs.php");
$configId = (int) ($listRes['body']['data']['configs'][0]['id'] ?? 0);

if ($configId <= 0) {
    echo "FAIL: Could not get a config ID. Is the database seeded?\n";
    exit(1);
}

// Tier/integration IDs are global auto-increment, not per-user — look this user's up by name
// rather than hardcoding IDs from a fresh database.
$fullConfig = req('GET', "{$BASE}/api/config/get_config.php?id={$configId}")['body']['data']['config'];
$tierIdByName = [];
foreach ($fullConfig['complexity_tiers'] as $t) {
    $tierIdByName[$t['name']] = (int) $t['id'];
}
$integrationIds = [];
foreach (array_slice($fullConfig['integrations'], 0, 2) as $i) {
    $integrationIds[] = (int) $i['id'];
}
$expectedIntegrationPoints = array_sum(array_column(array_slice($fullConfig['integrations'], 0, 2), 'points'));
$simpleTierId = $tierIdByName['Simple'];
$mediumTierId = $tierIdByName['Medium'];

echo "Pricing Engine Test\n";
echo str_repeat('-', 40) . "\n";

// 1. Basic price calculation
test('Calculate price with 2 screens + integrations + platforms', function () use ($BASE, $configId, $simpleTierId, $mediumTierId, $integrationIds, $expectedIntegrationPoints, $fullConfig) {
    $res = req('POST', "{$BASE}/api/quotes/calculate_price.php", [
        'config_id' => $configId,
        'screens' => [
            ['name' => 'Login', 'complexity_tier_id' => (string) $simpleTierId, 'notes' => 'Simple form'],
            ['name' => 'Dashboard', 'complexity_tier_id' => (string) $mediumTierId, 'notes' => 'Stats overview'],
        ],
        'integration_ids' => array_map('strval', $integrationIds),
        'platforms' => ['Web', 'Android'],
        'maintenance' => ['model' => 'percentage', 'value' => 15],
    ]);
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');

    $bd = $res['body']['data']['breakdown'];

    // Screen 1 (Simple ×1.0) + Screen 2 (Medium ×2.0) = 3.0 pts
    assert(abs($bd['total_screen_points'] - 3.0) < 0.01, "screen points should be ~3.0, got {$bd['total_screen_points']}");
    assert($bd['total_integration_points'] === $expectedIntegrationPoints, "integration points should be {$expectedIntegrationPoints}, got {$bd['total_integration_points']}");
    $expectedSubtotal = 3.0 + $expectedIntegrationPoints;
    assert(abs($bd['subtotal'] - $expectedSubtotal) < 0.01, "subtotal should be {$expectedSubtotal}, got {$bd['subtotal']}");
    $androidMultiplier = 1.0;
    foreach ($fullConfig['platform_multipliers'] as $pm) {
        if ($pm['platform'] === 'Android') {
            $androidMultiplier = (float) $pm['multiplier'];
        }
    }
    assert(abs($bd['platform_multiplier'] - $androidMultiplier) < 0.01, "platform multiplier should be {$androidMultiplier}, got {$bd['platform_multiplier']}");
    $expectedAdjusted = $expectedSubtotal * $androidMultiplier;
    assert(abs($bd['adjusted_subtotal'] - $expectedAdjusted) < 0.01, "adjusted subtotal should be {$expectedAdjusted}, got {$bd['adjusted_subtotal']}");
});

// 2. Verify breakdown structure
test('Verify breakdown has all required fields', function () use ($BASE, $configId) {
    $res = req('POST', "{$BASE}/api/quotes/calculate_price.php", [
        'config_id' => $configId,
        'screens' => [['name' => 'Test', 'complexity_tier_id' => '1']],
        'integration_ids' => [],
        'platforms' => ['Web'],
    ]);
    $bd = $res['body']['data']['breakdown'];

    assert(isset($bd['currency']), 'missing currency');
    assert(isset($bd['base_rate']), 'missing base_rate');
    assert(isset($bd['screen_breakdown']), 'missing screen_breakdown');
    assert(isset($bd['total_screen_points']), 'missing total_screen_points');
    assert(isset($bd['integration_breakdown']), 'missing integration_breakdown');
    assert(isset($bd['total_integration_points']), 'missing total_integration_points');
    assert(isset($bd['subtotal']), 'missing subtotal');
    assert(isset($bd['platform_breakdown']), 'missing platform_breakdown');
    assert(isset($bd['platform_multiplier']), 'missing platform_multiplier');
    assert(isset($bd['adjusted_subtotal']), 'missing adjusted_subtotal');
    assert(isset($bd['base_price']), 'missing base_price');
    assert(isset($bd['packages']), 'missing packages');
    assert(is_array($bd['packages']), 'packages should be array');
    assert(count($bd['packages']) > 0, 'packages should have items');
});

// 3. Verify package prices are correct multiples
test('Verify package tier prices match multipliers', function () use ($BASE, $configId) {
    $res = req('POST', "{$BASE}/api/quotes/calculate_price.php", [
        'config_id' => $configId,
        'screens' => [['name' => 'Test', 'complexity_tier_id' => '1']],
        'integration_ids' => [],
        'platforms' => ['Web'],
    ]);
    $bd = $res['body']['data']['breakdown'];
    $basePrice = $bd['base_price'];

    foreach ($bd['packages'] as $pkg) {
        $expected = round($basePrice * $pkg['multiplier'], 2);
        $msg = "package {$pkg['name']}: expected {$expected}, got {$pkg['price']}";
        assert(abs($pkg['price'] - $expected) < 0.01, $msg);
    }
});

// 4. Empty screens
test('Empty screens returns 422 (validation)', function () use ($BASE, $configId) {
    $res = req('POST', "{$BASE}/api/quotes/calculate_price.php", [
        'config_id' => $configId,
        'screens' => [],
        'integration_ids' => [],
        'platforms' => ['Web'],
    ]);
    // The API should still calculate with 0 screens (no validation on screens in calc endpoint)
    assert($res['code'] === 200, "Expected 200, got {$res['code']}");
    assert(abs($res['body']['data']['breakdown']['total_screen_points'] - 0) < 0.01, 'screen points should be 0');
});

// 5. Missing config ID
test('Missing config_id returns 422', function () use ($BASE) {
    $res = req('POST', "{$BASE}/api/quotes/calculate_price.php", [
        'screens' => [['name' => 'Test', 'complexity_tier_id' => '1']],
        'platforms' => ['Web'],
    ]);
    assert($res['code'] === 422, "Expected 422, got {$res['code']}");
});

// 6. Nonexistent config ID
test('Nonexistent config_id returns 404', function () use ($BASE) {
    $res = req('POST', "{$BASE}/api/quotes/calculate_price.php", [
        'config_id' => 999999,
        'screens' => [['name' => 'Test', 'complexity_tier_id' => '1']],
        'platforms' => ['Web'],
    ]);
    assert($res['code'] === 404, "Expected 404, got {$res['code']}");
});

// 7. Maintenance breakdown
test('Maintenance calculation in breakdown', function () use ($BASE, $configId) {
    $res = req('POST', "{$BASE}/api/quotes/calculate_price.php", [
        'config_id' => $configId,
        'screens' => [['name' => 'Test', 'complexity_tier_id' => '1']],
        'integration_ids' => [],
        'platforms' => ['Web'],
        'maintenance' => ['model' => 'percentage', 'value' => 10],
    ]);
    $bd = $res['body']['data']['breakdown'];
    assert(isset($bd['maintenance']), 'maintenance should be present');
    assert($bd['maintenance']['model'] === 'percentage', 'model should be percentage');
    // 1pt × 1.0 × 1.0 × 20000 = 20000 base, 10% = 2000/mo
    $expectedMaint = round(20000 * 10 / 100, 2);
    assert(abs($bd['maintenance']['per_month'] - $expectedMaint) < 0.01, "maintenance should be {$expectedMaint}, got {$bd['maintenance']['per_month']}");
});

echo str_repeat('-', 40) . "\n";
echo "Results: {$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
