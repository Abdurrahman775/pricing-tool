<?php
declare(strict_types=1);

require_once __DIR__ . '/backend/config/app.php';
loadEnv();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route API requests to backend
if (str_starts_with($uri, '/api/')) {
    $base = realpath(__DIR__ . '/backend');
    $resolved = realpath($base . '/' . ltrim(parse_url($uri, PHP_URL_PATH), '/'));
    if ($resolved !== false && str_starts_with($resolved, $base)) {
        require $resolved;
        return true;
    }
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'API endpoint not found']);
    return true;
}

// Route document generation to generator
if ($uri === '/generate-doc') {
    require __DIR__ . '/generator/generate_doc.php';
    return true;
}

// Route config export/import to generator
if ($uri === '/api/config/export') {
    require __DIR__ . '/generator/export.php';
    return true;
}
if ($uri === '/api/config/import') {
    require __DIR__ . '/generator/import.php';
    return true;
}

// Map root to index.php
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/frontend/public/index.php';
    return true;
}

// Serve .php and static files from frontend/public
$staticFile = __DIR__ . '/frontend/public' . $uri;
if (file_exists($staticFile) && !is_dir($staticFile)) {
    if (str_ends_with($uri, '.php')) {
        require $staticFile;
        return true;
    }
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile($staticFile);
        return true;
    }
    return false;
}

// Fallback 404
http_response_code(404);
echo 'Page not found';
return true;
