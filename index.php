<?php

/**
 * Hostinger / Apache / LiteSpeed Root Front Controller
 * Routes requests cleanly into public/index.php when the repository is cloned
 * directly into public_html via Hostinger Git Deployment.
 */

// Production error reporting: display errors during initial deployment troubleshooting
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 1. Instant static asset streaming fallback if requested directly from root
$requestUri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
if ($requestUri !== '/' && $requestUri !== '' && $requestUri !== '/index.php') {
    $publicCandidate = __DIR__ . '/public' . $requestUri;
    if (file_exists($publicCandidate) && is_file($publicCandidate)) {
        $ext = strtolower(pathinfo($publicCandidate, PATHINFO_EXTENSION));
        $mimes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'webmanifest' => 'application/manifest+json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'mp4' => 'video/mp4',
        ];
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($publicCandidate));
        header('Cache-Control: public, max-age=86400');
        readfile($publicCandidate);
        exit;
    }
}

// 2. Instant health check: https://yourdomain.com/?health_test=1
if (isset($_GET['health_test'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'ok',
        'php' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'os' => PHP_OS,
        'extensions' => [
            'pdo' => extension_loaded('pdo'),
            'pdo_sqlite' => extension_loaded('pdo_sqlite'),
            'sqlite3' => extension_loaded('sqlite3'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'curl' => extension_loaded('curl'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
        ],
        'env_exists' => file_exists(__DIR__ . '/.env'),
        'database_exists' => file_exists(__DIR__ . '/database/database.sqlite'),
        'database_size' => file_exists(__DIR__ . '/database/database.sqlite') ? filesize(__DIR__ . '/database/database.sqlite') : 0,
        'storage_writable' => is_writable(__DIR__ . '/storage'),
        'database_writable' => is_writable(__DIR__ . '/database'),
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    require __DIR__ . '/public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Application Startup Error</title>';
    echo '<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0b132b;color:#e0e6ed;padding:30px;line-height:1.6;}';
    echo '.container{max-width:900px;margin:0 auto;background:#1c2541;border-radius:12px;padding:24px;border:1px solid #3a506b;}';
    echo 'h1{color:#ff5a5f;margin-top:0;font-size:22px;}';
    echo 'pre{background:#0b132b;padding:16px;border-radius:8px;overflow-x:auto;color:#a5b4fc;font-size:13px;}';
    echo '.btn{display:inline-block;padding:10px 18px;background:#3a86ff;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;margin-top:15px;}';
    echo '</style></head><body><div class="container">';
    echo '<h1>Application Startup Error</h1>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '<a class="btn" href="/index.html">Open Static Version</a>';
    echo '</div></body></html>';
    exit;
}
