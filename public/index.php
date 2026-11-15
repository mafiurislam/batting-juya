<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('LARAVEL_START', microtime(true));

$basePath = dirname(__DIR__);

// 1. Self-healing .env check for Hostinger Git Deployment
if (!file_exists($basePath . '/.env')) {
    if (file_exists($basePath . '/.env.production')) {
        @copy($basePath . '/.env.production', $basePath . '/.env');
    } elseif (file_exists($basePath . '/.env.example')) {
        @copy($basePath . '/.env.example', $basePath . '/.env');
    }
}
if (file_exists($basePath . '/.env')) {
    @chmod($basePath . '/.env', 0666);
}

// 2. Ensure required writable storage, database & cache directories exist on Hostinger Linux
$criticalDirs = [
    $basePath . '/storage',
    $basePath . '/storage/app',
    $basePath . '/storage/app/public',
    $basePath . '/storage/framework',
    $basePath . '/storage/framework/cache',
    $basePath . '/storage/framework/cache/data',
    $basePath . '/storage/framework/cache/locks',
    $basePath . '/storage/framework/sessions',
    $basePath . '/storage/framework/views',
    $basePath . '/storage/logs',
    $basePath . '/bootstrap/cache',
    $basePath . '/database',
    $basePath . '/database/data',
];

foreach ($criticalDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    @chmod($dir, 0777);
}

$logFile = $basePath . '/storage/logs/laravel.log';
if (!file_exists($logFile)) {
    @touch($logFile);
}
@chmod($logFile, 0666);

// 3. Ensure SQLite database file exists on first deployment (copies pre-seeded snapshot)
$sqlitePath = $basePath . '/database/database.sqlite';
$initialSqlitePath = $basePath . '/database/data/initial_database.sqlite';
if (!file_exists($sqlitePath) || filesize($sqlitePath) === 0) {
    if (file_exists($initialSqlitePath) && filesize($initialSqlitePath) > 0) {
        @copy($initialSqlitePath, $sqlitePath);
        @chmod($sqlitePath, 0666);
    } else {
        @touch($sqlitePath);
        @chmod($sqlitePath, 0666);
    }
} else {
    @chmod($sqlitePath, 0666);
}

// 4. Ensure public/storage symlink exists for media uploads
if (function_exists('symlink') && !file_exists($basePath . '/public/storage') && is_dir($basePath . '/storage/app/public')) {
    try {
        @symlink($basePath . '/storage/app/public', $basePath . '/public/storage');
    } catch (\Throwable $e) {
        // Symlinks may be disabled by hosting provider, ignore safely
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
try {
    /** @var Application $app */
    $app = require_once $basePath . '/bootstrap/app.php';
    $app->handleRequest(Request::capture());
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
