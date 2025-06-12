<?php
namespace Aoux\SystemMonitor\Controllers;

use Aoux\SystemMonitor\Services\CacheMonitor;
use Aoux\SystemMonitor\Services\QueueMonitor;
use Aoux\SystemMonitor\Services\RedisMonitor;
use Illuminate\Contracts\Foundation\Application;

class MonitorController
{
    protected $app;
    protected $cacheMonitor;
    protected $queueMonitor;
    protected $redisMonitor;

    public function __construct(
        Application $app,
        CacheMonitor $cacheMonitor,
        QueueMonitor $queueMonitor,
        RedisMonitor $redisMonitor
    ) {
        $this->app = $app;
        $this->cacheMonitor = $cacheMonitor;
        $this->queueMonitor = $queueMonitor;
        $this->redisMonitor = $redisMonitor;
    }

    public function index()
    {
        $data = [
            'cache' => $this->cacheMonitor->getDriverInfo(),
            'queue' => $this->queueMonitor->getDriverInfo(),
            'redis' => $this->redisMonitor->getInfo(),
            'failed_jobs' => $this->queueMonitor->getFailedJobs(),
            'cache_keys' => $this->cacheMonitor->getCacheKeys()
        ];

        return view(config('system-monitor.view.index'), $data);
    }

    public function retryJob($id)
    {
        $job = \DB::table('failed_jobs')->find($id);
        
        if ($job) {
            \Artisan::call('queue:retry', ['id' => $id]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function deleteJob($id)
    {
        $job = \DB::table('failed_jobs')->find($id);
        
        if ($job) {
            \DB::table('failed_jobs')->where('id', $id)->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function deleteCacheKey($key)
    {
        try {
            $this->app['cache']->forget($key);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}