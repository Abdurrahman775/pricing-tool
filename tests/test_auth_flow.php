<?php
declare(strict_types=1);

/**
 * Manual Auth Flow Test
 * Run: php tests/test_auth_flow.php
 * Requires: MySQL running, schema imported, dev server running on localhost:8000
 *
 * Tests:
 * 1. Register a new user
 * 2. Login with correct credentials
 * 3. Access protected route (/api/auth/me.php)
 * 4. Logout
 * 5. Access protected route after logout (should fail)
 * 6. Login with wrong password (should fail)
 * 7. Register with duplicate email (should fail)
 */

$ROOT = 'http://localhost:8000';
$BASE = "{$ROOT}/api/auth";
$pass = 0;
$fail = 0;

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

function fetchCsrf(): string
{
    global $ROOT;
    $ch = curl_init("{$ROOT}/");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => '/tmp/test_cookies.txt',
        CURLOPT_COOKIEFILE     => '/tmp/test_cookies.txt',
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    preg_match('/<meta name="csrf-token" content="([^"]+)">/', $html, $m);
    return $m[1] ?? '';
}

function post(string $url, array $data, string $csrfToken = ''): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-CSRF-Token: ' . $csrfToken,
        ],
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_COOKIEJAR      => '/tmp/test_cookies.txt',
        CURLOPT_COOKIEFILE     => '/tmp/test_cookies.txt',
    ]);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => json_decode($body, true)];
}

function get(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_COOKIEJAR      => '/tmp/test_cookies.txt',
        CURLOPT_COOKIEFILE     => '/tmp/test_cookies.txt',
    ]);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => json_decode($body, true)];
}

echo "Auth Flow Test\n";
echo str_repeat('-', 40) . "\n";

$testEmail = 'test_' . time() . '@example.com';
$testPass  = 'TestPass123!';

// Fresh session — grab a CSRF token before any POST
@unlink('/tmp/test_cookies.txt');
$csrfToken = fetchCsrf();

// 1. Register
test('Register new user', function () use ($BASE, $testEmail, $testPass, $csrfToken) {
    $res = post("{$BASE}/register.php", [
        'name'     => 'Test User',
        'email'    => $testEmail,
        'password' => $testPass,
    ], $csrfToken);
    assert($res['code'] === 201, "Expected 201, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');
    assert(isset($res['body']['data']['user']['id']), 'user id not returned');
});

// 2. Duplicate email
test('Register duplicate email', function () use ($BASE, $testEmail, $testPass, $csrfToken) {
    $res = post("{$BASE}/register.php", [
        'name'     => 'Test User 2',
        'email'    => $testEmail,
        'password' => $testPass,
    ], $csrfToken);
    assert($res['code'] === 409, "Expected 409, got {$res['code']}");
});

// Clear cookies to simulate fresh session
@unlink('/tmp/test_cookies.txt');
$csrfToken = fetchCsrf();

// 3. Login
test('Login with correct credentials', function () use ($BASE, $testEmail, $testPass, $csrfToken) {
    $res = post("{$BASE}/login.php", [
        'email'    => $testEmail,
        'password' => $testPass,
    ], $csrfToken);
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');
});

// 4. Login with wrong password
test('Login with wrong password', function () use ($BASE, $testEmail, $csrfToken) {
    $res = post("{$BASE}/login.php", [
        'email'    => $testEmail,
        'password' => 'WrongPassword999!',
    ], $csrfToken);
    assert($res['code'] === 401, "Expected 401, got {$res['code']}");
    assert($res['body']['success'] === false, 'success should be false');
});

// 5. Access protected route while logged in
test('Access protected route (authenticated)', function () use ($BASE) {
    $res = get("{$BASE}/me.php");
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');
    assert(isset($res['body']['data']['user']['email']), 'user email not returned');
});

// 6. Logout
test('Logout', function () use ($BASE, $csrfToken) {
    $res = post("{$BASE}/logout.php", [], $csrfToken);
    assert($res['code'] === 200, "Expected 200, got {$res['code']} — " . ($res['body']['error'] ?? ''));
    assert($res['body']['success'] === true, 'success should be true');
});

// Clear cookies to simulate fresh session after logout
@unlink('/tmp/test_cookies.txt');

// 7. Access protected route after logout
test('Protected route rejected after logout', function () use ($BASE) {
    $res = get("{$BASE}/me.php");
    assert($res['code'] === 401, "Expected 401, got {$res['code']}");
    assert($res['body']['success'] === false, 'success should be false');
});

echo str_repeat('-', 40) . "\n";
echo "Results: {$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
