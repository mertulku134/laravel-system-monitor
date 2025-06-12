<?php
namespace Aoux\SystemMonitor\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheMonitor
{
    protected $app;
    protected $redis;
    protected $driver;
    protected $maxKeys;
    protected $showValues;
    protected $showTtl;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->redis = $app['redis'];
        $this->driver = config('cache.default');
        $this->maxKeys = config('system-monitor.cache.max_keys', 100);
        $this->showValues = config('system-monitor.cache.show_values', true);
        $this->showTtl = config('system-monitor.cache.show_ttl', true);
    }

    public function getDriverInfo()
    {
        $driver = config('cache.default');
        $config = config('cache.stores.' . $driver);

        return [
            'driver' => $driver,
            'config' => $config,
            'status' => $this->checkConnection($driver),
            'keys_count' => $this->getKeysCount(),
            'memory_usage' => $this->getMemoryUsage()
        ];
    }

    public function getKeysCount()
    {
        try {
            return $this->redis->dbSize();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getMemoryUsage()
    {
        try {
            $info = $this->redis->info('memory');
            return $this->formatBytes($info['used_memory']);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    public function getStatus()
    {
        return [
            'driver' => $this->driver,
            'keys' => $this->getCacheKeys(),
            'stats' => $this->getCacheStats()
        ];
    }

    protected function getCacheKeys()
    {
        $keys = [];

        if ($this->driver === 'redis') {
            $redis = Redis::connection();
            $keys = $redis->keys('*');
            $keys = array_slice($keys, 0, $this->maxKeys);
            
            return collect($keys)->map(function ($key) use ($redis) {
                $data = [
                    'key' => $key,
                    'value' => $this->showValues ? $redis->get($key) : null,
                    'ttl' => $this->showTtl ? $redis->ttl($key) : null
                ];
                return $data;
            })->toArray();
        }

        // Diğer cache sürücüleri için
        if (method_exists(Cache::getStore(), 'getAll')) {
            $keys = Cache::getStore()->getAll();
            $keys = array_slice($keys, 0, $this->maxKeys);
            
            return collect($keys)->map(function ($value, $key) {
                $data = [
                    'key' => $key,
                    'value' => $this->showValues ? $value : null,
                    'ttl' => $this->showTtl ? Cache::getStore()->getTimeToLive($key) : null
                ];
                return $data;
            })->toArray();
        }

        return [];
    }

    protected function getCacheStats()
    {
        $stats = [
            'driver' => $this->driver,
            'prefix' => config('cache.prefix'),
            'default_ttl' => config('cache.ttl', 60),
        ];

        if ($this->driver === 'redis') {
            $redis = Redis::connection();
            $info = $redis->info();
            
            $stats = array_merge($stats, [
                'used_memory' => $info['used_memory_human'] ?? null,
                'connected_clients' => $info['connected_clients'] ?? null,
                'total_keys' => count($redis->keys('*')),
                'uptime' => $info['uptime_in_seconds'] ?? null,
            ]);
        }

        return $stats;
    }

    public function deleteKey($key)
    {
        return Cache::forget($key);
    }

    public function clear()
    {
        return Cache::flush();
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function checkConnection($driver)
    {
        try {
            $this->app['cache']->store($driver)->put('test', 'test', 1);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}