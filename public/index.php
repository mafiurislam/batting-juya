<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

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

// 2. Ensure required writable storage & cache directories exist on Hostinger Linux
foreach ([
    '/storage/app/public',
    '/storage/framework/cache/data',
    '/storage/framework/cache/locks',
    '/storage/framework/sessions',
    '/storage/framework/views',
    '/storage/logs',
    '/bootstrap/cache',
] as $dir) {
    $fullDir = $basePath . $dir;
    if (!is_dir($fullDir)) {
        @mkdir($fullDir, 0775, true);
    }
}

// 3. Ensure SQLite database file exists on first deployment (copies pre-seeded snapshot)
$sqlitePath = $basePath . '/database/database.sqlite';
$initialSqlitePath = $basePath . '/database/data/initial_database.sqlite';
if (!file_exists($sqlitePath)) {
    if (file_exists($initialSqlitePath)) {
        @copy($initialSqlitePath, $sqlitePath);
        @chmod($sqlitePath, 0664);
    } else {
        @touch($sqlitePath);
        @chmod($sqlitePath, 0664);
    }
}

// 4. Ensure public/storage symlink exists for media uploads
if (!file_exists($basePath . '/public/storage') && is_dir($basePath . '/storage/app/public')) {
    @symlink($basePath . '/storage/app/public', $basePath . '/public/storage');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
