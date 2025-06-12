<?php

use Aoux\SystemMonitor\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;
use Aoux\SystemMonitor\Controllers\SystemMonitorController;

$url = config('system-monitor.url', 'system-monitor');
$middleware = config('system-monitor.middleware', ['web', 'auth']);

Route::prefix($url)->middleware($middleware)->group(function () {
    Route::get('/', [MonitorController::class, 'index'])->name('admin.system_monitor.index');
    Route::get('/user-activity', [MonitorController::class, 'userActivity'])->name('admin.system_monitor.user_activity');
    Route::get('/cache/{key}', [MonitorController::class, 'viewCacheKey'])->name('system-monitor.cache.view');
    Route::delete('/cache/{key}', [MonitorController::class, 'deleteCacheKey'])->name('system-monitor.cache.delete');
    Route::post('/jobs/{id}/retry', [MonitorController::class, 'retryJob'])->name('system-monitor.jobs.retry');
    Route::delete('/jobs/{id}', [MonitorController::class, 'deleteJob'])->name('system-monitor.jobs.delete');
});

Route::prefix(config('system-monitor.url', 'admin/monitor'))->group(function () {
    Route::get('/', [SystemMonitorController::class, 'index'])
        ->name('system.monitor')
        ->middleware(config('system-monitor.middleware', ['web', 'auth', 'system.monitor']));

    Route::post('/jobs/{id}/retry', [SystemMonitorController::class, 'retryJob'])
        ->name('system.monitor.jobs.retry')
        ->middleware(config('system-monitor.middleware', ['web', 'auth', 'system.monitor']));

    Route::delete('/jobs/{id}', [SystemMonitorController::class, 'deleteJob'])
        ->name('system.monitor.jobs.delete')
        ->middleware(config('system-monitor.middleware', ['web', 'auth', 'system.monitor']));

    Route::delete('/cache/{key}', [SystemMonitorController::class, 'deleteCacheKey'])
        ->name('system.monitor.cache.delete')
        ->middleware(config('system-monitor.middleware', ['web', 'auth', 'system.monitor']));

    Route::delete('/logs', [SystemMonitorController::class, 'clearLogs'])
        ->name('system.monitor.logs.clear')
        ->middleware(config('system-monitor.middleware', ['web', 'auth', 'system.monitor']));
});