@extends('layouts.app')

{{-- Halaman ini dipakai untuk 2 hal: Barang Masuk (tambah baru) dan Edit Barang. --}}
{{-- $barang->id kosong = tambah baru. $barang->id ada isinya = edit. --}}
@section('judul', $barang->id ? 'Edit Barang' : 'Input Barang Masuk')

@section('isi')

{{-- Barang yang sudah dihapus: form tetap tampil untuk dilihat, tapi tidak bisa disimpan --}}
@if ($barang->trashed())
    <div class="alert alert-danger">
        Barang ini sudah <strong>dihapus</strong> pada {{ $barang->deleted_at->format('j M Y, H:i') }}. Datanya hanya bisa dilihat, tidak bisa diubah.
        <form method="POST" action="{{ route('barang.kembalikan', $barang->id) }}" class="d-inline konfirmasi" data-pesan="Kembalikan barang ini ke daftar barang?">
            @csrf
            <button type="submit" class="btn btn-sm btn-success ms-2"><i class="icon-base bx bx-undo me-1"></i> Kembalikan Barang</button>
        </form>
    </div>
@endif

<form method="POST" action="{{ $barang->id ? route('barang.update', $barang->id) : route('barang.store') }}">
    @csrf
    @if ($barang->id)
        @method('PUT')
    @endif

    <div class="row">

        {{-- KIRI: informasi barang --}}
        <div class="col-lg-6">
            <div class="card mb-6">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Barang</h5>
                    <span class="badge bg-label-primary">{{ $barang->kode ?? 'Kode dibuat otomatis' }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $barang->nama) }}" placeholder="Contoh: Lcd Samsung A51 C" required autofocus>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Kategori</label>
                            <select name="kategori_id" class="form-select">
                                <option value="">- Pilih Kategori -</option>
                                @foreach ($kategori as $k)
                                    <option value="{{ $k->id }}" {{ old('kategori_id', $barang->kategori_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan_id" class="form-select" required>
                                <option value="">- Pilih Satuan -</option>
                                @foreach ($satuan as $s)
                                    {{-- Kalau satuan hanya ada 1, langsung terpilih --}}
                                    <option value="{{ $s->id }}" {{ old('satuan_id', $barang->satuan_id) == $s->id || $satuan->count() == 1 ? 'selected' : '' }}>{{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">- Pilih Supplier -</option>
                            @foreach ($supplier as $s)
                                <option value="{{ $s->id }}" {{ old('supplier_id', $barang->supplier_id) == $s->id ? 'selected' : '' }}>{{ $s->perusahaan }} - {{ $s->pic }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="qty" class="form-control rupiah" value="{{ old('qty', $barang->qty) }}" inputmode="numeric" required>
                            <span class="input-group-text">pcs</span>
                        </div>
                        @if ($barang->id)
                            <div class="form-text">Mengubah angka ini langsung mengubah stok barang.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: harga --}}
        <div class="col-lg-6">
            <div class="card mb-6">
                <h5 class="card-header">Harga</h5>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Harga Distributor (modal) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_distributor" id="hargaDistributor" class="form-control rupiah" value="{{ old('harga_distributor', $barang->harga_distributor) }}" inputmode="numeric" required>
                        </div>
                        <div class="form-text">Harga jual tidak boleh lebih kecil dari harga ini.</div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <label class="form-label">Harga Jual Member <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_member" class="form-control rupiah harga-jual" value="{{ old('harga_member', $barang->harga_member) }}" inputmode="numeric" required>
                        </div>
                        <div class="form-text teks-untung"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Harga Jual User <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_user" class="form-control rupiah harga-jual" value="{{ old('harga_user', $barang->harga_user) }}" inputmode="numeric" required>
                        </div>
                        <div class="form-text teks-untung"></div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Harga Jual + Ongkir <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_ongkir" class="form-control rupiah harga-jual" value="{{ old('harga_ongkir', $barang->harga_ongkir) }}" inputmode="numeric" required>
                        </div>
                        <div class="form-text teks-untung"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-6">
        @if ($barang->trashed())
            <a href="{{ route('barang.index', ['tampil' => 'dihapus']) }}" class="btn btn-outline-secondary">Kembali</a>
        @elseif ($barang->id)
            <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary">Kembali ke Laporan Stok</a>
            <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Perubahan</button>
        @else
            <button type="reset" class="btn btn-outline-secondary">Kosongkan</button>
            <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Barang</button>
        @endif
    </div>
</form>

{{-- ================= KARTU STOK (hanya di halaman edit) =================
     Semua perubahan stok barang ini: barang masuk, penjualan, batal penjualan, retur, penyesuaian stok, edit. --}}
@if ($barang->id)
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Kartu Stok</h5>
            <span class="badge bg-label-primary">Stok sekarang: {{ $barang->qty }} pcs</span>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover tabel-padat datatable">
                <thead>
                    <tr><th>Waktu</th><th>Jenis</th><th class="text-end">Masuk</th><th class="text-end">Keluar</th><th class="text-end">Stok Awal</th><th class="text-end">Stok Akhir</th><th>Keterangan</th><th>Oleh</th></tr>
                </thead>
                <tbody>
                    @foreach ($kartuStok as $k)
                        <tr>
                            <td class="text-nowrap" data-order="{{ $k->created_at->format('Y-m-d H:i:s') }}">
                                {{ $k->created_at->format('j M Y') }} <small class="text-muted">{{ $k->created_at->format('H:i') }}</small>
                            </td>
                            <td class="text-nowrap">{{ \App\Models\KartuStok::DAFTAR_JENIS[$k->jenis] ?? $k->jenis }}</td>
                            {{-- Masuk = stok bertambah, Keluar = stok berkurang. Data lama yang jumlahnya tidak tercatat ditampilkan "-" --}}
                            <td class="text-end">
                                @if ($k->perubahan > 0) <span class="badge bg-label-success">+{{ $k->perubahan }}</span> @endif
                            </td>
                            <td class="text-end">
                                @if ($k->perubahan < 0) <span class="badge bg-label-danger">{{ $k->perubahan }}</span> @endif
                            </td>
                            <td class="text-end">{{ $k->stok_sebelum ?? '-' }}</td>
                            <td class="text-end fw-medium">{{ $k->stok_sesudah ?? '-' }}</td>
                            <td>{{ $k->keterangan }}</td>
                            <td>{{ $k->user->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <small class="text-muted">Stok Awal dan Stok Akhir bertanda "-" berasal dari data lama, sebelum kartu stok dibuat.</small>
        </div>
    </div>
@endif

{{-- ================= RIWAYAT BARANG (hanya di halaman edit) ================= --}}
@if ($barang->id)
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Barang</h5>
            <small class="text-muted">Siapa yang membuat, mengubah, menghapus, dan mengembalikan</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Waktu</th><th>Aksi</th><th>Keterangan</th><th>Oleh</th></tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $r)
                        <tr>
                            <td class="text-nowrap">
                                <div>{{ $r->created_at->format('j M Y') }}</div>
                                <small class="text-muted">{{ $r->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @if ($r->aksi == 'dibuat')
                                    <span class="badge bg-label-success">Dibuat</span>
                                @elseif ($r->aksi == 'dihapus')
                                    <span class="badge bg-label-danger">Dihapus</span>
                                @elseif ($r->aksi == 'dipulihkan')
                                    <span class="badge bg-label-info">Dikembalikan</span>
                                @else
                                    <span class="badge bg-label-warning">Diubah</span>
                                @endif
                            </td>
                            {{-- nl2br: tiap perubahan tampil di baris sendiri --}}
                            <td>{!! nl2br(e($r->keterangan)) !!}</td>
                            <td>{{ $r->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-6">Belum ada riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@section('script')
<script>
    // Menampilkan keuntungan per pcs di bawah tiap harga jual (harga jual - harga distributor)
    function tampilkanUntung() {
        var modal = Number($('#hargaDistributor').val().replace(/[^0-9]/g, ''));

        $('.harga-jual').each(function () {
            var jual = Number(this.value.replace(/[^0-9]/g, ''));
            var kotakTeks = $(this).closest('.input-group').next('.teks-untung');

            if (this.value == '' || modal == 0) {
                kotakTeks.text('').removeClass('text-danger');
            } else if (jual < modal) {
                kotakTeks.text('Di bawah harga distributor!').addClass('text-danger');
            } else {
                kotakTeks.text('Untung Rp ' + formatRibuan(String(jual - modal)) + ' per pcs').removeClass('text-danger');
            }
        });
    }

    $('#hargaDistributor, .harga-jual').on('input', tampilkanUntung);
    tampilkanUntung();
</script>
@endsection
