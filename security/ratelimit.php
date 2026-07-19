<?php
declare(strict_types=1);

/**
 * Rate limiter using a simple file-based sliding window.
 * For production, replace with Redis or MySQL-based storage.
 *
 * Rate limit key: ratelimit:{identifier}:{action}
 * Window: sliding window of $windowSeconds
 * Max hits: $maxHits per window
 */
function checkRateLimit(string $identifier, string $action, int $maxHits, int $windowSeconds = 900): bool
{
    $storageDir = dirname(__DIR__) . '/backend/cache/ratelimit';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0755, true);
    }

    $key = "rl_{$identifier}_{$action}";
    $file = "{$storageDir}/{$key}.json";

    $data = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?? [];
    }

    $now = time();
    $cutoff = $now - $windowSeconds;

    // Remove expired entries
    $data = array_values(array_filter($data, fn(int $ts) => $ts > $cutoff));

    if (count($data) >= $maxHits) {
        return false; // Rate limited
    }

    $data[] = $now;
    file_put_contents($file, json_encode($data), LOCK_EX);

    return true; // Allowed
}

/**
 * Get remaining attempts for a rate-limited action.
 */
function getRateLimitRemaining(string $identifier, string $action, int $maxHits, int $windowSeconds = 900): int
{
    $storageDir = dirname(__DIR__) . '/backend/cache/ratelimit';
    $key = "rl_{$identifier}_{$action}";
    $file = "{$storageDir}/{$key}.json";

    if (!file_exists($file)) {
        return $maxHits;
    }

    $data = json_decode(file_get_contents($file), true) ?? [];
    $now = time();
    $cutoff = $now - $windowSeconds;

    $data = array_values(array_filter($data, fn(int $ts) => $ts > $cutoff));

    return max(0, $maxHits - count($data));
}
