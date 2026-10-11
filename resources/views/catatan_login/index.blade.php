@extends('layouts.app')

@section('judul', 'Catatan Login')

@section('isi')
<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Siapa login, kapan, dan dari perangkat apa</h5>
        <small class="text-muted">1000 catatan terakhir</small>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover tabel-padat datatable">
            <thead>
                <tr><th>Waktu</th><th>Kejadian</th><th>Pengguna</th><th>Perangkat</th><th>Alamat IP</th></tr>
            </thead>
            <tbody>
                @foreach ($daftar as $c)
                    <tr>
                        <td class="text-nowrap" data-order="{{ $c->created_at->format('Y-m-d H:i:s') }}">
                            {{ $c->created_at->translatedFormat('j M Y') }} <small class="text-muted">{{ $c->created_at->format('H:i:s') }}</small>
                        </td>
                        <td class="text-nowrap">
                            @if ($c->kejadian == 'login')
                                <span class="badge bg-label-success">Login</span>
                            @elseif ($c->kejadian == 'logout')
                                <span class="badge bg-label-secondary">Logout</span>
                            @elseif ($c->kejadian == 'gagal')
                                <span class="badge bg-label-warning">Login gagal</span>
                            @else
                                <span class="badge bg-label-danger">{{ \App\Models\CatatanLogin::DAFTAR_KEJADIAN[$c->kejadian] ?? $c->kejadian }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($c->user)
                                <div class="fw-medium">{{ $c->user->name }}</div>
                                <small class="text-muted">{{ '@' . $c->user->username }}</small>
                            @else
                                {{-- Username yang diketik tidak terdaftar --}}
                                <div class="text-muted">tidak dikenal</div>
                                <small class="text-muted">diketik: {{ \Illuminate\Support\Str::limit($c->username, 40) }}</small>
                            @endif
                        </td>
                        <td title="{{ $c->perangkat }}">{{ $c->namaPerangkat() }}</td>
                        <td>{{ $c->ip }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
