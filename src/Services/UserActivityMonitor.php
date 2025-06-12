<?php

namespace Aoux\SystemMonitor\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserActivityMonitor
{
    protected $maxRecords;
    protected $showDetails;
    protected $trackEvents;
    protected $trackErrors;
    protected $trackPerformance;

    public function __construct()
    {
        $this->maxRecords = config('system-monitor.user_activity.max_records', 1000);
        $this->showDetails = config('system-monitor.user_activity.show_details', true);
        $this->trackEvents = config('system-monitor.user_activity.track_events', true);
        $this->trackErrors = config('system-monitor.user_activity.track_errors', true);
        $this->trackPerformance = config('system-monitor.user_activity.track_performance', true);
    }

    public function getStatus()
    {
        return [
            'recent_activities' => $this->getRecentActivities(),
            'active_users' => $this->getActiveUsers(),
            'error_stats' => $this->getErrorStats(),
            'performance_stats' => $this->getPerformanceStats(),
            'event_stats' => $this->getEventStats()
        ];
    }

    protected function getRecentActivities()
    {
        $query = DB::table('user_activities')
            ->select([
                'id',
                'user_id',
                'type',
                'url',
                'method',
                'ip',
                'duration',
                'status',
                'created_at'
            ])
            ->when(!$this->showDetails, function ($query) {
                return $query->select(['id', 'user_id', 'type', 'created_at']);
            })
            ->orderBy('created_at', 'desc')
            ->limit($this->maxRecords);

        $activities = $query->get();

        if ($this->showDetails && $this->trackEvents) {
            $activities->each(function ($activity) {
                $activity->events = DB::table('user_activity_events')
                    ->where('user_activity_id', $activity->id)
                    ->get();
            });
        }

        return $activities;
    }

    protected function getActiveUsers()
    {
        $timeout = config('system-monitor.user_activity.session_timeout', 30);
        $now = now();

        return DB::table('user_activities')
            ->select('user_id', DB::raw('MAX(created_at) as last_activity'))
            ->where('created_at', '>=', $now->subMinutes($timeout))
            ->groupBy('user_id')
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->user_id,
                    'last_activity' => $user->last_activity,
                    'duration' => $now->diffInMinutes($user->last_activity)
                ];
            });
    }

    protected function getErrorStats()
    {
        if (!$this->trackErrors) {
            return null;
        }

        $period = config('system-monitor.user_activity.error_stats_period', 24);
        $now = now();

        return [
            'total_errors' => DB::table('user_activities')
                ->where('type', 'error')
                ->where('created_at', '>=', $now->subHours($period))
                ->count(),
            'error_types' => DB::table('user_activity_events')
                ->where('event_type', 'error')
                ->where('created_at', '>=', $now->subHours($period))
                ->select('event_data->message as error', DB::raw('count(*) as count'))
                ->groupBy('error')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    protected function getPerformanceStats()
    {
        if (!$this->trackPerformance) {
            return null;
        }

        $period = config('system-monitor.user_activity.performance_stats_period', 24);
        $now = now();

        return [
            'avg_page_load' => DB::table('user_activities')
                ->where('type', 'page_view')
                ->where('created_at', '>=', $now->subHours($period))
                ->avg('duration'),
            'slow_pages' => DB::table('user_activities')
                ->where('type', 'page_view')
                ->where('created_at', '>=', $now->subHours($period))
                ->where('duration', '>', config('system-monitor.user_activity.slow_page_threshold', 3))
                ->select('url', DB::raw('AVG(duration) as avg_duration'))
                ->groupBy('url')
                ->orderBy('avg_duration', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    protected function getEventStats()
    {
        if (!$this->trackEvents) {
            return null;
        }

        $period = config('system-monitor.user_activity.event_stats_period', 24);
        $now = now();

        return [
            'total_events' => DB::table('user_activity_events')
                ->where('created_at', '>=', $now->subHours($period))
                ->count(),
            'event_types' => DB::table('user_activity_events')
                ->where('created_at', '>=', $now->subHours($period))
                ->select('event_type', DB::raw('count(*) as count'))
                ->groupBy('event_type')
                ->orderBy('count', 'desc')
                ->get()
        ];
    }

    public function clearOldRecords()
    {
        $retention = config('system-monitor.user_activity.retention_days', 30);
        $date = now()->subDays($retention);

        DB::table('user_activity_events')
            ->where('created_at', '<', $date)
            ->delete();

        DB::table('user_activities')
            ->where('created_at', '<', $date)
            ->delete();

        return true;
    }
} 