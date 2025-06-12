<?php

namespace Aoux\SystemMonitor\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserActivityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$this->shouldTrack($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;

        $this->logActivity($request, $response, $duration);
        
        return $response;
    }

    protected function shouldTrack(Request $request)
    {
        // Sadece web isteklerini izle
        if (!$request->isMethod('GET') && !$request->isMethod('POST')) {
            return false;
        }

        // Belirli URL'leri hariç tut
        $excludedPaths = config('system-monitor.user_activity.excluded_paths', [
            'system-monitor',
            'api/user-activity',
            '_debugbar',
            'horizon',
            'telescope'
        ]);

        foreach ($excludedPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return false;
            }
        }

        return true;
    }

    protected function logActivity(Request $request, $response, $duration)
    {
        try {
            $activityId = DB::table('user_activities')->insertGetId([
                'user_id' => Auth::id(),
                'type' => $this->getActivityType($request),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'duration' => $duration,
                'status' => $response->status(),
                'created_at' => now()
            ]);

            // JavaScript olaylarını kaydet
            if ($request->has('events')) {
                $events = $request->input('events');
                foreach ($events as $event) {
                    DB::table('user_activity_events')->insert([
                        'user_activity_id' => $activityId,
                        'event_type' => $event['type'],
                        'event_data' => json_encode($event),
                        'created_at' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Hata durumunda sessizce devam et
            return;
        }
    }

    protected function getActivityType(Request $request)
    {
        if ($request->isMethod('GET')) {
            return 'page_view';
        }

        if ($request->isMethod('POST')) {
            if ($request->is('api/*')) {
                return 'api_call';
            }
            return 'form_submit';
        }

        return 'other';
    }
} 