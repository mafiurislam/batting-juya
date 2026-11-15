<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register REGEXP function for SQLite connection so regex queries work seamlessly
        try {
            if (config('database.default') === 'sqlite') {
                $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
                if ($pdo && method_exists($pdo, 'sqliteCreateFunction')) {
                    $pdo->sqliteCreateFunction('REGEXP', function ($pattern, $value) {
                        if ($value === null) return false;
                        return (bool) @preg_match('/' . str_replace('/', '\/', $pattern) . '/', (string) $value);
                    }, 2);
                }
            }
        } catch (\Throwable $e) {
            // Ignore if connection not ready
        }

        // 1. Dynamic URL & HTTPS detection for Hostinger / Production domains
        if (!$this->app->runningInConsole() && !$this->app->runningUnitTests()) {
            $request = request();
            $host = $request->getHost();

            if ($host && !in_array($host, ['127.0.0.1', 'localhost'], true)) {
                $isSecure = $request->isSecure()
                    || strtolower((string) $request->header('X-Forwarded-Proto')) === 'https'
                    || strtolower((string) $request->server('HTTPS')) === 'on'
                    || str_starts_with((string) config('app.url'), 'https://');

                $scheme = $isSecure ? 'https' : $request->getScheme();
                $rootUrl = $scheme . '://' . $request->getHttpHost();

                config(['app.url' => $rootUrl]);
                URL::forceRootUrl($rootUrl);

                if ($isSecure) {
                    URL::forceScheme('https');
                }
            }
        }

        // 2. Self-healing Auto-Migration & Auto-Seeding on first live deployment
        if (!$this->app->runningUnitTests() && !$this->app->runningInConsole()) {
            $flagFile = storage_path('framework/db_ready_' . md5((string) config('database.default') . '_' . (string) config('database.connections.' . config('database.default') . '.database')) . '.flag');

            if (!file_exists($flagFile)) {
                try {
                    if (!Schema::hasTable('users')) {
                        Artisan::call('migrate', ['--force' => true]);
                        Artisan::call('db:seed', ['--force' => true]);
                    } elseif (User::count() === 0) {
                        Artisan::call('db:seed', ['--force' => true]);
                    }

                    if (Schema::hasTable('users') && User::count() > 0) {
                        @file_put_contents($flagFile, now()->toIso8601String());
                    }
                } catch (Throwable $e) {
                    // Fail gracefully without crashing boot if DB credentials are still being configured
                }
            }
        }
    }
}
