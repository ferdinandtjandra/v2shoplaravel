@extends('layouts.app')

@php
    $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $judulPeriode = $namaBulan[$bulan] . ' ' . $tahun;

    // Angka tambahan untuk laporan
    // Margin = berapa persen laba kotor dari penjualan. Contoh: jual 100, laba kotor 40 -> margin 40%
    $margin = $data['penjualan'] > 0 ? round($data['laba_kotor'] / $data['penjualan'] * 100, 1) : 0;
    $rataRata = $data['transaksi'] > 0 ? round($data['penjualan'] / $data['transaksi']) : 0;
    $rugi = $data['laba_bersih'] < 0;
@endphp

@section('judul', 'Cetak Laba / Rugi')

@section('isi')

{{-- ====================================================================
     Sama seperti halaman Struk Penjualan:
     - Di layar  : laporan tampil di dalam halaman admin sebagai contoh (preview).
     - Saat print: semua bagian admin disembunyikan, yang tercetak hanya lembar laporannya.
     Lembarnya dirancang untuk printer biasa, kertas A4 tegak, hitam putih.
     ==================================================================== --}}
<style>
    /* ---------- Lembar laporan ---------- */
    .lembar-laporan { background: #fff; width: 100%; max-width: 720px; padding: 40px 44px; box-sizing: border-box; }
    .lembar-laporan, .lembar-laporan * { font-family: Arial, Helvetica, sans-serif; color: #000; line-height: 1.4; }

    /* Kepala laporan: nama toko di kiri, judul laporan di kanan */
    .lembar-laporan .kepala { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #000; padding-bottom: 12px; margin-bottom: 20px; }
    .lembar-laporan .nama-toko { font-size: 22px; font-weight: 700; letter-spacing: .5px; }
    .lembar-laporan .alamat-toko { font-size: 11px; }
    .lembar-laporan .judul { text-align: right; }
    .lembar-laporan .judul .besar { font-size: 16px; font-weight: 700; letter-spacing: 1px; }
    .lembar-laporan .judul .periode { font-size: 13px; }

    /* 3 kotak kecil di bawah kepala */
    .lembar-laporan .kotak-info { display: flex; gap: 12px; margin-bottom: 22px; }
    .lembar-laporan .kotak-info div { flex: 1; border: 1px solid #000; padding: 8px 10px; }
    .lembar-laporan .kotak-info small { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
    .lembar-laporan .kotak-info strong { font-size: 15px; }

    /* Tabel angka */
    .lembar-laporan table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .lembar-laporan td { padding: 6px 4px; }
    .lembar-laporan .bagian td { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #000; padding-top: 16px; }
    .lembar-laporan .angka { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .lembar-laporan .masuk td:first-child { padding-left: 16px; }
    .lembar-laporan .jumlah td { font-weight: 700; border-top: 1px solid #000; }

    /* Baris hasil akhir */
    .lembar-laporan .hasil { display: flex; justify-content: space-between; align-items: center; border: 2px solid #000; padding: 12px 14px; margin-top: 22px; }
    .lembar-laporan .hasil .label { font-size: 15px; font-weight: 700; letter-spacing: 1px; }
    .lembar-laporan .hasil .nilai { font-size: 20px; font-weight: 700; }
    .lembar-laporan .catatan { font-size: 11px; font-style: italic; margin-top: 10px; }

    .lembar-laporan .kaki { font-size: 10px; margin-top: 28px; border-top: 1px solid #000; padding-top: 6px; display: flex; justify-content: space-between; }

    /* Kotak abu-abu di belakang lembar, hanya hiasan di layar */
    .alas-laporan { background: #f5f5f9; padding: 24px; display: flex; justify-content: center; border-radius: 8px; }
    .alas-laporan .lembar-laporan { box-shadow: 0 2px 8px rgba(0, 0, 0, .15); }

    /* ---------- Saat dicetak ---------- */
    @page { size: A4 portrait; margin: 18mm 16mm; }

    @media print {
        /* Sembunyikan semua bagian admin */
        .layout-menu, .layout-navbar, .layout-overlay, .content-backdrop, .tidak-dicetak, .container-p-y > h4, .alert { display: none !important; }

        /* Hilangkan jarak, garis, dan warna pembungkus, supaya yang tercetak hanya lembar laporannya */
        html, body, .layout-wrapper, .layout-container, .layout-page, .content-wrapper, .container-p-y,
        .row, .kolom-laporan, .card, .card-body, .alas-laporan {
            padding: 0 !important; margin: 0 !important; border: 0 !important;
            box-shadow: none !important; background: #fff !important;
            display: block !important; width: auto !important; max-width: none !important; min-height: 0 !important;
        }

        /* Lembar memenuhi lebar kertas (jarak pinggir sudah diatur oleh @page di atas) */
        .alas-laporan .lembar-laporan { box-shadow: none !important; padding: 0 !important; max-width: none !important; width: 100% !important; }
    }
</style>

<div class="row">

    {{-- KIRI: contoh laporan (bagian inilah yang tercetak) --}}
    <div class="col-xl-8 kolom-laporan">
        <div class="card mb-6">
            <h5 class="card-header tidak-dicetak">Preview Laporan (kertas A4)</h5>
            <div class="card-body">
                <div class="alas-laporan">
                    <div class="lembar-laporan">

                        {{-- Kepala --}}
                        <div class="kepala">
                            <div>
                                <div class="nama-toko">{{ strtoupper(config('toko.nama')) }}</div>
                                <div class="alamat-toko">{{ config('toko.alamat') }}{{ config('toko.hp') ? ' · Hp. ' . config('toko.hp') : '' }}</div>
                            </div>
                            <div class="judul">
                                <div class="besar">LAPORAN LABA / RUGI</div>
                                <div class="periode">Periode {{ $judulPeriode }}</div>
                            </div>
                        </div>

                        {{-- Ringkasan singkat --}}
                        <div class="kotak-info">
                            <div><small>Jumlah transaksi</small><strong>{{ number_format($data['transaksi']) }}</strong></div>
                            <div><small>Rata-rata per transaksi</small><strong>Rp {{ number_format($rataRata) }}</strong></div>
                            <div><small>Margin laba kotor</small><strong>{{ $margin }}%</strong></div>
                        </div>

                        {{-- Angka --}}
                        <table>
                            <tr class="bagian"><td colspan="2">Pendapatan</td></tr>
                            <tr class="masuk"><td>Total Penjualan</td><td class="angka">Rp {{ number_format($data['penjualan']) }}</td></tr>
                            <tr class="masuk"><td>Harga Pokok Penjualan</td><td class="angka">(Rp {{ number_format($data['hpp']) }})</td></tr>
                            <tr class="jumlah"><td>Laba Kotor</td><td class="angka">Rp {{ number_format($data['laba_kotor']) }}</td></tr>

                            <tr class="bagian"><td colspan="2">Biaya</td></tr>
                            <tr class="masuk"><td>Gaji</td><td class="angka">Rp {{ number_format($data['gaji']) }}</td></tr>
                            <tr class="masuk"><td>Listrik</td><td class="angka">Rp {{ number_format($data['listrik']) }}</td></tr>
                            <tr class="masuk"><td>Biaya Tak Terduga</td><td class="angka">Rp {{ number_format($data['operasional']) }}</td></tr>
                            <tr class="jumlah"><td>Total Biaya</td><td class="angka">(Rp {{ number_format($data['biaya']) }})</td></tr>
                        </table>

                        {{-- Hasil akhir --}}
                        <div class="hasil">
                            <span class="label">{{ $rugi ? 'RUGI BERSIH' : 'LABA BERSIH' }}</span>
                            <span class="nilai">{{ $rugi ? '(Rp ' . number_format(abs($data['laba_bersih'])) . ')' : 'Rp ' . number_format($data['laba_bersih']) }}</span>
                        </div>

                        @if (! $data['biaya_diisi'])
                            <div class="catatan">Catatan: biaya bulan ini belum diisi, jadi laba bersih masih sama dengan laba kotor.</div>
                        @endif
                        <div class="catatan">Angka dalam kurung adalah pengurang. Transaksi yang dibatalkan tidak dihitung.</div>

                        <div class="kaki">
                            <span>{{ config('toko.nama') }} · Laba / Rugi {{ $periode }}</span>
                            <span>Dicetak {{ now()->translatedFormat('j F Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN: ringkasan + tombol (tidak ikut tercetak) --}}
    <div class="col-xl-4 tidak-dicetak">
        <div class="card mb-6">
            <h5 class="card-header">{{ $judulPeriode }}</h5>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Transaksi</span>
                    <span>{{ number_format($data['transaksi']) }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-2">
                    <span class="text-muted">Penjualan</span>
                    <span>Rp {{ number_format($data['penjualan']) }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-2">
                    <span class="text-muted">Laba kotor</span>
                    <span>Rp {{ number_format($data['laba_kotor']) }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-2">
                    <span class="text-muted">Biaya</span>
                    <span>
                        @if ($data['biaya_diisi'])
                            Rp {{ number_format($data['biaya']) }}
                        @else
                            <span class="badge bg-label-warning">belum diisi</span>
                        @endif
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center border-top pt-4 mb-6">
                    <span class="fw-medium">{{ $rugi ? 'RUGI BERSIH' : 'LABA BERSIH' }}</span>
                    <span class="fs-4 fw-bold {{ $rugi ? 'text-danger' : 'text-primary' }}">Rp {{ number_format($data['laba_bersih']) }}</span>
                </div>

                <button type="button" class="btn btn-primary btn-lg w-100 mb-3" onclick="window.print()">
                    <i class="icon-base bx bx-printer me-1"></i> Print Laporan
                </button>
                <a href="{{ route('laba_rugi.index', ['tahun' => $tahun]) }}" class="btn btn-outline-secondary w-100">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Kembali ke Laba / Rugi {{ $tahun }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
