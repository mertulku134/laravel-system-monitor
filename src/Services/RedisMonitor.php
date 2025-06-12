<?php
namespace Aoux\SystemMonitor\Services;

use Illuminate\Support\Facades\Redis;

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