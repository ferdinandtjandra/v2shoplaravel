@extends('layouts.app')

@section('judul', 'Struk Penjualan')

@section('isi')

{{-- ====================================================================
     - Di layar  : struk tampil di dalam halaman admin sebagai contoh (preview).
     - Saat print: semua bagian admin disembunyikan, yang tercetak hanya struknya.
     Bentuk struknya sendiri ada di file penjualan/_struk.blade.php
     ==================================================================== --}}
<style>
    /* Kertas struk 80mm: tanpa margin tambahan dari browser */
    @page { margin: 0; }

    @media print {
        /* Sembunyikan semua bagian admin */
        .layout-menu, .layout-navbar, .layout-overlay, .content-backdrop, .tidak-dicetak, .container-p-y > h4, .alert { display: none !important; }

        /* Hilangkan jarak, garis, dan warna pembungkus, supaya struk menempel di pojok kiri atas kertas */
        html, body, .layout-wrapper, .layout-container, .layout-page, .content-wrapper, .container-p-y,
        .row, .kolom-struk, .card, .card-body, .alas-struk, .alas-struk .ticket {
            padding: 0 !important; margin: 0 !important; border: 0 !important;
            box-shadow: none !important; background: #fff !important;
            display: block !important; width: auto !important; max-width: none !important; min-height: 0 !important;
        }

        /* Lebar cetak kertas 80mm = 72mm. Kalau sisi kanan terpotong di printer, ubah juga angka di penjualan/_struk.blade.php */
        .alas-struk .ticket { width: 72mm !important; max-width: 72mm !important; padding: 0 2mm !important; }
    }
</style>

{{-- Transaksi yang sudah dibatalkan --}}
@if ($penjualan->sudahDibatalkan())
    <div class="alert alert-danger tidak-dicetak">
        <strong>TRANSAKSI DIBATALKAN</strong> pada {{ $penjualan->dibatalkan_at->format('j M Y, H:i') }}
        oleh {{ $penjualan->pembatal->name ?? '-' }}. Stok barangnya sudah dikembalikan.
        <br>Alasan: {{ $penjualan->alasan_batal }}
    </div>
@endif

<div class="row">

    {{-- KIRI: contoh struk (bagian inilah yang tercetak) --}}
    <div class="col-lg-7 kolom-struk">
        <div class="card mb-6">
            <h5 class="card-header tidak-dicetak">Preview Struk</h5>
            <div class="card-body">
                @include('penjualan._struk', ['template' => $template, 'penjualan' => $penjualan])
            </div>
        </div>
    </div>

    {{-- KANAN: ringkasan transaksi + tombol (tidak ikut tercetak) --}}
    <div class="col-lg-5 tidak-dicetak">
        <div class="card mb-6">
            <h5 class="card-header">Transaksi</h5>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Kode</span>
                    <span class="fw-medium">{{ $penjualan->kode_transaksi }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-2">
                    <span class="text-muted">Tanggal</span>
                    <span>{{ $penjualan->tanggal->format('j M Y, H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-2">
                    <span class="text-muted">Sales</span>
                    <span>{{ $penjualan->sales->name ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-2">
                    <span class="text-muted">Kasir</span>
                    <span>{{ $penjualan->kasir->name ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between border-top py-2">
                    <span class="text-muted">Jumlah barang</span>
                    <span>{{ $penjualan->detail->sum('qty') }} pcs</span>
                </div>
                @if ($penjualan->keterangan)
                    <div class="border-top py-2">
                        <span class="text-muted d-block">Keterangan</span>
                        {{ $penjualan->keterangan }}
                    </div>
                @endif
                @if ($penjualan->diskon > 0)
                    <div class="d-flex justify-content-between border-top py-2">
                        <span class="text-muted">Diskon</span>
                        <span>- Rp. {{ number_format($penjualan->diskon) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between align-items-center border-top pt-4 mb-6">
                    <span class="fw-medium">TOTAL</span>
                    <span class="fs-3 fw-bold text-primary">Rp. {{ number_format($penjualan->total) }}</span>
                </div>

                {{-- Struk transaksi yang dibatalkan tidak bisa dicetak --}}
                @if (! $penjualan->sudahDibatalkan())
                    <button type="button" class="btn btn-primary btn-lg w-100 mb-3" onclick="window.print()">
                        <i class="icon-base bx bx-printer me-1"></i> Print Struk
                    </button>
                @endif

                {{-- Kalau struk ini dibuka dari Laporan Penjualan (ada bulan di alamat URL),
                     tombolnya kembali ke report dengan periode yang sama. Kalau tidak, ke Transaksi Baru. --}}
                @if (request('bulan') != '')
                    <a href="{{ route('laporan.penjualan', request()->only('bulan', 'tahun', 'tanggal', 'sales_id', 'tampil')) }}" class="btn btn-outline-secondary w-100">
                        <i class="icon-base bx bx-arrow-back me-1"></i> Kembali ke Laporan Penjualan
                    </a>
                @else
                    <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="icon-base bx bx-cart me-1"></i> Transaksi Baru
                    </a>
                @endif

                {{-- Batalkan transaksi: hanya Bos, dan hanya kalau belum dibatalkan --}}
                @if (Auth::user()->isBos() && ! $penjualan->sudahDibatalkan())
                    <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#modalBatal">
                        <i class="icon-base bx bx-x-circle me-1"></i> Batalkan Transaksi
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================= KOTAK BATALKAN TRANSAKSI (hanya Bos) ================= --}}
@if (Auth::user()->isBos() && ! $penjualan->sudahDibatalkan())
    <div class="modal fade tidak-dicetak" id="modalBatal">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('penjualan.batalkan', $penjualan->id) }}" class="modal-content konfirmasi"
                data-pesan="Batalkan transaksi {{ $penjualan->kode_transaksi }}? Stok barang akan dikembalikan dan ini tidak bisa diurungkan.">
                @csrf
                {{-- Periode report ikut dikirim, supaya setelah batal bisa langsung kembali ke daftar yang sama --}}
                <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                <input type="hidden" name="tanggal" value="{{ request('tanggal') }}">
                <input type="hidden" name="sales_id" value="{{ request('sales_id') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Transaksi {{ $penjualan->kode_transaksi }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        Transaksi tidak dihapus, hanya ditandai <strong>dibatalkan</strong>. Stok semua barangnya dikembalikan,
                        dan transaksi ini tidak lagi dihitung di laporan penjualan, dashboard, dan laba rugi.
                    </div>
                    <label class="form-label">Alasan pembatalan <span class="text-danger">*</span></label>
                    <textarea name="alasan_batal" class="form-control" rows="3" placeholder="Contoh: kasir salah input barang" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Batalkan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
