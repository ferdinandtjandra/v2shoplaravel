@extends('layouts.app')

@section('judul', 'Input Retur')

@section('isi')
<div class="row">

    {{-- KIRI: barang yang diretur --}}
    <div class="col-lg-5">
        <div class="card mb-6">
            <h5 class="card-header">Barang yang Diretur</h5>
            <div class="card-body">
                <span class="badge bg-label-primary mb-2">{{ $barang->kode }}</span>
                <h5 class="mb-4">{{ $barang->nama }}</h5>
                <div class="d-flex justify-content-between border-top py-3">
                    <span class="text-muted">Supplier</span>
                    <span>{{ $barang->supplier->perusahaan ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-3">
                    <span class="text-muted">Tanggal masuk</span>
                    <span>{{ $barang->tanggal_masuk->format('j M Y') }}</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-3">
                    <span class="text-muted">Stok saat ini</span>
                    <span class="fw-bold">{{ $barang->qty }} pcs</span>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN: form retur --}}
    <div class="col-lg-7">
        <div class="card mb-6">
            <h5 class="card-header">Data Retur</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('retur.store', $barang->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Quantity Retur <span class="text-danger">*</span></label>
                        <div class="input-group" style="max-width: 220px;">
                            <input type="number" name="qty" class="form-control" min="1" max="{{ $barang->qty }}" value="{{ old('qty', 1) }}" required>
                            <span class="input-group-text">dari {{ $barang->qty }} pcs</span>
                        </div>
                        <div class="form-text">Stok akan berkurang sebanyak jumlah ini, dan kembali lagi saat barang ditandai "Barang Kembali".</div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Keterangan Retur <span class="text-danger">*</span></label>
                        <textarea name="keterangan" class="form-control" rows="4" placeholder="Contoh: layar bergaris, tidak bisa disentuh" required>{{ old('keterangan') }}</textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('retur.pilih') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Retur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ================= RIWAYAT RETUR BARANG INI ================= --}}
<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Riwayat Retur Barang Ini</h5>
        <span class="badge bg-label-primary">{{ count($riwayat) }} retur</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover tabel-padat mb-0">
            <thead>
                <tr><th>Waktu</th><th>Kode</th><th class="text-end">Qty</th><th>Keterangan</th><th>Diinput oleh</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                    <tr>
                        <td class="text-nowrap">{{ $r->tanggal_retur->format('j M Y') }} <small class="text-muted">{{ $r->tanggal_retur->format('H:i') }}</small></td>
                        <td class="text-nowrap">{{ $r->kode }}</td>
                        <td class="text-end">{{ $r->qty }}</td>
                        <td>{{ $r->keterangan }}</td>
                        <td>{{ $r->user->name ?? '-' }}</td>
                        <td class="text-nowrap">
                            @if ($r->sudah_kembali)
                                <span class="badge bg-label-success">Sudah kembali</span>
                                <small class="text-muted">{{ $r->tanggal_kembali->format('j M Y') }}</small>
                            @else
                                <span class="badge bg-label-warning">Belum kembali</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-6">Barang ini belum pernah diretur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
