<?php

namespace Aoux\SystemMonitor\Controllers;

use Illuminate\Routing\Controller;
use Aoux\SystemMonitor\Services\CacheMonitor;
use Aoux\SystemMonitor\Services\QueueMonitor;
use Aoux\SystemMonitor\Services\RedisMonitor;
use Aoux\SystemMonitor\Services\LogMonitor;
use Aoux\SystemMonitor\Services\SystemResourceMonitor;

class SystemMonitorController extends Controller
{
    protected $cacheMonitor;
    protected $queueMonitor;
    protected $redisMonitor;
    protected $logMonitor;
    protected $systemMonitor;

    public function __construct(
        CacheMonitor $cacheMonitor,
        QueueMonitor $queueMonitor,
        RedisMonitor $redisMonitor,
        LogMonitor $logMonitor,
        SystemResourceMonitor $systemMonitor
    ) {
        $this->cacheMonitor = $cacheMonitor;
        $this->queueMonitor = $queueMonitor;
        $this->redisMonitor = $redisMonitor;
        $this->logMonitor = $logMonitor;
        $this->systemMonitor = $systemMonitor;
    }

    public function index()
    {
        $data = [
            'cache' => $this->cacheMonitor->getStatus(),
            'queue' => $this->queueMonitor->getStatus(),
            'redis' => $this->redisMonitor->getStatus(),
            'logs' => $this->logMonitor->getLogs(),
            'system' => $this->systemMonitor->getStatus(),
            'config' => config('system-monitor')
        ];

        return view(config('system-monitor.view[index]', 'admin.monitor.index'), $data);
    }

    public function retryJob($id)
    {
        $result = $this->queueMonitor->retryJob($id);
        return response()->json(['success' => $result]);
    }

    public function deleteJob($id)
    {
        $result = $this->queueMonitor->deleteJob($id);
        return response()->json(['success' => $result]);
    }

    public function deleteCacheKey($key)
    {
        $result = $this->cacheMonitor->deleteKey($key);
        return response()->json(['success' => $result]);
    }

    public function clearLogs()
    {
        $result = $this->logMonitor->clearLogs();
        return response()->json(['success' => $result]);
    }
} 