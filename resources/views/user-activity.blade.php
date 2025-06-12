@extends('layout.admin.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kullanıcı Aktiviteleri</h3>
        </div>
        <div class="card-body">
            <!-- Aktif Kullanıcılar -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4>Aktif Kullanıcılar</h4>
                            <p>Toplam: {{ $status['active_users']['total'] }}</p>
                            <p>Son 24 Saat: {{ $status['active_users']['last_24h'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Son Aktiviteler -->
            <div class="row mt-4">
                <div class="col-12">
                    <h4>Son Aktiviteler</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kullanıcı</th>
                                <th>Tip</th>
                                <th>URL</th>
                                <th>IP</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($status['recent_activities'] as $activity)
                                <tr>
                                    <td>{{ $activity->user->name ?? 'Misafir' }}</td>
                                    <td>{{ $activity->type }}</td>
                                    <td>{{ $activity->url }}</td>
                                    <td>{{ $activity->ip }}</td>
                                    <td>{{ $activity->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Hata İstatistikleri -->
            <div class="row mt-4">
                <div class="col-12">
                    <h4>Hata İstatistikleri</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hata Tipi</th>
                                <th>Sayı</th>
                                <th>Son Hata</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($status['error_stats'] as $type => $stats)
                                <tr>
                                    <td>{{ $type }}</td>
                                    <td>{{ $stats['count'] }}</td>
                                    <td>{{ $stats['last_error'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Performans İstatistikleri -->
            <div class="row mt-4">
                <div class="col-12">
                    <h4>Performans İstatistikleri</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Metrik</th>
                                <th>Ortalama</th>
                                <th>En Yüksek</th>
                                <th>En Düşük</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($status['performance_stats'] as $metric => $stats)
                                <tr>
                                    <td>{{ $metric }}</td>
                                    <td>{{ $stats['average'] }}</td>
                                    <td>{{ $stats['max'] }}</td>
                                    <td>{{ $stats['min'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Olay İstatistikleri -->
            <div class="row mt-4">
                <div class="col-12">
                    <h4>Olay İstatistikleri</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Olay Tipi</th>
                                <th>Sayı</th>
                                <th>Son Olay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($status['event_stats'] as $type => $stats)
                                <tr>
                                    <td>{{ $type }}</td>
                                    <td>{{ $stats['count'] }}</td>
                                    <td>{{ $stats['last_event'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection 