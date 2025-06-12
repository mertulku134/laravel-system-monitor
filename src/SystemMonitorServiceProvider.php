<?php
namespace Aoux\SystemMonitor;

use Illuminate\Support\ServiceProvider;
use Aoux\SystemMonitor\Services\CacheMonitor;
use Aoux\SystemMonitor\Services\QueueMonitor;
use Aoux\SystemMonitor\Services\RedisMonitor;
use Aoux\SystemMonitor\Console\Commands\MonitorCommand;
use Aoux\SystemMonitor\Middleware\SystemMonitorMiddleware;

class SystemMonitorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/system-monitor.php', 'system-monitor'
        );

        // Cache Monitor servisini kaydet
        $this->app->singleton(CacheMonitor::class, function ($app) {
            return new CacheMonitor($app);
        });

        // Queue Monitor servisini kaydet
        $this->app->singleton(QueueMonitor::class, function ($app) {
            return new QueueMonitor($app);
        });

        // Redis Monitor servisini kaydet
        $this->app->singleton(RedisMonitor::class, function ($app) {
            return new RedisMonitor($app['redis']);
        });

        // Ana SystemMonitor servisini kaydet
        $this->app->singleton('system-monitor', function ($app) {
            return new SystemMonitor($app);
        });
    }

    public function boot()
    {
        // Config dosyasını yayınla
        $this->publishes([
            __DIR__.'/../config/system-monitor.php' => config_path('system-monitor.php'),
        ], 'config');

        // View dosyalarını yayınla
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'system-monitor');

        // Route dosyalarını yükle
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Migration dosyalarını yükle
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Assets dosyalarını yayınla
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/system-monitor/js'),
        ], 'assets');

        // Komutları kaydet
        if ($this->app->runningInConsole()) {
            $this->commands([
                MonitorCommand::class,
            ]);
        }

        // Middleware'i kaydet
        $this->app['router']->aliasMiddleware('system.monitor', SystemMonitorMiddleware::class);
    }
}