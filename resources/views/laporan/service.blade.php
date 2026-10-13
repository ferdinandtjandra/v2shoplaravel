@extends('layouts.app')

@section('judul', 'Laporan Service')

@php
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp

@section('isi')
<div class="card mb-6">
    <h5 class="card-header">Pilih Periode</h5>
    <div class="card-body">
        {{-- method GET: pilihan periode ikut di alamat URL, jadi halaman bisa di-refresh atau dibookmark --}}
        <form method="GET" action="{{ route('laporan.service') }}">
            {{-- Halaman yang sedang dibuka (lunas / batal) ikut dibawa saat periode diganti --}}
            <input type="hidden" name="tampil" value="{{ request('tampil') }}">
            <div class="row align-items-end">
                <div class="col-md-2 mb-4 mb-md-0">
                    <label class="form-label">Tanggal</label>
                    {{-- Opsional. Dikosongkan = semua tanggal di bulan itu. --}}
                    <select name="tanggal" class="form-select">
                        <option value="">Semua</option>
                        @for ($tgl = 1; $tgl <= 31; $tgl++)
                            <option value="{{ $tgl }}" {{ request('tanggal') == $tgl ? 'selected' : '' }}>{{ $tgl }}</option>
                        @endfor
                    </select>
                </div>
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
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-search me-1"></i> Tampilkan</button>
                </div>
            </div>
        </form>
        <small class="text-muted d-block mt-3">Pendapatan service dihitung saat HP diambil dan dibayar, jadi laporan ini memakai tanggal diambil.</small>
    </div>
</div>

@if ($daftar !== null)

@php
    // Pilihan periode, dibawa ke semua link di bawah supaya selalu kembali ke daftar yang sama
    $periode = request()->only('bulan', 'tahun', 'tanggal');
@endphp

{{-- 2 halaman: service yang sudah diambil (lunas) dan yang dibatalkan --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('laporan.service', $periode) }}" class="btn btn-sm {{ $tampilBatal ? 'btn-outline-secondary' : 'btn-primary' }}">Sudah diambil</a>
    <a href="{{ route('laporan.service', $periode + ['tampil' => 'batal']) }}" class="btn btn-sm {{ $tampilBatal ? 'btn-danger' : 'btn-outline-secondary' }}">Dibatalkan ({{ $jumlahBatal }})</a>
</div>

@if (! $tampilBatal)
    {{-- Ringkasan keuangan periode yang dipilih --}}
    <div class="row">
        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted d-block">Jumlah Nota</small>
                <span class="fs-4 fw-bold">{{ number_format($daftar->count()) }}</span>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted d-block">Total Pendapatan</small>
                <span class="fs-4 fw-bold text-primary">Rp {{ number_format($daftar->sum('total')) }}</span>
                <small class="text-muted d-block">sparepart {{ number_format($daftar->sum('total_sparepart')) }} · jasa {{ number_format($daftar->sum('total_jasa')) }} · diskon {{ number_format($daftar->sum('diskon')) }}</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted d-block">Modal Sparepart</small>
                <span class="fs-4 fw-bold">Rp {{ number_format($daftar->sum('modal')) }}</span>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100"><div class="card-body">
                <small class="text-muted d-block">Keuntungan</small>
                <span class="fs-4 fw-bold text-success">Rp {{ number_format($daftar->sum('keuntungan')) }}</span>
            </div></div>
        </div>
    </div>
@endif

<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $tampilBatal ? 'Service Dibatalkan' : 'Service Sudah Diambil' }} {{ is_numeric(request('tanggal')) ? (int) request('tanggal') : '' }} {{ $namaBulan[(int) request('bulan')] ?? '' }} {{ request('tahun') }}</h5>
        {{-- Export mengikuti periode dan halaman yang sedang dibuka --}}
        <a href="{{ route('laporan.service.export', request()->query()) }}" class="btn btn-sm btn-outline-success"><i class="icon-base bx bx-download me-1"></i> Export Excel</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover tabel-padat datatable">
            <thead>
                <tr>
                    <th>Service</th><th>Pelanggan</th><th>Sparepart dan Jasa</th><th>Teknisi</th>
                    @if ($tampilBatal)
                        <th>Dibatalkan oleh</th><th>Alasan</th>
                    @else
                        <th class="text-end">Total</th><th class="text-end">Modal</th><th class="text-end">Untung</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daftar as $service)
                    @php $waktu = $tampilBatal ? $service->batal_at : $service->tanggal_diambil; @endphp
                    <tr>
                        <td class="text-nowrap" data-order="{{ $waktu->format('Y-m-d H:i:s') }}">
                            <div class="fw-medium">{{ $service->kode }}</div>
                            <small class="text-muted">{{ $waktu->translatedFormat('j M Y, H:i') }}</small>
                        </td>
                        <td>
                            <div>{{ $service->nama_pelanggan }}</div>
                            <small class="text-muted">{{ $service->merk_tipe }}</small>
                        </td>
                        <td>
                            {{-- Tiap sparepart / jasa 1 baris --}}
                            @foreach ($service->item as $item)
                                <div class="d-flex justify-content-between gap-3">
                                    <span>{{ $item->nama }} <span class="text-muted">{{ $item->jenis == 'jasa' ? '(jasa)' : 'x' . $item->qty }}</span></span>
                                    <span class="text-muted text-nowrap">{{ number_format($item->subtotal) }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td>{{ $service->nama_teknisi ?: '-' }}</td>

                        @if ($tampilBatal)
                            <td class="text-nowrap">
                                <div class="fw-medium text-danger">{{ $service->pembatal->name ?? '-' }}</div>
                            </td>
                            <td>{{ $service->alasan_batal }}</td>
                        @else
                            <td class="text-end text-nowrap fw-medium" data-order="{{ $service->total }}">
                                {{ number_format($service->total) }}
                                @if ($service->diskon > 0)
                                    <small class="text-muted d-block">diskon {{ number_format($service->diskon) }}</small>
                                @endif
                            </td>
                            <td class="text-end text-nowrap text-muted" data-order="{{ $service->modal }}">{{ number_format($service->modal) }}</td>
                            <td class="text-end text-nowrap text-success" data-order="{{ $service->keuntungan }}">{{ number_format($service->keuntungan) }}</td>
                        @endif

                        <td class="text-end">
                            <a href="{{ route('service.show', $service->id) }}" class="btn btn-sm btn-outline-primary">Buka</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
