@extends('layouts.app')

@section('judul', 'Service ' . $service->kode)

@php
    $bos = Auth::user()->isBos();
    $bisaDiubah = $service->masihBisaDiubah();   // true = sparepart dan jasa masih boleh ditambah / dihapus
@endphp

@section('isi')

{{-- Service yang dibatalkan --}}
@if ($service->status == 'batal')
    <div class="alert alert-danger">
        <strong>SERVICE DIBATALKAN</strong> pada {{ $service->batal_at->translatedFormat('j M Y, H:i') }} oleh {{ $service->pembatal->name ?? '-' }}.
        Sparepart dari stok toko sudah dikembalikan.
        <br>Alasan: {{ $service->alasan_batal }}
    </div>
@endif

<div class="row">

    {{-- ================= KOLOM KIRI ================= --}}
    <div class="col-lg-8">

        {{-- ---------- 1. DATA PELANGGAN DAN HP ---------- --}}
        <div class="card mb-6">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1">{{ $service->merk_tipe }}</h5>
                    @include('service._status', ['service' => $service])
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('service.tanda_terima', $service->id) }}" class="btn btn-sm btn-outline-primary"><i class="icon-base bx bx-printer me-1"></i> Tanda Terima</a>
                    @if ($bisaDiubah)
                        <a href="{{ route('service.edit', $service->id) }}" class="btn btn-sm btn-outline-secondary"><i class="icon-base bx bx-edit me-1"></i> Ubah Data</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between py-2"><span class="text-muted">Pelanggan</span><span class="fw-medium">{{ $service->nama_pelanggan }}</span></div>
                        <div class="d-flex justify-content-between border-top py-2"><span class="text-muted">No. HP</span><span>{{ $service->hp_pelanggan }}</span></div>
                        <div class="d-flex justify-content-between border-top py-2"><span class="text-muted">IMEI</span><span>{{ $service->imei ?: '-' }}</span></div>
                        <div class="d-flex justify-content-between border-top py-2"><span class="text-muted">Diterima</span><span>{{ $service->tanggal_terima->translatedFormat('j M Y, H:i') }} · {{ $service->penerima->name ?? '-' }}</span></div>
                        <div class="d-flex justify-content-between border-top py-2"><span class="text-muted">Teknisi</span><span>{{ $service->nama_teknisi ?: '-' }}</span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="py-2"><span class="text-muted d-block">Keluhan</span>{{ $service->keluhan }}</div>
                        <div class="border-top py-2"><span class="text-muted d-block">Kerusakan (hasil cek)</span>{{ $service->kerusakan ?: 'belum diisi' }}</div>
                        <div class="border-top py-2"><span class="text-muted d-block">Kelengkapan · Kondisi fisik</span>{{ $service->kelengkapan ?: '-' }} · {{ $service->kondisi_fisik ?: '-' }}</div>
                        <div class="border-top py-2">
                            <span class="text-muted d-block">Perkiraan</span>
                            {{ $service->perkiraan_biaya > 0 ? 'Rp ' . number_format($service->perkiraan_biaya) : 'biaya belum diisi' }}
                            · {{ $service->perkiraan_selesai ? 'selesai ' . $service->perkiraan_selesai->translatedFormat('j M Y') : 'tanggal belum diisi' }}
                        </div>
                    </div>
                </div>
                @if ($service->catatan)
                    <div class="border-top pt-2 mt-2"><span class="text-muted">Catatan:</span> {{ $service->catatan }}</div>
                @endif
            </div>
        </div>

        {{-- ---------- 2. SPAREPART DAN JASA ---------- --}}
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Sparepart dan Jasa</h5>
                <span class="badge bg-label-primary">{{ $service->item->count() }} baris</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover tabel-padat mb-0">
                    <thead>
                        <tr>
                            <th>Jenis</th><th>Nama</th><th class="text-center">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th>
                            @if ($bos) <th class="text-end">Modal</th> @endif
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($service->item as $item)
                            <tr>
                                <td class="text-nowrap">
                                    @if ($item->jenis == 'stok')
                                        <span class="badge bg-label-primary">Stok toko</span>
                                    @elseif ($item->jenis == 'luar')
                                        <span class="badge bg-label-info">Dari luar</span>
                                    @else
                                        <span class="badge bg-label-success">Jasa</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $item->nama }}</div>
                                    <small class="text-muted">diinput {{ $item->user->name ?? '-' }}, {{ $item->created_at->translatedFormat('j M H:i') }}</small>
                                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end text-nowrap">{{ number_format($item->harga_satuan) }}</td>
                                <td class="text-end text-nowrap fw-medium">{{ number_format($item->subtotal) }}</td>
                                {{-- Modal hanya untuk Bos --}}
                                @if ($bos) <td class="text-end text-nowrap text-muted">{{ number_format($item->modal_satuan * $item->qty) }}</td> @endif
                                <td class="text-end">
                                    @if ($bisaDiubah)
                                        <form method="POST" action="{{ route('service.hapus_item', [$service->id, $item->id]) }}" class="konfirmasi"
                                            data-pesan="Hapus {{ $item->nama }} dari service ini?{{ $item->jenis == 'stok' ? ' Stoknya akan dikembalikan.' : '' }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-6">Belum ada sparepart atau jasa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ---------- 3. TAMBAH SPAREPART / JASA (selama belum diambil dan belum batal) ----------
             Satu kartu dengan 3 pilihan di atasnya. Yang tampil hanya form dari pilihan yang sedang dibuka,
             jadi tiap form mendapat lebar penuh. Pilihan terakhir diingat browser (JavaScript di bawah). --}}
        @if ($bisaDiubah)
            <div class="card mb-6">
                <div class="card-header pb-0">
                    <h5 class="mb-3">Tambah Sparepart / Jasa</h5>
                    <ul class="nav nav-pills flex-wrap gap-2" id="pilihanTambah">
                        <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabStok"><i class="icon-base bx bx-box me-1"></i> Sparepart dari stok toko</button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#tabLuar"><i class="icon-base bx bx-store me-1"></i> Sparepart dari luar</button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#tabJasa"><i class="icon-base bx bx-wrench me-1"></i> Jasa pengerjaan</button></li>
                    </ul>
                </div>
                <div class="card-body pt-4">
                    <div class="tab-content p-0">

                        {{-- 3a. Sparepart dari stok toko --}}
                        <div class="tab-pane fade show active" id="tabStok">
                            <form method="POST" action="{{ route('service.tambah_stok', $service->id) }}">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-md-7 mb-3">
                                        <label class="form-label">Barang</label>
                                        {{-- Barang dicari ke server saat diketik (fungsinya ada di layouts/app.blade.php) --}}
                                        <select name="barang_id" id="pilihBarang" class="form-select" required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-2 mb-3">
                                        <label class="form-label">Qty</label>
                                        <input type="number" name="qty" class="form-control" min="1" value="1" required>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-plus"></i> Tambah</button>
                                    </div>
                                </div>
                                {{-- Diisi JavaScript setelah barang dipilih: harga yang dipakai dan stoknya --}}
                                <div class="fw-medium" id="teksHargaStok"></div>
                                <small class="text-muted">Harga otomatis memakai Harga Jual + Ongkir. Stok langsung berkurang saat ditambahkan.</small>
                            </form>
                        </div>

                        {{-- 3b. Sparepart dari luar --}}
                        <div class="tab-pane fade" id="tabLuar">
                            <form method="POST" action="{{ route('service.tambah_luar', $service->id) }}">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Nama sparepart</label>
                                        <input type="text" name="nama" class="form-control" maxlength="150" placeholder="Contoh: IC Power" required>
                                    </div>
                                    <div class="col-4 col-md-2 mb-3">
                                        <label class="form-label">Qty</label>
                                        <input type="number" name="qty" class="form-control" min="1" value="1" required>
                                    </div>
                                    <div class="col-8 col-md-5 mb-3">
                                        <label class="form-label">Harga ke pelanggan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="harga" class="form-control rupiah" inputmode="numeric" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-end">
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Harga beli <span class="text-muted fw-normal">(opsional, untuk hitung laba)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="harga_beli" class="form-control rupiah" inputmode="numeric">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3"></div>
                                    <div class="col-md-3 mb-3">
                                        <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-plus"></i> Tambah</button>
                                    </div>
                                </div>
                                <small class="text-muted">Untuk sparepart yang dibeli di tempat lain. Tidak mengubah stok toko.</small>
                            </form>
                        </div>

                        {{-- 3c. Jasa --}}
                        <div class="tab-pane fade" id="tabJasa">
                            <form method="POST" action="{{ route('service.tambah_jasa', $service->id) }}">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Keterangan jasa</label>
                                        <input type="text" name="nama" class="form-control" maxlength="150" placeholder="Contoh: Jasa ganti LCD" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nominal</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="harga" class="form-control rupiah" inputmode="numeric" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-plus"></i> Tambah</button>
                                    </div>
                                </div>
                                <small class="text-muted">Keterangan dan nominalnya bebas. Boleh ditambahkan lebih dari satu.</small>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ================= KOLOM KANAN: total dan tombol aksi ================= --}}
    <div class="col-lg-4">
        <div class="card mb-6 position-sticky" style="top: 90px;">
            <h5 class="card-header">{{ $service->kode }}</h5>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Sparepart</span><span>Rp {{ number_format($service->total_sparepart) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Jasa</span><span>Rp {{ number_format($service->total_jasa) }}</span></div>
                @if ($service->diskon > 0)
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Diskon</span><span>- Rp {{ number_format($service->diskon) }}</span></div>
                @endif
                <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
                    <span class="fw-medium">TOTAL</span>
                    {{-- data-sebelum-diskon dibaca JavaScript untuk menghitung total saat diskon diketik --}}
                    <span class="fs-3 fw-bold text-primary text-nowrap" id="teksTotal" data-sebelum-diskon="{{ $service->total_sparepart + $service->total_jasa }}">Rp {{ number_format($service->total) }}</span>
                </div>

                {{-- Modal dan keuntungan: hanya Bos --}}
                @if ($bos)
                    <div class="d-flex justify-content-between small text-muted"><span>Modal sparepart</span><span>Rp {{ number_format($service->modal) }}</span></div>
                    <div class="d-flex justify-content-between small text-muted mb-3"><span>Keuntungan</span><span>Rp {{ number_format($service->keuntungan) }}</span></div>
                @endif

                {{-- ---------- Tombol sesuai status ---------- --}}
                @if (in_array($service->status, ['diterima', 'dikerjakan']))
                    <form method="POST" action="{{ route('service.selesai', $service->id) }}" class="konfirmasi" data-pesan="Tandai service ini selesai dan siap diambil?">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg w-100 mb-3"><i class="icon-base bx bx-check me-1"></i> Tandai Selesai</button>
                    </form>
                @endif

                @if ($service->status == 'selesai')
                    <div class="alert alert-warning small">
                        Selesai {{ $service->tanggal_selesai->translatedFormat('j M Y, H:i') }}. Hubungi pelanggan untuk mengambil HP-nya.
                    </div>

                    {{-- Membuka WhatsApp dengan pesan siap kirim. Teknisi tinggal menekan Kirim. --}}
                    @php
                        $pesanWa = 'Halo ' . $service->nama_pelanggan . ', HP ' . $service->merk_tipe . ' Anda (No. ' . $service->kode . ') sudah selesai diservice. '
                            . 'Total biaya Rp ' . number_format($service->total) . '. Silakan diambil di ' . config('toko.nama') . '. Terima kasih.';
                    @endphp
                    <a href="https://wa.me/{{ $service->nomorWhatsapp() }}?text={{ urlencode($pesanWa) }}" target="_blank" class="btn btn-outline-success w-100 mb-3">
                        <i class="icon-base bx bxl-whatsapp me-1"></i> Hubungi via WhatsApp
                    </a>

                    {{-- Serah terima + pembayaran: garansi dan diskon diisi di sini --}}
                    <form method="POST" action="{{ route('service.ambil', $service->id) }}" class="konfirmasi border-top pt-3" data-pesan="HP diserahkan ke pelanggan dan pembayaran sudah diterima?">
                        @csrf
                        <h6 class="mb-3">Serah Terima dan Pembayaran</h6>
                        <div class="mb-3">
                            <label class="form-label">Garansi service <span class="text-danger">*</span></label>
                            <select name="garansi_hari" class="form-select" required>
                                @foreach (\App\Models\PenjualanDetail::PILIHAN_GARANSI as $hari => $label)
                                    <option value="{{ $hari }}" {{ old('garansi_hari', 0) == $hari ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diskon</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="diskon" id="inputDiskon" class="form-control rupiah" value="{{ old('diskon') }}" inputmode="numeric" placeholder="0">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 mb-3"><i class="icon-base bx bx-check me-1"></i> Diambil dan Dibayar</button>
                    </form>
                @endif

                @if (in_array($service->status, ['selesai', 'diambil']))
                    <a href="{{ route('service.nota', $service->id) }}" class="btn btn-primary w-100 mb-3"><i class="icon-base bx bx-printer me-1"></i> Nota Service</a>
                @endif

                @if ($service->status == 'diambil')
                    <div class="small text-muted mb-3">
                        Diambil {{ $service->tanggal_diambil->translatedFormat('j M Y, H:i') }}, diserahkan oleh {{ $service->penyerah->name ?? '-' }}.
                        @if ($service->garansi_hari > 0)
                            Garansi {{ $service->garansi_hari }} hari s/d {{ $service->tanggal_diambil->copy()->addDays($service->garansi_hari)->translatedFormat('j M Y') }}.
                        @else
                            Tanpa garansi.
                        @endif
                    </div>
                @endif

                {{-- Batalkan: sebelum diambil boleh semua level, setelah diambil hanya Bos --}}
                @if ($bisaDiubah || ($service->status == 'diambil' && $bos))
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#modalBatal">
                        <i class="icon-base bx bx-x-circle me-1"></i> Batalkan Service
                    </button>
                @endif

                <a href="{{ route('service.index') }}" class="btn btn-outline-secondary w-100 mt-3"><i class="icon-base bx bx-arrow-back me-1"></i> Daftar Service</a>
            </div>
        </div>
    </div>
</div>

{{-- ================= KOTAK BATALKAN SERVICE ================= --}}
@if ($bisaDiubah || ($service->status == 'diambil' && $bos))
    <div class="modal fade" id="modalBatal">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('service.batal', $service->id) }}" class="modal-content konfirmasi"
                data-pesan="Batalkan service {{ $service->kode }}? Ini tidak bisa diurungkan.">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Service {{ $service->kode }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        Dipakai kalau pelanggan batal atau HP tidak bisa diperbaiki. Service tidak dihapus, hanya ditandai <strong>batal</strong>,
                        dan sparepart yang diambil dari stok toko dikembalikan ke stok.
                    </div>
                    <label class="form-label">Alasan pembatalan <span class="text-danger">*</span></label>
                    <textarea name="alasan_batal" class="form-control" rows="3" placeholder="Contoh: pelanggan tidak jadi service" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Batalkan Service</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@section('script')
<script>
    // Kotak pilih sparepart dari stok: mencari ke server saat diketik, hanya barang yang masih ada stoknya
    if ($('#pilihBarang').length > 0) {
        aktifkanCariBarang('#pilihBarang', '{{ route('barang.cari') }}');

        // Setelah barang dipilih, tampilkan harga yang akan dipakai (Harga Jual + Ongkir) dan stoknya
        $('#pilihBarang').on('select2:select', function (kejadian) {
            var barang = kejadian.params.data;
            $('#teksHargaStok').text('Harga Rp ' + formatRibuan(String(barang.harga_ongkir)) + ' per pcs · stok tersedia ' + barang.qty);
        });
    }

    // Pilihan "Tambah Sparepart / Jasa" yang terakhir dibuka diingat oleh browser,
    // supaya setelah menekan Tambah (halaman dimuat ulang) pilihannya tidak kembali ke awal.
    $('#pilihanTambah button').on('shown.bs.tab', function () {
        try { localStorage.setItem('pilihanTambahService', $(this).data('bs-target')); } catch (e) {}
    });
    try {
        var pilihanTerakhir = localStorage.getItem('pilihanTambahService');
        if (pilihanTerakhir) {
            var tombol = document.querySelector('#pilihanTambah button[data-bs-target="' + pilihanTerakhir + '"]');
            if (tombol) {
                bootstrap.Tab.getOrCreateInstance(tombol).show();
            }
        }
    } catch (e) {}

    // Total di kolom kanan = (sparepart + jasa) - diskon yang sedang diketik
    $('#inputDiskon').on('input', function () {
        var sebelumDiskon = Number($('#teksTotal').data('sebelum-diskon'));
        var diskon = Number((this.value || '').replace(/[^0-9]/g, ''));
        var total = sebelumDiskon - diskon;

        if (total < 0) {
            total = 0;
        }

        $('#teksTotal').text('Rp ' + formatRibuan(String(total)));
    });
</script>
@endsection
