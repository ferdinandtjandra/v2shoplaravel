@extends('layouts.app')

@section('judul', 'Laporan Penjualan')

@php
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp

@section('isi')
<div class="card mb-6">
    <h5 class="card-header">Pilih Periode</h5>
    <div class="card-body">
        {{-- method GET: pilihan bulan/tahun/sales ikut di alamat URL, jadi halaman bisa di-refresh atau dibookmark --}}
        <form method="GET" action="{{ route('laporan.penjualan') }}">
            {{-- Halaman yang sedang dibuka (biasa / batal) ikut dibawa saat periode diganti --}}
            <input type="hidden" name="tampil" value="{{ request('tampil') }}">
            <div class="row align-items-end">
                <div class="col-md-3 mb-4 mb-md-0">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @foreach ($namaBulan as $nomor => $nama)
                            <option value="{{ $nomor }}" {{ request('bulan', date('n')) == $nomor ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for ($tahun = date('Y'); $tahun >= 2022; $tahun--)
                            <option value="{{ $tahun }}" {{ request('tahun', date('Y')) == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <label class="form-label">Sales</label>
                    <select name="sales_id" class="form-select">
                        <option value="">Semua Sales</option>
                        @foreach ($daftarSales as $s)
                            <option value="{{ $s->id }}" {{ request('sales_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}{{ $s->aktif ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-search me-1"></i> Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($daftar !== null)

@php
    // Pilihan periode + halaman, dibawa ke semua link di bawah supaya selalu kembali ke daftar yang sama
    $periode = request()->only('bulan', 'tahun', 'sales_id');
@endphp

{{-- 2 halaman: penjualan biasa dan yang dibatalkan. Tombol yang sedang aktif berwarna biru. --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('laporan.penjualan', $periode) }}" class="btn btn-sm {{ $tampilBatal ? 'btn-outline-secondary' : 'btn-primary' }}">Penjualan</a>
    <a href="{{ route('laporan.penjualan', $periode + ['tampil' => 'batal']) }}" class="btn btn-sm {{ $tampilBatal ? 'btn-danger' : 'btn-outline-secondary' }}">Dibatalkan ({{ $jumlahBatal }})</a>
</div>

{{-- Ringkasan periode yang dipilih --}}
<div class="row">
    <div class="col-md-4 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">{{ $tampilBatal ? 'Jumlah Transaksi Dibatalkan' : 'Jumlah Transaksi' }}</small>
                <span class="fs-4 fw-bold">{{ number_format($daftar->count()) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">{{ $tampilBatal ? 'Nilai yang Dibatalkan' : 'Total Penjualan' }}</small>
                <span class="fs-4 fw-bold {{ $tampilBatal ? 'text-danger' : 'text-primary' }}">Rp {{ number_format($daftar->sum('total')) }}</span>
            </div>
        </div>
    </div>
    @if (! $tampilBatal)
        <div class="col-md-4 mb-6">
            <div class="card h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Total Keuntungan</small>
                    <span class="fs-4 fw-bold text-success">Rp {{ number_format($daftar->sum('keuntungan')) }}</span>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $tampilBatal ? 'Penjualan Dibatalkan' : 'Penjualan' }} {{ $namaBulan[(int) request('bulan')] ?? '' }} {{ request('tahun') }}</h5>
        {{-- Export mengikuti periode dan halaman (biasa / batal) yang sedang dibuka --}}
        <a href="{{ route('laporan.penjualan.export', request()->query()) }}" class="btn btn-sm btn-outline-success"><i class="icon-base bx bx-download me-1"></i> Export Excel</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover tabel-padat datatable">
            <thead>
                <tr>
                    <th>Transaksi</th><th>Barang</th><th>Sales</th><th class="text-end">Total</th>
                    @if ($tampilBatal)
                        <th>Dibatalkan oleh</th><th>Alasan</th>
                    @else
                        <th class="text-end">Untung</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daftar as $penjualan)
                    <tr>
                        <td class="text-nowrap" data-order="{{ $penjualan->tanggal->format('Y-m-d H:i:s') }}">
                            <div class="fw-medium">{{ $penjualan->kode_transaksi }}</div>
                            <small class="text-muted">{{ $penjualan->tanggal->format('j M Y, H:i') }}</small>
                        </td>
                        <td>
                            {{-- Tiap barang 1 baris: nama x qty, harganya di kanan --}}
                            @foreach ($penjualan->detail as $item)
                                <div class="d-flex justify-content-between gap-3">
                                    <span>{{ $item->nama_barang }} <span class="text-muted">x{{ $item->qty }}</span>
                                        @if ($item->garansi_hari > 0)
                                            <span class="badge bg-label-info">garansi {{ $item->garansi_hari }} hari</span>
                                        @endif
                                    </span>
                                    <span class="text-muted text-nowrap">{{ number_format($item->subtotal) }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td>{{ $penjualan->sales->name ?? '-' }}</td>
                        <td class="text-end text-nowrap fw-medium" data-order="{{ $penjualan->total }}">
                            {{ number_format($penjualan->total) }}
                            @if ($penjualan->diskon > 0)
                                <small class="text-muted d-block">diskon {{ number_format($penjualan->diskon) }}</small>
                            @endif
                        </td>

                        @if ($tampilBatal)
                            {{-- Siapa yang membatalkan, kapan, dan alasannya --}}
                            <td class="text-nowrap" data-order="{{ $penjualan->dibatalkan_at->format('Y-m-d H:i:s') }}">
                                <div class="fw-medium text-danger">{{ $penjualan->pembatal->name ?? '-' }}</div>
                                <small class="text-muted">{{ $penjualan->dibatalkan_at->format('j M Y, H:i') }}</small>
                            </td>
                            <td>{{ $penjualan->alasan_batal }}</td>
                        @else
                            <td class="text-end text-nowrap text-success" data-order="{{ $penjualan->keuntungan }}">{{ number_format($penjualan->keuntungan) }}</td>
                        @endif

                        <td class="text-end">
                            {{-- Periode dan halaman ikut dibawa ke struk, supaya dari sana bisa kembali ke daftar ini --}}
                            <a href="{{ route('penjualan.struk', ['id' => $penjualan->id] + request()->only('bulan', 'tahun', 'sales_id', 'tampil')) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Lihat struk"><i class="icon-base bx bx-receipt"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
