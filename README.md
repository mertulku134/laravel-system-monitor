# Laravel System Monitor

Laravel uygulamalarınız için gelişmiş sistem izleme paketi. Cache, Queue, Redis, sistem kaynakları ve kullanıcı aktivitelerini izlemenizi sağlar.

## Özellikler

- Cache sistemi izleme
- Queue sistemi izleme
- Redis sistemi izleme
- Sistem kaynakları izleme (CPU, RAM, Disk)
- Kullanıcı aktivite izleme
- Özelleştirilebilir arayüz
- Güvenlik kontrolleri
- Detaylı loglama

## Kurulum

```bash
composer require aoux/system-monitor
```

## Yapılandırma

Paketi yükledikten sonra, yapılandırma dosyasını yayınlayın:

```bash
php artisan vendor:publish --provider="Aoux\SystemMonitor\SystemMonitorServiceProvider"
```

## Kullanım

### View Oluşturma

Kendi view dosyanızı oluşturun:

```php
// resources/views/admin/monitor/index.blade.php

@extends('layout.admin.master')

@section('content')
    <div class="system-monitor">
        @if(config('system-monitor.cache.enabled'))
            <div class="cache-status">
                <h3>Cache Durumu</h3>
                <p>Toplam Anahtar: {{ $cacheStatus['total_keys'] }}</p>
                <p>Kullanılan Bellek: {{ $cacheStatus['memory_usage'] }}</p>
            </div>
        @endif

        @if(config('system-monitor.queue.enabled'))
            <div class="queue-status">
                <h3>Queue Durumu</h3>
                <p>Bekleyen İşler: {{ $queueStatus['pending_jobs'] }}</p>
                <p>Başarısız İşler: {{ $queueStatus['failed_jobs'] }}</p>
            </div>
        @endif
    </div>
@endsection
```

### Route Erişimi

Sistem monitörüne erişmek için:

```php
// routes/web.php
Route::get('/admin/monitor', [MonitorController::class, 'index'])
    ->name('admin.monitor.index');
```

### Komut Satırı

Sistem durumunu komut satırından kontrol etmek için:

```bash
php artisan system:monitor
```

## Güvenlik

Güvenlik sorunlarını bildirmek için: mertulkusmulku@gmail.com

## Lisans

MIT 