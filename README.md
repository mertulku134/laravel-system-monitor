# Laravel System Monitor

Laravel uygulamanızın cache, queue, redis ve sistem kaynaklarını izlemek için geliştirilmiş bir paket.

## Özellikler

- Cache sistemi izleme
- Queue sistemi izleme
- Redis sistemi izleme
- Sistem kaynakları izleme
- Kullanıcı aktivite izleme
- Modern ve kullanıcı dostu arayüz
- Özelleştirilebilir izinler
- Detaylı istatistikler

## Kurulum

```bash
composer require aoux/system-monitor
```

## Yapılandırma

Config dosyasını yayınlayın:

```bash
php artisan vendor:publish --tag=config
```

Assets dosyalarını yayınlayın:

```bash
php artisan vendor:publish --tag=assets
```

## Kullanım

1. `.env` dosyanızda gerekli ayarları yapın:

```env
SYSTEM_MONITOR_URL=system-monitor
SYSTEM_MONITOR_MIDDLEWARE=web,auth
SYSTEM_MONITOR_LAYOUT=layout.admin.master
SYSTEM_MONITOR_VIEW=system-monitor::monitor
```

2. İzinleri yapılandırın:

```php
// config/admin_permissions.php
[
    'flag' => 'admin.system_monitor',
    'icon' => 'fas fa-tachometer-alt',
    'menuVisible' => true
],
[
    'flag' => 'admin.system_monitor.index',
    'parent_flag' => 'admin.system_monitor',
    'menuVisible' => true
],
[
    'flag' => 'admin.system_monitor.user_activity',
    'parent_flag' => 'admin.system_monitor',
    'menuVisible' => true
]
```

3. Sistemi izlemeye başlayın:

```php
use Aoux\SystemMonitor\Services\CacheMonitor;
use Aoux\SystemMonitor\Services\QueueMonitor;
use Aoux\SystemMonitor\Services\RedisMonitor;
use Aoux\SystemMonitor\Services\SystemResourceMonitor;
use Aoux\SystemMonitor\Services\UserActivityMonitor;

$cacheMonitor = new CacheMonitor();
$queueMonitor = new QueueMonitor();
$redisMonitor = new RedisMonitor();
$systemMonitor = new SystemResourceMonitor();
$userActivityMonitor = new UserActivityMonitor();

$cacheStatus = $cacheMonitor->getStatus();
$queueStatus = $queueMonitor->getStatus();
$redisStatus = $redisMonitor->getStatus();
$systemStatus = $systemMonitor->getStatus();
$userActivityStatus = $userActivityMonitor->getStatus();
```

## Güvenlik

Eğer güvenlik açıkları bulursanız, lütfen security@aoux.com adresine e-posta gönderin.

## Lisans

MIT lisansı altında lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına bakın. 