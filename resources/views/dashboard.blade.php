@extends('layouts.app')

@section('judul', 'Dashboard')

@php
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $bulanIni = $namaBulan[(int) date('n')] . ' ' . date('Y');
@endphp

@section('isi')
<style>
    /* Kotak angka ringkasan */
    .kotak-angka .label-angka { font-size: 13px; color: #8592a3; }
    .kotak-angka .angka { font-size: 1.5rem; font-weight: 700; line-height: 1.2; color: #384551; }
    .kotak-angka .ikon { width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
</style>

{{-- ================= PENGINGAT (paling atas, mencolok) ================= --}}
@include('_pengingat')

{{-- ================= 1. ANGKA RINGKASAN ================= --}}
<div class="row">
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100 kotak-angka">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="label-angka">Penjualan hari ini</div>
                    <div class="angka">Rp {{ number_format($penjualanHariIni->sum('total')) }}</div>
                    <small class="text-muted">{{ $penjualanHariIni->count() }} transaksi</small>
                </div>
                <span class="ikon bg-label-primary"><i class="icon-base bx bx-cart"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100 kotak-angka">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="label-angka">Penjualan {{ $bulanIni }}</div>
                    <div class="angka">Rp {{ number_format($penjualanBulanIni->sum('total')) }}</div>
                    <small class="text-muted">{{ $penjualanBulanIni->count() }} transaksi &middot; bulan lalu Rp {{ number_format($totalBulanLalu) }}</small>
                </div>
                <span class="ikon bg-label-primary"><i class="icon-base bx bx-calendar"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100 kotak-angka">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="label-angka">Keuntungan {{ $bulanIni }}</div>
                    <div class="angka">Rp {{ number_format($penjualanBulanIni->sum('keuntungan')) }}</div>
                    <small class="text-muted">hari ini Rp {{ number_format($penjualanHariIni->sum('keuntungan')) }}</small>
                </div>
                <span class="ikon bg-label-success"><i class="icon-base bx bx-line-chart"></i></span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-6">
        <div class="card h-100 kotak-angka">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="label-angka">Stok menipis (sisa 1-2)</div>
                    <div class="angka">{{ number_format($jumlahStokMenipis) }} barang</div>
                    <small class="text-muted">dari {{ number_format($jumlahBarang) }} barang yang tersedia</small>
                </div>
                <span class="ikon bg-label-warning"><i class="icon-base bx bx-box"></i></span>
            </div>
        </div>
    </div>
</div>

{{-- ================= 2. GRAFIK HARIAN + BARANG TERLARIS ================= --}}
<div class="row">
    <div class="col-xl-8 mb-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Penjualan Harian &middot; {{ $bulanIni }}</h5>
                <small class="text-muted">Arahkan kursor ke batang untuk melihat angkanya</small>
            </div>
            <div class="card-body">
                @if ($tertinggi == 0)
                    <p class="text-center text-muted py-6 mb-0">Belum ada penjualan bulan ini.</p>
                @else
                    <small class="text-muted">Tertinggi: Rp {{ number_format($tertinggi) }}</small>

                    {{-- Tiap tanggal 1 batang. Tinggi batang = persen dari penjualan tertinggi. --}}
                    <div class="grafik mt-2">
                        @foreach ($perHari as $tanggal => $data)
                            <div class="kolom" data-bs-toggle="tooltip"
                                title="{{ $tanggal }} {{ $namaBulan[(int) date('n')] }}: Rp {{ number_format($data['total']) }} ({{ $data['jumlah'] }} transaksi)">
                                @if ($data['total'] > 0)
                                    <div class="batang" style="height: {{ max(2, round($data['total'] / $tertinggi * 100)) }}%"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Angka tanggal di bawah grafik: tanggal 1, kelipatan 5, dan hari ini --}}
                    <div class="grafik-tanggal">
                        @foreach ($perHari as $tanggal => $data)
                            <span class="{{ $tanggal == date('j') ? 'hari-ini' : '' }}">{{ $tanggal == 1 || $tanggal % 5 == 0 || $tanggal == date('j') ? $tanggal : '' }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4 mb-6">
        <div class="card h-100">
            <h5 class="card-header">Barang Terlaris &middot; {{ $bulanIni }}</h5>
            <div class="table-responsive">
                <table class="table tabel-padat mb-0">
                    <tbody>
                        @forelse ($terlaris as $namaBarang => $qty)
                            <tr>
                                <td class="text-muted" style="width: 1%;">{{ $loop->iteration }}</td>
                                <td class="fw-medium">{{ $namaBarang }}</td>
                                <td class="text-end text-nowrap">{{ $qty }} pcs</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-6">Belum ada penjualan bulan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ================= 3. TRANSAKSI TERAKHIR + NOTA + STOK ================= --}}
<div class="row">
    <div class="col-xl-4 mb-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transaksi Terakhir</h5>
                <a href="{{ route('laporan.penjualan', ['bulan' => date('n'), 'tahun' => date('Y')]) }}" class="small">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover tabel-padat mb-0">
                    <tbody>
                        @forelse ($transaksiTerakhir as $penjualan)
                            <tr>
                                <td>
                                    <a href="{{ route('penjualan.struk', $penjualan->id) }}" class="fw-medium">{{ $penjualan->kode_transaksi }}</a>
                                    <small class="text-muted d-block">{{ $penjualan->tanggal->format('j M Y, H:i') }} &middot; {{ $penjualan->sales->name ?? '-' }}</small>
                                </td>
                                <td class="text-end text-nowrap">Rp {{ number_format($penjualan->total) }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-6">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4 mb-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Nota Jatuh Tempo</h5>
                <a href="{{ route('nota.index') }}" class="small">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover tabel-padat mb-0">
                    <tbody>
                        @forelse ($notaJatuhTempo as $nota)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $nota->nomor_nota }}</div>
                                    <small class="text-muted">
                                        {{ $nota->tanggal_jatuh_tempo->format('j M Y') }}
                                        @if ($nota->tanggal_jatuh_tempo->format('Y-m-d') < date('Y-m-d'))
                                            <span class="badge bg-label-danger">Lewat</span>
                                        @else
                                            <span class="badge bg-label-warning">Segera</span>
                                        @endif
                                    </small>
                                </td>
                                <td class="text-end text-nowrap">Rp {{ number_format($nota->nominal) }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-6">Tidak ada nota yang lewat atau jatuh tempo dalam 7 hari.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4 mb-6">
        @include('_stok_menipis')
    </div>
</div>
@endsection

@section('script')
<script>
    // Mengaktifkan kotak keterangan (tooltip) yang muncul saat kursor di atas batang grafik
    $('[data-bs-toggle="tooltip"]').each(function () {
        bootstrap.Tooltip.getOrCreateInstance(this);
    });
</script>
@endsection
