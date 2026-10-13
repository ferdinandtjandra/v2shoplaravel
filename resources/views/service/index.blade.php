@extends('layouts.app')

@section('judul', 'Service')

@section('isi')
<div class="card mb-6">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        {{-- 4 kelompok. Tombol yang sedang aktif berwarna biru. --}}
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('service.index') }}" class="btn btn-sm {{ $kelompok == 'aktif' ? 'btn-primary' : 'btn-outline-secondary' }}">Sedang dikerjakan ({{ $jumlahAktif }})</a>
            <a href="{{ route('service.index', ['status' => 'selesai']) }}" class="btn btn-sm {{ $kelompok == 'selesai' ? 'btn-primary' : 'btn-outline-secondary' }}">Selesai, belum diambil ({{ $jumlahSelesai }})</a>
            <a href="{{ route('service.index', ['status' => 'diambil']) }}" class="btn btn-sm {{ $kelompok == 'diambil' ? 'btn-primary' : 'btn-outline-secondary' }}">Sudah diambil</a>
            <a href="{{ route('service.index', ['status' => 'batal']) }}" class="btn btn-sm {{ $kelompok == 'batal' ? 'btn-primary' : 'btn-outline-secondary' }}">Batal</a>
        </div>
        <a href="{{ route('service.create') }}" class="btn btn-success"><i class="icon-base bx bx-plus me-1"></i> Terima Service Baru</a>
    </div>

    <div class="card-body">
        {{-- Pencarian: nomor service, nama pelanggan, atau nomor HP pelanggan --}}
        <form method="GET" action="{{ route('service.index') }}" class="row g-2 mb-4">
            <input type="hidden" name="status" value="{{ $kelompok }}">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control" value="{{ $cari }}" placeholder="Cari nomor service, nama, atau no. HP pelanggan">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="icon-base bx bx-search me-1"></i> Cari</button>
                <a href="{{ route('service.index', ['status' => $kelompok]) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover tabel-padat">
                <thead>
                    <tr><th>No. Service</th><th>Pelanggan</th><th>HP yang diservice</th><th>Teknisi</th><th>Status</th><th class="text-end">Total</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $service)
                        <tr>
                            <td class="text-nowrap">
                                <a href="{{ route('service.show', $service->id) }}" class="fw-medium">{{ $service->kode }}</a>
                                <small class="text-muted d-block">{{ $service->tanggal_terima->translatedFormat('j M Y, H:i') }}</small>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $service->nama_pelanggan }}</div>
                                <small class="text-muted">{{ $service->hp_pelanggan }}</small>
                            </td>
                            <td>
                                <div>{{ $service->merk_tipe }}</div>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($service->kerusakan ?: $service->keluhan, 60) }}</small>
                            </td>
                            <td>{{ $service->nama_teknisi ?: '-' }}</td>
                            <td class="text-nowrap">
                                @include('service._status', ['service' => $service])
                                {{-- HP yang sudah selesai tapi belum diambil: tampilkan sudah berapa hari menunggu --}}
                                @if ($service->status == 'selesai')
                                    <small class="text-muted d-block">menunggu {{ (int) $service->tanggal_selesai->diffInDays(now()) }} hari</small>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">{{ number_format($service->total) }}</td>
                            <td class="text-end"><a href="{{ route('service.show', $service->id) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-6">Tidak ada service di kelompok ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $daftar->links() }}</div>
    </div>
</div>
@endsection
