@extends('layouts.app')

{{-- Halaman cetak untuk 2 dokumen service. $jenis menentukan yang mana:
       'tanda_terima' = surat tanda terima (saat HP ditinggal)
       'nota'         = nota service (saat HP diambil)
     Cara kerjanya sama dengan halaman Struk Penjualan: di layar tampil sebagai preview di dalam
     halaman admin, saat di-print hanya dokumennya yang tercetak (kertas 80mm). --}}
@section('judul', $jenis == 'nota' ? 'Nota Service' : 'Surat Tanda Terima')

@section('isi')

@include('partials.css_struk')
@include('partials.css_cetak_80mm')

<div class="row">

    {{-- KIRI: dokumen (bagian inilah yang tercetak) --}}
    <div class="col-lg-7 kolom-struk">
        <div class="card mb-6">
            <h5 class="card-header tidak-dicetak">Preview {{ $jenis == 'nota' ? 'Nota Service' : 'Tanda Terima' }}</h5>
            <div class="card-body">
                <div class="alas-struk">
                    <div class="ticket">

                        {{-- HEADER toko (yang berlaku saat service dibuat) --}}
                        <div class="header">{!! nl2br(e($service->struk_header)) !!}</div>
                        <div class="garis"></div>

                        @if ($jenis == 'tanda_terima')
                            {{-- ==================== SURAT TANDA TERIMA ==================== --}}
                            <div class="judul-dokumen">TANDA TERIMA SERVICE</div>
                            <div class="baris"><span>No</span><span class="kanan">{{ $service->kode }}</span></div>
                            <div class="baris"><span>Tanggal</span><span class="kanan">{{ $service->tanggal_terima->translatedFormat('j M Y, H:i') }}</span></div>
                            <div class="garis"></div>

                            <div class="baris"><span>Pelanggan</span><span class="kanan">{{ $service->nama_pelanggan }}</span></div>
                            <div class="baris"><span>No. HP</span><span class="kanan">{{ $service->hp_pelanggan }}</span></div>
                            <div class="baris"><span>Perangkat</span><span class="kanan" style="white-space: normal;">{{ $service->merk_tipe }}</span></div>
                            @if ($service->imei)
                                <div class="baris"><span>IMEI</span><span class="kanan">{{ $service->imei }}</span></div>
                            @endif
                            <div class="garis"></div>

                            <div class="label-kecil">Keluhan</div>
                            <div>{{ $service->keluhan }}</div>
                            @if ($service->kerusakan)
                                <div class="label-kecil">Kerusakan</div>
                                <div>{{ $service->kerusakan }}</div>
                            @endif
                            @if ($service->kelengkapan)
                                <div class="label-kecil">Kelengkapan</div>
                                <div>{{ $service->kelengkapan }}</div>
                            @endif
                            @if ($service->kondisi_fisik)
                                <div class="label-kecil">Kondisi fisik</div>
                                <div>{{ $service->kondisi_fisik }}</div>
                            @endif
                            <div class="garis"></div>

                            @if ($service->perkiraan_biaya > 0)
                                <div class="baris"><span>Perkiraan biaya</span><span class="kanan">{{ number_format($service->perkiraan_biaya) }}</span></div>
                            @endif
                            @if ($service->perkiraan_selesai)
                                <div class="baris"><span>Perkiraan selesai</span><span class="kanan">{{ $service->perkiraan_selesai->translatedFormat('j M Y') }}</span></div>
                            @endif
                            <div class="baris"><span>Diterima oleh</span><span class="kanan">{{ $service->penerima->name ?? '-' }}</span></div>
                            <div class="garis"></div>

                            @include('partials.catatan_poin', ['teks' => $service->struk_catatan_tt])
                        @else
                            {{-- ==================== NOTA SERVICE ==================== --}}
                            <div class="judul-dokumen">NOTA SERVICE</div>
                            <div class="baris"><span>No</span><span class="kanan">{{ $service->kode }}</span></div>
                            {{-- Tanggal nota = tanggal diambil. Kalau belum diambil, tanggal hari ini. --}}
                            <div class="baris"><span>Tanggal</span><span class="kanan">{{ ($service->tanggal_diambil ?? now())->translatedFormat('j M Y, H:i') }}</span></div>
                            <div class="baris"><span>Pelanggan</span><span class="kanan">{{ $service->nama_pelanggan }}</span></div>
                            <div class="baris"><span>Perangkat</span><span class="kanan" style="white-space: normal;">{{ $service->merk_tipe }}</span></div>
                            <div class="garis"></div>

                            {{-- Sparepart (dari stok toko maupun dari luar tampil sama di nota) --}}
                            @if ($service->item->where('jenis', '!=', 'jasa')->count() > 0)
                                <div class="sub-judul">Sparepart</div>
                                @foreach ($service->item->where('jenis', '!=', 'jasa') as $item)
                                    <div class="barang">
                                        <div class="nama">{{ $item->nama }}</div>
                                        <div class="baris rincian">
                                            <span>{{ $item->qty }} x {{ number_format($item->harga_satuan) }}</span>
                                            <span class="kanan">{{ number_format($item->subtotal) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Jasa --}}
                            @if ($service->item->where('jenis', 'jasa')->count() > 0)
                                <div class="sub-judul">Jasa</div>
                                @foreach ($service->item->where('jenis', 'jasa') as $item)
                                    <div class="baris"><span>{{ $item->nama }}</span><span class="kanan">{{ number_format($item->subtotal) }}</span></div>
                                @endforeach
                            @endif
                            <div class="garis"></div>

                            {{-- Subtotal dan Diskon hanya tampil kalau ada diskon --}}
                            @if ($service->diskon > 0)
                                <div class="baris"><span>Subtotal</span><span class="kanan">{{ number_format($service->total_sparepart + $service->total_jasa) }}</span></div>
                                <div class="baris"><span>Diskon</span><span class="kanan">-{{ number_format($service->diskon) }}</span></div>
                            @endif
                            <div class="baris total"><span>TOTAL</span><span class="kanan">{{ number_format($service->total) }}</span></div>

                            @if ($service->status == 'diambil')
                                {{-- 1 garansi untuk 1 nota, dihitung dari tanggal HP diambil --}}
                                @if ($service->garansi_hari > 0)
                                    <div style="margin-top: 1.5mm;">Garansi service {{ $service->garansi_hari }} hari s/d {{ $service->tanggal_diambil->copy()->addDays($service->garansi_hari)->translatedFormat('j M Y') }}</div>
                                @else
                                    <div style="margin-top: 1.5mm;">Tanpa garansi</div>
                                @endif
                            @else
                                <div style="margin-top: 1.5mm; font-weight: 700;">BELUM DIBAYAR</div>
                            @endif
                            <div class="garis"></div>

                            @include('partials.catatan_poin', ['teks' => $service->struk_catatan_nota])
                        @endif

                        <div class="penutup">{{ $service->struk_penutup }}</div>

                        {{-- Baris kosong supaya kertas keluar cukup panjang sebelum disobek --}}
                        <br><br><br>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN: ringkasan + tombol (tidak ikut tercetak) --}}
    <div class="col-lg-5 tidak-dicetak">
        <div class="card mb-6">
            <h5 class="card-header">{{ $service->kode }}</h5>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2"><span class="text-muted">Pelanggan</span><span class="fw-medium">{{ $service->nama_pelanggan }}</span></div>
                <div class="d-flex justify-content-between border-top py-2"><span class="text-muted">Perangkat</span><span>{{ $service->merk_tipe }}</span></div>
                <div class="d-flex justify-content-between border-top py-2 mb-4"><span class="text-muted">Status</span><span>@include('service._status', ['service' => $service])</span></div>

                @if ($jenis == 'nota' && $service->status != 'diambil')
                    <div class="alert alert-warning small">Service ini belum diserahkan, jadi nota masih bertanda "BELUM DIBAYAR" dan belum ada garansi serta diskonnya.</div>
                @endif

                <button type="button" class="btn btn-primary btn-lg w-100 mb-3" onclick="window.print()">
                    <i class="icon-base bx bx-printer me-1"></i> Print {{ $jenis == 'nota' ? 'Nota' : 'Tanda Terima' }}
                </button>
                <a href="{{ route('service.show', $service->id) }}" class="btn btn-outline-secondary w-100">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Kembali ke Service
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
