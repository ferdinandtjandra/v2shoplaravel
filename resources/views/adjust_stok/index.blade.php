@extends('layouts.app')

@section('judul', 'Penyesuaian Stok')

@section('isi')
<div class="row">

    {{-- KIRI: form tambah / kurang stok --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Tambah / Kurang Stok</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('adjust_stok.store') }}" class="konfirmasi" data-pesan="Simpan perubahan stok ini? Riwayat tidak bisa dihapus.">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Barang <span class="text-danger">*</span></label>
                        {{-- Stok tiap barang dititipkan di data-qty, lalu dibaca JavaScript di bawah --}}
                        <select name="barang_id" id="pilihBarang" class="form-select" required>
                            <option value="">- Cari kode atau nama barang -</option>
                            @foreach ($barang as $b)
<option value="{{ $b->id }}" data-qty="{{ $b->qty }}" {{ old('barang_id') == $b->id ? 'selected' : '' }}>{{ $b->kode }} - {{ $b->nama }}</option>
                            @endforeach
                        </select>
                        {{-- Diisi JavaScript setelah barang dipilih. Contoh: "Stok saat ini 5 pcs" --}}
                        <div class="form-text fw-medium" id="teksStok"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Jenis <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check pilihan-jenis" name="jenis" id="jenisKurang" value="kurang" {{ old('jenis', 'kurang') == 'kurang' ? 'checked' : '' }}>
                            <label class="btn btn-outline-danger" for="jenisKurang"><i class="icon-base bx bx-minus me-1"></i> Kurangi</label>

                            <input type="radio" class="btn-check pilihan-jenis" name="jenis" id="jenisTambah" value="tambah" {{ old('jenis') == 'tambah' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="jenisTambah"><i class="icon-base bx bx-plus me-1"></i> Tambah</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="qty" id="qtyAdjust" class="form-control" min="1" value="{{ old('qty', 1) }}" required>
                            <span class="input-group-text">pcs</span>
                        </div>
                        {{-- Peringatan, hanya muncul kalau jumlah yang dikurangi melebihi stok --}}
                        <div class="form-text text-danger" id="teksPeringatan"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Alasan <span class="text-danger">*</span></label>
                        <select name="alasan" id="pilihAlasan" class="form-select" required>
                            <option value="">- Pilih Alasan -</option>
                            @foreach (\App\Models\StokPenyesuaian::DAFTAR_ALASAN as $alasan)
                                <option value="{{ $alasan }}" {{ old('alasan') == $alasan ? 'selected' : '' }}>{{ $alasan }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kotak ini hanya muncul kalau alasannya "Pindah ke toko lain" atau "Terima dari toko lain" (diatur JavaScript di bawah) --}}
                    <div class="mb-4" id="kotakToko" style="display: none;">
                        <label class="form-label"><span id="labelToko">Toko</span> <span class="text-danger">*</span></label>
                        <select name="toko_id" id="pilihToko" class="form-select">
                            <option value="">- Pilih Toko -</option>
                            @foreach ($daftarToko as $toko)
                                <option value="{{ $toko->id }}" {{ old('toko_id') == $toko->id ? 'selected' : '' }}>{{ $toko->nama }}</option>
                            @endforeach
                        </select>
                        @if ($daftarToko->count() == 0)
                            <div class="form-text text-danger">Belum ada toko. Bos perlu menambahkannya dulu di menu Toko.</div>
                        @endif
                    </div>

                    <div class="mb-6">
                        <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: ambil 2 pcs untuk toko V2 Com" required>{{ old('keterangan') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- KANAN: riwayat (hanya untuk Bos) --}}
    @if (Auth::user()->isBos())
    <div class="col-lg-8">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riwayat Penyesuaian Stok</h5>
                <small class="text-muted">500 terakhir</small>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover tabel-padat datatable">
                    <thead>
                        <tr><th>Waktu</th><th>Barang</th><th class="text-center">Jumlah</th><th class="text-end">Stok Awal</th><th class="text-end">Stok Akhir</th><th>Alasan / Keterangan</th><th>Oleh</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $r)
                            <tr>
                                <td class="text-nowrap" data-order="{{ $r->created_at->format('Y-m-d H:i:s') }}">
                                    <div>{{ $r->created_at->format('j M Y') }}</div>
                                    <small class="text-muted">{{ $r->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $r->barang->nama }}</div>
                                    <small class="text-muted">{{ $r->barang->kode }}</small>
                                </td>
                                <td class="text-center">
                                    @if ($r->jenis == 'tambah')
                                        <span class="badge bg-label-success">+{{ $r->qty }}</span>
                                    @else
                                        <span class="badge bg-label-danger">-{{ $r->qty }}</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $r->stok_sebelum }}</td>
                                <td class="text-end fw-medium">{{ $r->stok_sesudah }}</td>
                                <td>
                                    <div class="fw-medium">
                                        {{ $r->alasan }}
                                        @if ($r->toko)
                                            <span class="badge bg-label-info">{{ $r->toko->nama }}</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $r->keterangan }}</small>
                                </td>
                                <td>{{ $r->user->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('script')
<script>
    // Kotak pilih barang yang bisa dicari
    $('#pilihBarang').select2({ width: '100%' });

    // Menampilkan stok barang yang dipilih di bawah kotak Barang,
    // dan peringatan di bawah Jumlah kalau pengurangan melebihi stok.
    function tampilkanStok() {
        var pilihan = $('#pilihBarang option:selected');

        $('#teksStok').text('');
        $('#teksPeringatan').text('');

        if (pilihan.val() == '') {
            return;
        }

        var stok = Number(pilihan.data('qty'));
        var qty = Number($('#qtyAdjust').val() || 0);
        var jenis = $('.pilihan-jenis:checked').val();

        $('#teksStok').text('Stok saat ini ' + stok + ' pcs');

        if (jenis == 'kurang' && qty > stok) {
            $('#teksPeringatan').text('Tidak bisa dikurangi ' + qty + ', stok hanya ' + stok + ' pcs.');
        }
    }

    // Kotak pilih toko hanya muncul untuk 2 alasan ini
    function tampilkanToko() {
        var alasan = $('#pilihAlasan').val();

        if (alasan == 'Pindah ke toko lain') {
            $('#labelToko').text('Toko Tujuan');
            $('#kotakToko').show();
            $('#pilihToko').prop('required', true);
        } else if (alasan == 'Terima dari toko lain') {
            $('#labelToko').text('Toko Asal');
            $('#kotakToko').show();
            $('#pilihToko').prop('required', true);
        } else {
            $('#kotakToko').hide();
            $('#pilihToko').prop('required', false).val('');
        }
    }
    $('#pilihAlasan').on('change', tampilkanToko);
    tampilkanToko();

    $('#pilihBarang').on('change', tampilkanStok);
    $('.pilihan-jenis').on('change', tampilkanStok);
    $('#qtyAdjust').on('input', tampilkanStok);
    tampilkanStok();
</script>
@endsection
