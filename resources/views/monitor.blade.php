@extends('layout.admin.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sistem Monitörü</h3>
        </div>
        <div class="card-body">
            <!-- Queue Durumu -->
            @if($queue)
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4>Queue Durumu</h4>
                            <p>Driver: {{ $queue['driver'] }}</p>
                            <p>Connection: {{ $queue['connection'] }}</p>
                            <p>Bekleyen İşler: {{ $queue['pending_jobs'] }}</p>
                            <p>Başarısız İşler: {{ $queue['failed_jobs'] }}</p>
                            <p>İşlenen İşler: {{ $queue['processed_jobs'] }}</p>
                            <p>Yeniden Deneme: {{ $queue['retry_after'] }} saniye</p>
                            <p>Timeout: {{ $queue['timeout'] }} saniye</p>
                            <p>Maksimum Deneme: {{ $queue['max_tries'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Cache Durumu -->
            @if($cache)
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4>Cache Durumu</h4>
                            <p>Driver: {{ $cache['driver'] }}</p>
                            <p>Connection: {{ $cache['connection'] }}</p>
                            <p>Prefix: {{ $cache['prefix'] }}</p>
                            <p>Default TTL: {{ $cache['default_ttl'] }} saniye</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Redis Durumu -->
            @if($redis)
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4>Redis Durumu</h4>
                            <p>Version: {{ $redis['version'] }}</p>
                            <p>Connected Clients: {{ $redis['connected_clients'] }}</p>
                            <p>Used Memory: {{ $redis['used_memory'] }}</p>
                            <p>Used Memory Peak: {{ $redis['used_memory_peak'] }}</p>
                            <p>Total Connections: {{ $redis['total_connections_received'] }}</p>
                            <p>Total Commands: {{ $redis['total_commands_processed'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Sistem Kaynakları -->
            @if($system)
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4>Sistem Kaynakları</h4>
                            @if($system['cpu'])
                            <h5>CPU</h5>
                            <p>Yük (1dk): {{ $system['cpu']['load']['1min'] }}</p>
                            <p>Yük (5dk): {{ $system['cpu']['load']['5min'] }}</p>
                            <p>Yük (15dk): {{ $system['cpu']['load']['15min'] }}</p>
                            <p>Çekirdek: {{ $system['cpu']['cores'] }}</p>
                            <p>Kullanım: %{{ $system['cpu']['usage'] }}</p>
                            @endif

                            @if($system['memory'])
                            <h5>Bellek</h5>
                            <p>Toplam: {{ $system['memory']['total'] }}</p>
                            <p>Kullanılan: {{ $system['memory']['used'] }}</p>
                            <p>Boş: {{ $system['memory']['free'] }}</p>
                            <p>Kullanım: %{{ $system['memory']['percent'] }}</p>
                            @endif

                            @if($system['disk'])
                            <h5>Disk</h5>
                            <p>Toplam: {{ $system['disk']['total'] }}</p>
                            <p>Kullanılan: {{ $system['disk']['used'] }}</p>
                            <p>Boş: {{ $system['disk']['free'] }}</p>
                            <p>Kullanım: %{{ $system['disk']['percent'] }}</p>
                            @endif

                            @if($system['php'])
                            <h5>PHP</h5>
                            <p>Version: {{ $system['php']['version'] }}</p>
                            <p>Memory Limit: {{ $system['php']['memory_limit'] }}</p>
                            <p>Max Execution Time: {{ $system['php']['max_execution_time'] }}</p>
                            <p>Upload Max Filesize: {{ $system['php']['upload_max_filesize'] }}</p>
                            <p>Post Max Size: {{ $system['php']['post_max_size'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection