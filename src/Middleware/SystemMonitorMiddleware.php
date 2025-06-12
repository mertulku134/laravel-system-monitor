<?php

namespace Aoux\SystemMonitor\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemMonitorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // IP kontrolü
        $allowedIps = config('system-monitor.security.allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            abort(403, 'Bu sayfaya erişim izniniz yok.');
        }

        // Kullanıcı kontrolü
        if (!Auth::check()) {
            abort(403, 'Bu sayfaya erişim için giriş yapmalısınız.');
        }

        // Rol kontrolü
        $allowedRoles = config('system-monitor.security.allowed_roles', []);
        if (!empty($allowedRoles)) {
            $hasRole = false;
            $user = Auth::user();
            
            // Rol kontrolü için callback fonksiyonu
            $roleCheckCallback = config('system-monitor.security.role_check_callback');
            if (is_callable($roleCheckCallback)) {
                $hasRole = $roleCheckCallback($user, $allowedRoles);
            } else {
                // Varsayılan rol kontrolü
                if (method_exists($user, 'hasAnyRole')) {
                    $hasRole = $user->hasAnyRole($allowedRoles);
                } elseif (method_exists($user, 'hasRole')) {
                    $hasRole = collect($allowedRoles)->contains(function ($role) use ($user) {
                        return $user->hasRole($role);
                    });
                }
            }

            if (!$hasRole) {
                abort(403, 'Bu sayfaya erişim izniniz yok.');
            }
        }

        // İzin kontrolü
        $allowedPermissions = config('system-monitor.security.allowed_permissions', []);
        if (!empty($allowedPermissions)) {
            $hasPermission = false;
            $user = Auth::user();
            
            // İzin kontrolü için callback fonksiyonu
            $permissionCheckCallback = config('system-monitor.security.permission_check_callback');
            if (is_callable($permissionCheckCallback)) {
                $hasPermission = $permissionCheckCallback($user, $allowedPermissions);
            } else {
                // Varsayılan izin kontrolü
                if (method_exists($user, 'hasAnyPermission')) {
                    $hasPermission = $user->hasAnyPermission($allowedPermissions);
                } elseif (method_exists($user, 'hasPermission')) {
                    $hasPermission = collect($allowedPermissions)->contains(function ($permission) use ($user) {
                        return $user->hasPermission($permission);
                    });
                }
            }

            if (!$hasPermission) {
                abort(403, 'Bu sayfaya erişim izniniz yok.');
            }
        }

        // Deneme sayısı kontrolü
        $maxRetries = config('system-monitor.security.max_retries', 3);
        $lockoutTime = config('system-monitor.security.lockout_time', 15);
        $key = 'system_monitor_attempts_' . $request->ip();

        if (cache()->has($key)) {
            $attempts = cache()->get($key);
            if ($attempts >= $maxRetries) {
                abort(429, 'Çok fazla deneme yaptınız. Lütfen ' . $lockoutTime . ' dakika sonra tekrar deneyin.');
            }
            cache()->increment($key);
        } else {
            cache()->put($key, 1, $lockoutTime * 60);
        }

        return $next($request);
    }
} 