<?php
namespace Aoux\SystemMonitor\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;

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

    public function getDriverInfo(): array
    {
        $driver = Config::get('cache.default');
        $connection = Config::get('cache.stores.' . $driver);

        return [
            'name' => $driver,
            'connection' => $connection['connection'] ?? null,
            'prefix' => $connection['prefix'] ?? '',
            'ttl' => $connection['ttl'] ?? 60,
            'status' => $this->checkConnection(),
            'memory_usage' => $this->getMemoryUsage(),
            'keys_count' => $this->getTotalKeys(),
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

    public function getMemoryUsage(): string
    {
        try {
            $keys = $this->getCacheKeys();
            $totalSize = 0;

            foreach ($keys as $key) {
                $value = Cache::get($key);
                if ($value !== null) {
                    $totalSize += strlen(serialize($value));
                }
            }

            return $this->formatBytes($totalSize);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    public function getStatus(): array
    {
        return [
            'driver' => $this->getDriverInfo(),
            'status' => $this->checkConnection(),
            'total_keys' => $this->getTotalKeys(),
            'memory_usage' => $this->getMemoryUsage()
        ];
    }

    public function getCacheKeys(): array
    {
        try {
            $driver = Config::get('cache.default');
            
            if ($driver === 'redis') {
                return $this->getRedisKeys();
            } elseif ($driver === 'memcached') {
                return $this->getMemcachedKeys();
            } elseif ($driver === 'file') {
                return $this->getFileKeys();
            }
            
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getRedisKeys(): array
    {
        try {
            $redis = Cache::getRedis();
            $keys = $redis->keys(Cache::getPrefix() . '*');
            return array_map(function($key) {
                return str_replace(Cache::getPrefix(), '', $key);
            }, $keys);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getMemcachedKeys(): array
    {
        try {
            $memcached = Cache::getMemcached();
            $keys = [];
            $allKeys = $memcached->getAllKeys();
            
            foreach ($allKeys as $key) {
                if (strpos($key, Cache::getPrefix()) === 0) {
                    $keys[] = str_replace(Cache::getPrefix(), '', $key);
                }
            }
            
            return $keys;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getFileKeys(): array
    {
        try {
            $path = Config::get('cache.stores.file.path');
            $keys = [];
            
            if (is_dir($path)) {
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $keys[] = basename($file);
                    }
                }
            }
            
            return $keys;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function deleteKey($key)
    {
        return Cache::forget($key);
    }

    public function clear()
    {
        return Cache::flush();
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function checkConnection(): bool
    {
        try {
            Cache::put('test_connection', true, 1);
            return Cache::get('test_connection') === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getTotalKeys(): int
    {
        try {
            return count($this->getCacheKeys());
        } catch (\Exception $e) {
            return 0;
        }
    }
}