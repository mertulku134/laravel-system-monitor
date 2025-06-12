<?php
namespace Aoux\SystemMonitor\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;

class RedisMonitor
{
    protected $connection;
    protected $showMemory;
    protected $showClients;
    protected $showKeys;

    public function __construct()
    {
        $this->connection = config('database.redis.default');
        $this->showMemory = config('system-monitor.redis.show_memory', true);
        $this->showClients = config('system-monitor.redis.show_clients', true);
        $this->showKeys = config('system-monitor.redis.show_keys', true);
    }

    public function getStatus()
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();

            return [
                'status' => 'connected',
                'version' => $info['redis_version'] ?? null,
                'memory' => $this->showMemory ? $this->getMemoryInfo($info) : null,
                'clients' => $this->showClients ? $this->getClientInfo($info) : null,
                'keys' => $this->showKeys ? $this->getKeysInfo($redis) : null,
                'stats' => $this->getStatsInfo($info)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'disconnected',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getInfo(): array
    {
        return [
            'status' => $this->checkConnection(),
            'connected_clients' => $this->getConnectedClients(),
            'used_memory' => $this->getUsedMemory(),
            'total_keys' => $this->getTotalKeys(),
            'uptime' => $this->getUptime(),
            'hit_rate' => $this->getHitRate()
        ];
    }

    public function checkConnection(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConnectedClients(): int
    {
        try {
            $info = Redis::info();
            return $info['connected_clients'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getUsedMemory(): string
    {
        try {
            $info = Redis::info();
            $usedMemory = $info['used_memory'] ?? 0;
            return $this->formatBytes($usedMemory);
        } catch (\Exception $e) {
            return '0 B';
        }
    }

    public function getTotalKeys(): int
    {
        try {
            return Redis::dbSize();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getUptime(): string
    {
        try {
            $info = Redis::info();
            $uptime = $info['uptime_in_seconds'] ?? 0;
            return $this->formatUptime($uptime);
        } catch (\Exception $e) {
            return '0s';
        }
    }

    public function getHitRate(): float
    {
        try {
            $info = Redis::info();
            $keyspaceHits = $info['keyspace_hits'] ?? 0;
            $keyspaceMisses = $info['keyspace_misses'] ?? 0;
            
            if ($keyspaceHits + $keyspaceMisses === 0) {
                return 0;
            }
            
            return round(($keyspaceHits / ($keyspaceHits + $keyspaceMisses)) * 100, 2);
        } catch (\Exception $e) {
            return 0;
        }
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

    protected function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        $parts = [];
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if ($seconds > 0 || empty($parts)) $parts[] = $seconds . 's';
        
        return implode(' ', $parts);
    }

    protected function getMemoryInfo($info)
    {
        return [
            'used_memory' => $info['used_memory_human'] ?? null,
            'used_memory_peak' => $info['used_memory_peak_human'] ?? null,
            'used_memory_lua' => $info['used_memory_lua_human'] ?? null,
            'mem_fragmentation_ratio' => $info['mem_fragmentation_ratio'] ?? null
        ];
    }

    protected function getClientInfo($info)
    {
        return [
            'connected_clients' => $info['connected_clients'] ?? null,
            'blocked_clients' => $info['blocked_clients'] ?? null,
            'max_clients' => $info['maxclients'] ?? null
        ];
    }

    protected function getKeysInfo($redis)
    {
        $keys = $redis->keys('*');
        $types = [];
        $counts = [];

        foreach ($keys as $key) {
            $type = $redis->type($key);
            $types[$key] = $type;
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        return [
            'total_keys' => count($keys),
            'key_types' => $counts,
            'sample_keys' => array_slice($types, 0, 10)
        ];
    }

    protected function getStatsInfo($info)
    {
        return [
            'uptime' => $info['uptime_in_seconds'] ?? null,
            'total_commands_processed' => $info['total_commands_processed'] ?? null,
            'instantaneous_ops_per_sec' => $info['instantaneous_ops_per_sec'] ?? null,
            'rejected_connections' => $info['rejected_connections'] ?? null,
            'expired_keys' => $info['expired_keys'] ?? null,
            'evicted_keys' => $info['evicted_keys'] ?? null,
            'keyspace_hits' => $info['keyspace_hits'] ?? null,
            'keyspace_misses' => $info['keyspace_misses'] ?? null
        ];
    }
}