# Laravel System Monitor

Advanced system monitoring package for Laravel applications. Monitors Cache, Queue, Redis, system resources, and user activities.

## Features

- Cache system monitoring
- Queue system monitoring
- Redis system monitoring
- System resources monitoring (CPU, RAM, Disk)
- User activity monitoring
- Customizable interface
- Security checks
- Detailed logging

## Installation

```bash
composer require aoux/system-monitor
```

## Configuration

After installing the package, publish the configuration file:

```bash
php artisan vendor:publish --provider="Aoux\SystemMonitor\SystemMonitorServiceProvider"
```

You can also publish specific files using the following commands:

```bash
# Publish only configuration file
php artisan vendor:publish --tag=config

# Publish only view files
php artisan vendor:publish --tag=views

# Publish only migration files
php artisan vendor:publish --tag=migrations

# Publish all files
php artisan vendor:publish --tag=all
```

## Usage

### Creating Views

Create your own view file:

```php
// resources/views/admin/monitor/index.blade.php

@extends('layout.admin.master')

@section('content')
    <div class="system-monitor">
        @if(config('system-monitor.cache.enabled'))
            <div class="cache-status">
                <h3>Cache Status</h3>
                <p>Total Keys: {{ $cacheStatus['total_keys'] }}</p>
                <p>Memory Usage: {{ $cacheStatus['memory_usage'] }}</p>
            </div>
        @endif

        @if(config('system-monitor.queue.enabled'))
            <div class="queue-status">
                <h3>Queue Status</h3>
                <p>Pending Jobs: {{ $queueStatus['pending_jobs'] }}</p>
                <p>Failed Jobs: {{ $queueStatus['failed_jobs'] }}</p>
            </div>
        @endif
    </div>
@endsection
```

### Route Access

To access the system monitor:

```php
// routes/web.php
Route::get('/admin/monitor', [MonitorController::class, 'index'])
    ->name('admin.monitor.index');
```

### Command Line

To check system status from command line:

```bash
php artisan system:monitor
```

## Security

To report security issues: mertsmulku@gmail.com

## License

MIT 