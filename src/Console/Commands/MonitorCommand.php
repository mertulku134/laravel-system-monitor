<?php

namespace Aoux\SystemMonitor\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class MonitorCommand extends Command
{
    protected $signature = 'system:monitor';
    protected $description = 'Sistem durumunu komut satırından görüntüle';

    public function handle()
    {
        $this->info('Sistem Durumu');
        $this->newLine();

        // Cache Durumu
        $this->info('Cache Durumu:');
        $this->table(
            ['Özellik', 'Değer'],
            [
                ['Driver', config('cache.default')],
                ['Durum', Cache::get('test') !== null ? 'Aktif' : 'Pasif'],
                ['Anahtar Sayısı', count(Cache::get('keys', []))],
                ['Bellek Kullanımı', $this->formatBytes(memory_get_usage())],
            ]
        );

        // Queue Durumu
        $this->info('Queue Durumu:');
        $this->table(
            ['Özellik', 'Değer'],
            [
                ['Driver', config('queue.default')],
                ['Durum', Queue::size() !== null ? 'Aktif' : 'Pasif'],
                ['Bekleyen İşler', Queue::size()],
                ['Başarısız İşler', $this->getFailedJobsCount()],
            ]
        );

        // Redis Durumu
        if (config('cache.default') === 'redis') {
            $this->info('Redis Durumu:');
            $info = Redis::info();
            $this->table(
                ['Özellik', 'Değer'],
                [
                    ['Durum', 'Aktif'],
                    ['Kullanılan Bellek', $this->formatBytes($info['used_memory'])],
                    ['Bağlı İstemciler', $info['connected_clients']],
                    ['Çalışma Süresi', $this->formatUptime($info['uptime_in_seconds'])],
                    ['Hit Oranı', $this->calculateHitRate($info) . '%'],
                ]
            );
        }
    }

    private function getFailedJobsCount()
    {
        try {
            return \DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "{$days}g {$hours}s {$minutes}d";
    }

    private function calculateHitRate($info)
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }
} 