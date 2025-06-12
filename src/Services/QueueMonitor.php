<?php
namespace Aoux\SystemMonitor\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class QueueMonitor
{
    protected $config;

    public function __construct()
    {
        $this->config = config('system-monitor.queue', []);
    }

    public function getStatus()
    {
        if (!$this->config['enabled']) {
            return null;
        }

        return [
            'driver' => $this->getQueueDriver(),
            'connection' => $this->getQueueConnection(),
            'pending_jobs' => $this->getPendingJobsCount(),
            'failed_jobs' => $this->getFailedJobsCount(),
            'processed_jobs' => $this->getProcessedJobsCount(),
            'retry_after' => $this->getRetryAfter(),
            'timeout' => $this->getTimeout(),
            'max_tries' => $this->getMaxTries(),
            'failed_jobs_table' => $this->getFailedJobsTable(),
        ];
    }

    protected function getQueueDriver()
    {
        return config('queue.default', 'sync');
    }

    protected function getQueueConnection()
    {
        return config('queue.connections.' . $this->getQueueDriver() . '.connection', 'default');
    }

    protected function getPendingJobsCount()
    {
        $driver = $this->getQueueDriver();
        
        if ($driver === 'database') {
            return DB::table('jobs')->count();
        } elseif ($driver === 'redis') {
            return Queue::size('default');
        }
        
        return 0;
    }

    protected function getFailedJobsCount()
    {
        return DB::table($this->getFailedJobsTable())->count();
    }

    protected function getProcessedJobsCount()
    {
        $driver = $this->getQueueDriver();
        
        if ($driver === 'database') {
            return DB::table('jobs')
                ->whereNotNull('reserved_at')
                ->whereNotNull('completed_at')
                ->count();
        }
        
        return 0;
    }

    protected function getRetryAfter()
    {
        return config('queue.connections.' . $this->getQueueDriver() . '.retry_after', 90);
    }

    protected function getTimeout()
    {
        return config('queue.connections.' . $this->getQueueDriver() . '.timeout', 60);
    }

    protected function getMaxTries()
    {
        return config('queue.connections.' . $this->getQueueDriver() . '.tries', 3);
    }

    protected function getFailedJobsTable()
    {
        return config('queue.failed.table', 'failed_jobs');
    }

    public function getFailedJobs($limit = 10)
    {
        return DB::table($this->getFailedJobsTable())
            ->orderBy('failed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function retryJob($id)
    {
        $failedJob = DB::table($this->getFailedJobsTable())->find($id);
        
        if (!$failedJob) {
            return false;
        }

        $job = unserialize($failedJob->payload);
        
        if (!$job) {
            return false;
        }

        dispatch($job);
        
        DB::table($this->getFailedJobsTable())->where('id', $id)->delete();
        
        return true;
    }

    public function deleteJob($id)
    {
        return DB::table($this->getFailedJobsTable())->where('id', $id)->delete();
    }
}