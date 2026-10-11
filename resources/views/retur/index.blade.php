@extends('layouts.app')

@section('judul', 'Laporan Retur')

@section('isi')
<div class="card mb-6">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Daftar Retur</h5>
        <div class="d-flex gap-2">
            <span class="badge bg-label-primary">{{ $daftar->count() }} retur</span>
            <span class="badge bg-label-warning">{{ $daftar->where('sudah_kembali', false)->count() }} belum kembali</span>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover tabel-padat datatable">
            <thead>
                <tr>
                    <th>Kode</th><th>Barang</th><th>Supplier</th><th class="text-end">Qty</th>
                    <th>Tgl Beli</th><th>Tgl Retur</th><th>Keterangan</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daftar as $retur)
                    <tr>
                        <td class="text-nowrap">{{ $retur->kode }}</td>
                        <td>
                            <div class="fw-medium">{{ $retur->barang->nama }}</div>
                            <small class="text-muted">{{ $retur->barang->kode }}</small>
                        </td>
                        <td>{{ $retur->barang->supplier->perusahaan ?? '-' }}</td>
                        <td class="text-end">{{ $retur->qty }}</td>
                        <td class="text-nowrap" data-order="{{ $retur->barang->tanggal_masuk->format('Y-m-d') }}">{{ $retur->barang->tanggal_masuk->format('j M Y') }}</td>
                        <td class="text-nowrap" data-order="{{ $retur->tanggal_retur->format('Y-m-d H:i') }}">{{ $retur->tanggal_retur->format('j M Y') }}</td>
                        <td>{{ $retur->keterangan }}</td>
                        <td class="text-nowrap">
                            @if ($retur->sudah_kembali)
                                <span class="badge bg-label-success">Sudah kembali</span>
                                <small class="text-muted d-block">{{ $retur->tanggal_kembali->format('j M Y') }}</small>
                            @else
                                <form method="POST" action="{{ route('retur.kembali', $retur->id) }}" class="konfirmasi" data-pesan="Barang sudah kembali dari supplier? Stok akan ditambah {{ $retur->qty }}.">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Barang Kembali</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
