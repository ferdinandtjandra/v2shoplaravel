@extends('layouts.app')

@section('judul', 'Laba / Rugi')

@php
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp

@section('isi')
<div class="row">

    {{-- KIRI: form buat laporan --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Buat Laporan Bulanan</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('laba_rugi.store') }}">
                    @csrf
                    <h6 class="text-muted text-uppercase small mb-3">Periode</h6>
                    <div class="row">
                        <div class="col-7 mb-4">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select">
                                @foreach ($namaBulan as $nomor => $nama)
                                    <option value="{{ $nomor }}" {{ old('bulan', date('n')) == $nomor ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5 mb-4">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select">
                                @for ($tahun = date('Y'); $tahun >= 2022; $tahun--)
                                    <option value="{{ $tahun }}" {{ old('tahun', date('Y')) == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <h6 class="text-muted text-uppercase small mb-3 mt-2">Biaya Bulan Itu</h6>
                    <div class="mb-4">
                        <label class="form-label">Gaji <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="gaji" class="form-control rupiah" value="{{ old('gaji') }}" inputmode="numeric" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Listrik <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="listrik" class="form-control rupiah" value="{{ old('listrik') }}" inputmode="numeric" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Biaya Tak Terduga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="operasional" class="form-control rupiah" value="{{ old('operasional') }}" inputmode="numeric" required>
                        </div>
                        <div class="form-text">Isi 0 kalau tidak ada.</div>
                    </div>

                    <div class="alert alert-primary small" role="alert">
                        Total penjualan dan keuntungan diambil otomatis dari data penjualan bulan tersebut. Satu bulan hanya bisa dibuat satu kali.
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-save me-1"></i> Buat Laporan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- KANAN: daftar laporan --}}
    <div class="col-lg-8">
        <div class="card mb-6">
            <h5 class="card-header">Daftar Laporan Laba / Rugi</h5>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>Periode</th><th class="text-end">Laba Kotor</th><th class="text-end">Total Biaya</th><th class="text-end">Laba Bersih</th><th>Dibuat oleh</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $laporan)
                            <tr>
                                <td class="fw-medium">{{ $laporan->periode }}</td>
                                <td class="text-end text-nowrap">Rp {{ number_format($laporan->laba_kotor) }}</td>
                                <td class="text-end text-nowrap">Rp {{ number_format($laporan->total_biaya) }}</td>
                                <td class="text-end text-nowrap fw-bold {{ $laporan->laba_bersih < 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($laporan->laba_bersih) }}</td>
                                <td>{{ $laporan->user->name ?? '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('laba_rugi.show', $laporan->id) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="icon-base bx bx-printer me-1"></i> Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-6">Belum ada laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
