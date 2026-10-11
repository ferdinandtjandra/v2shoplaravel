@extends('layouts.app')

@section('judul', 'Dashboard')

@section('isi')
<style>
    .kotak-angka .label-angka { font-size: 13px; color: #8592a3; }
    .kotak-angka .angka { font-size: 1.5rem; font-weight: 700; line-height: 1.2; color: #384551; }
    .kotak-angka .ikon { width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
</style>

{{-- ================= PENGINGAT (paling atas, mencolok) ================= --}}
@include('_pengingat')

<div class="row">
    <div class="col-sm-6 col-xl-4 mb-6">
        <div class="card h-100 kotak-angka">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="label-angka">Transaksi saya hari ini</div>
                    <div class="angka">{{ $transaksiSaya->count() }} transaksi</div>
                    <small class="text-muted">Rp {{ number_format($transaksiSaya->sum('total')) }}</small>
                </div>
                <span class="ikon bg-label-primary"><i class="icon-base bx bx-cart"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4 mb-6">
        <div class="card h-100 kotak-angka">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="label-angka">Barang tersedia</div>
                    <div class="angka">{{ number_format($jumlahBarang) }} barang</div>
                    <small class="text-muted"><a href="{{ route('barang.index') }}">Lihat Laporan Stok</a></small>
                </div>
                <span class="ikon bg-label-warning"><i class="icon-base bx bx-box"></i></span>
            </div>
        </div>
    </div>
    <div class="col-xl-4 mb-6">
        <a href="{{ route('penjualan.index') }}" class="btn btn-primary btn-lg w-100 h-100 d-flex align-items-center justify-content-center">
            <i class="icon-base bx bx-plus me-2"></i> Penjualan Baru
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl-7 mb-6">
        <div class="card h-100">
            <h5 class="card-header">Transaksi Saya Hari Ini</h5>
            <div class="table-responsive">
                <table class="table table-hover tabel-padat mb-0">
                    <tbody>
                        @forelse ($transaksiSaya as $penjualan)
                            <tr>
                                <td>
                                    <a href="{{ route('penjualan.struk', $penjualan->id) }}" class="fw-medium">{{ $penjualan->kode_transaksi }}</a>
                                    <small class="text-muted d-block">{{ $penjualan->tanggal->format('H:i') }}</small>
                                </td>
                                <td class="text-end text-nowrap">Rp {{ number_format($penjualan->total) }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-6">Belum ada transaksi hari ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5 mb-6">
        @include('_stok_menipis')
    </div>
</div>
@endsection
