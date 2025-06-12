<?php
namespace Aoux\SystemMonitor\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;

class QueueMonitor
{
    protected $config;

    public function __construct()
    {
        $this->config = config('system-monitor.queue', []);
    }

    public function getStatus(): array
    {
        return [
            'driver' => $this->getDriverInfo(),
            'status' => $this->checkConnection(),
            'pending_jobs' => $this->getPendingJobsCount(),
            'failed_jobs' => $this->getFailedJobsCount()
        ];
    }

    public function getDriverInfo(): array
    {
        $driver = Config::get('queue.default');
        $connection = Config::get('queue.connections.' . $driver);

        return [
            'name' => $driver,
            'connection' => $connection['connection'] ?? null,
            'queue' => $connection['queue'] ?? 'default',
            'retry_after' => $connection['retry_after'] ?? 90,
            'block_for' => $connection['block_for'] ?? null,
            'after_commit' => $connection['after_commit'] ?? false
        ];
    }

    public function checkConnection(): bool
    {
        try {
            Queue::size();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getPendingJobsCount(): int
    {
        try {
            return Queue::size();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getFailedJobsCount(): int
    {
        try {
            return \DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getFailedJobs(int $limit = 10): array
    {
        try {
            return \DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function retryJob(int $id): bool
    {
        try {
            $job = \DB::table('failed_jobs')->find($id);
            if (!$job) {
                return false;
            }

            \DB::table('failed_jobs')->where('id', $id)->delete();
            \Queue::push(json_decode($job->payload, true));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteJob(int $id): bool
    {
        try {
            return \DB::table('failed_jobs')->where('id', $id)->delete() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}