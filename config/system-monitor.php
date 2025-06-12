<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Monitor Route Configuration
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, System Monitor'un route yapılandırmasını kontrol eder.
    |
    */
    'route' => [
        'prefix' => 'system-monitor',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Monitor URL
    |--------------------------------------------------------------------------
    |
    | Bu ayar, sistem monitörüne erişmek için kullanılacak URL'i belirler.
    |
    */
    'url' => env('SYSTEM_MONITOR_URL', 'system-monitor'),

    /*
    |--------------------------------------------------------------------------
    | View Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, sistem monitörü görünümlerinin nasıl yapılandırılacağını belirler.
    |
    */
    'view' => [
        'layout' => env('SYSTEM_MONITOR_LAYOUT', 'layouts.app'),
        'index' => env('SYSTEM_MONITOR_VIEW', 'system-monitor.index'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, sistem monitörüne erişimi kontrol eden middleware'leri belirler.
    |
    */
    'middleware' => explode(',', env('SYSTEM_MONITOR_MIDDLEWARE', 'web')),

    /*
    |--------------------------------------------------------------------------
    | Cache Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, cache izleme özelliklerini yapılandırır.
    |
    */
    'cache' => [
        'enabled' => env('SYSTEM_MONITOR_CACHE_ENABLED', true),
        'refresh_interval' => env('SYSTEM_MONITOR_CACHE_REFRESH_INTERVAL', 60),
        'max_keys' => env('SYSTEM_MONITOR_CACHE_MAX_KEYS', 100),
        'show_values' => env('SYSTEM_MONITOR_CACHE_SHOW_VALUES', false),
        'excluded_keys' => explode(',', env('SYSTEM_MONITOR_CACHE_EXCLUDED_KEYS', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, queue izleme özelliklerini yapılandırır.
    |
    */
    'queue' => [
        'enabled' => env('SYSTEM_MONITOR_QUEUE_ENABLED', true),
        'refresh_interval' => env('SYSTEM_MONITOR_QUEUE_REFRESH_INTERVAL', 60),
        'max_jobs' => env('SYSTEM_MONITOR_QUEUE_MAX_JOBS', 100),
        'show_payload' => env('SYSTEM_MONITOR_QUEUE_SHOW_PAYLOAD', false),
        'excluded_queues' => explode(',', env('SYSTEM_MONITOR_QUEUE_EXCLUDED_QUEUES', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, Redis izleme özelliklerini yapılandırır.
    |
    */
    'redis' => [
        'enabled' => env('SYSTEM_MONITOR_REDIS_ENABLED', true),
        'refresh_interval' => env('SYSTEM_MONITOR_REDIS_REFRESH_INTERVAL', 60),
        'show_clients' => env('SYSTEM_MONITOR_REDIS_SHOW_CLIENTS', true),
        'show_memory' => env('SYSTEM_MONITOR_REDIS_SHOW_MEMORY', true),
        'show_uptime' => env('SYSTEM_MONITOR_REDIS_SHOW_UPTIME', true),
        'show_hit_rate' => env('SYSTEM_MONITOR_REDIS_SHOW_HIT_RATE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, kullanıcı arayüzünün görünümünü yapılandırır.
    |
    */
    'ui' => [
        'theme' => env('SYSTEM_MONITOR_UI_THEME', 'light'),
        'refresh_interval' => env('SYSTEM_MONITOR_UI_REFRESH_INTERVAL', 30),
        'show_timestamps' => env('SYSTEM_MONITOR_UI_SHOW_TIMESTAMPS', true),
        'date_format' => env('SYSTEM_MONITOR_UI_DATE_FORMAT', 'Y-m-d H:i:s'),
        'timezone' => env('SYSTEM_MONITOR_UI_TIMEZONE', config('app.timezone')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Güvenlik Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, güvenlik özelliklerini yapılandırır.
    |
    */
    'security' => [
        'allowed_ips' => explode(',', env('SYSTEM_MONITOR_ALLOWED_IPS', '')),
        'allowed_roles' => explode(',', env('SYSTEM_MONITOR_ALLOWED_ROLES', '')),
        'allowed_permissions' => explode(',', env('SYSTEM_MONITOR_ALLOWED_PERMISSIONS', '')),
        'max_retries' => env('SYSTEM_MONITOR_MAX_RETRIES', 3),
        'lockout_time' => env('SYSTEM_MONITOR_LOCKOUT_TIME', 15),
        
        // Özel rol kontrolü için callback fonksiyonu
        'role_check_callback' => env('SYSTEM_MONITOR_ROLE_CHECK_CALLBACK', null),
        
        // Özel izin kontrolü için callback fonksiyonu
        'permission_check_callback' => env('SYSTEM_MONITOR_PERMISSION_CHECK_CALLBACK', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Loglama Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu ayarlar, loglama özelliklerini yapılandırır.
    |
    */
    'logging' => [
        'enabled' => env('SYSTEM_MONITOR_LOGGING_ENABLED', true),
        'channel' => env('SYSTEM_MONITOR_LOGGING_CHANNEL', 'stack'),
        'level' => env('SYSTEM_MONITOR_LOGGING_LEVEL', 'info'),
        'max_files' => env('SYSTEM_MONITOR_LOGGING_MAX_FILES', 7),
    ],

    'system' => [
        'enabled' => env('SYSTEM_MONITOR_SYSTEM_ENABLED', true),
        'refresh_interval' => env('SYSTEM_MONITOR_SYSTEM_REFRESH_INTERVAL', 60),
        'show_cpu' => env('SYSTEM_MONITOR_SYSTEM_SHOW_CPU', true),
        'show_memory' => env('SYSTEM_MONITOR_SYSTEM_SHOW_MEMORY', true),
        'show_disk' => env('SYSTEM_MONITOR_SYSTEM_SHOW_DISK', true),
        'show_load' => env('SYSTEM_MONITOR_SYSTEM_SHOW_LOAD', true),
        'show_uptime' => env('SYSTEM_MONITOR_SYSTEM_SHOW_UPTIME', true),
        'show_php' => env('SYSTEM_MONITOR_SYSTEM_SHOW_PHP', true),
    ],

    'user_activity' => [
        'enabled' => env('SYSTEM_MONITOR_USER_ACTIVITY_ENABLED', true),
        'max_records' => env('SYSTEM_MONITOR_USER_ACTIVITY_MAX_RECORDS', 1000),
        'show_details' => env('SYSTEM_MONITOR_USER_ACTIVITY_SHOW_DETAILS', true),
        'track_events' => env('SYSTEM_MONITOR_USER_ACTIVITY_TRACK_EVENTS', true),
        'track_errors' => env('SYSTEM_MONITOR_USER_ACTIVITY_TRACK_ERRORS', true),
        'track_performance' => env('SYSTEM_MONITOR_USER_ACTIVITY_TRACK_PERFORMANCE', true),
        'session_timeout' => env('SYSTEM_MONITOR_USER_ACTIVITY_SESSION_TIMEOUT', 30),
        'error_stats_period' => env('SYSTEM_MONITOR_USER_ACTIVITY_ERROR_STATS_PERIOD', 24),
        'performance_stats_period' => env('SYSTEM_MONITOR_USER_ACTIVITY_PERFORMANCE_STATS_PERIOD', 24),
        'event_stats_period' => env('SYSTEM_MONITOR_USER_ACTIVITY_EVENT_STATS_PERIOD', 24),
        'slow_page_threshold' => env('SYSTEM_MONITOR_USER_ACTIVITY_SLOW_PAGE_THRESHOLD', 3),
        'retention_days' => env('SYSTEM_MONITOR_USER_ACTIVITY_RETENTION_DAYS', 30),
        'excluded_paths' => explode(',', env('SYSTEM_MONITOR_USER_ACTIVITY_EXCLUDED_PATHS', 'system-monitor,api/user-activity,_debugbar,horizon,telescope')),
    ],
]; 