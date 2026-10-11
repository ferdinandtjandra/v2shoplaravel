@extends('layouts.app')

@section('judul', 'Input Penjualan')

@section('isi')
<div class="row">

    {{-- ================= KOLOM KIRI: pilih barang + keranjang ================= --}}
    <div class="col-lg-8">

        {{-- ---------- 1. TAMBAH BARANG ---------- --}}
        <div class="card mb-6">
            <h5 class="card-header">Tambah Barang</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('penjualan.tambah') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Barang</label>
                        {{-- Stok dan harga tiap barang dititipkan di atribut data-..., lalu dibaca oleh JavaScript di bawah --}}
                        <select name="barang_id" id="pilihBarang" class="form-select" required>
                            <option value="">- Cari kode atau nama barang -</option>
                            @foreach ($barang as $b)
<option value="{{ $b->id }}" data-qty="{{ $b->qty }}" data-member="{{ $b->harga_member }}" data-user="{{ $b->harga_user }}" data-ongkir="{{ $b->harga_ongkir }}" {{ old('barang_id') == $b->id ? 'selected' : '' }}>{{ $b->kode }} - {{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Harga Jual</label>
                        {{-- 3 pilihan harga dari database. Angkanya diisi oleh JavaScript setelah barang dipilih. --}}
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check pilihan-harga" name="harga_pilihan" id="hargaMember" value="" disabled>
                            <label class="btn btn-outline-primary" for="hargaMember">
                                <small class="d-block">Member</small>
                                <strong id="teksHargaMember">-</strong>
                            </label>

                            <input type="radio" class="btn-check pilihan-harga" name="harga_pilihan" id="hargaUser" value="" disabled>
                            <label class="btn btn-outline-primary" for="hargaUser">
                                <small class="d-block">User</small>
                                <strong id="teksHargaUser">-</strong>
                            </label>

                            <input type="radio" class="btn-check pilihan-harga" name="harga_pilihan" id="hargaOngkir" value="" disabled>
                            <label class="btn btn-outline-primary" for="hargaOngkir">
                                <small class="d-block">+ Ongkir</small>
                                <strong id="teksHargaOngkir">-</strong>
                            </label>
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-6 col-md-2 mb-4">
                            <label class="form-label">Stok</label>
                            <input type="text" id="qtyStock" class="form-control" value="-" readonly>
                        </div>
                        <div class="col-6 col-md-2 mb-4">
                            <label class="form-label">Qty Beli</label>
                            <input type="number" name="qty" id="qtyBeli" class="form-control" min="1" value="{{ old('qty', 1) }}" required>
                        </div>
                        @if (Auth::user()->isBos())
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Harga Custom</label>
                                <input type="text" name="harga_custom" id="hargaCustom" class="form-control rupiah" value="{{ old('harga_custom') }}" placeholder="opsional">
                            </div>
                        @endif
                        <div class="col-md mb-4 text-md-end">
                            <small class="text-muted d-block">Subtotal</small>
                            <span class="fs-4 fw-bold" id="teksSubtotal">Rp. 0</span>
                        </div>
                        <div class="col-md-auto mb-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-plus"></i> Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ---------- 2. KERANJANG ---------- --}}
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Keranjang</h5>
                <span class="badge bg-label-primary">{{ $keranjang->count() }} baris / {{ $keranjang->sum('qty') }} pcs</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barang</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($keranjang as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-medium">{{ $item->barang->nama }}</div>
                                    <small class="text-muted">{{ $item->barang->kode }}</small>
                                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end text-nowrap">Rp. {{ number_format($item->harga_satuan) }}</td>
                                <td class="text-end text-nowrap fw-medium">Rp. {{ number_format($item->subtotal) }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger tombol-hapus" title="Hapus"
                                        data-alamat="{{ route('penjualan.hapus_item', $item->id) }}"
                                        data-judul="Hapus {{ $item->barang->nama }}">
                                        <i class="icon-base bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-6">
                                    <i class="icon-base bx bx-cart icon-lg d-block mx-auto mb-2"></i>
                                    Keranjang masih kosong. Pilih barang di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= KOLOM KANAN: ringkasan + checkout ================= --}}
    <div class="col-lg-4">
        <div class="card mb-6 position-sticky" style="top: 90px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pembayaran</h5>
                <small class="text-muted">{{ now()->format('j M Y, H:i') }}</small>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Jumlah barang</span>
                    <span>{{ $keranjang->sum('qty') }} pcs</span>
                </div>
                <div class="d-flex justify-content-between align-items-center border-top pt-4 mb-6">
                    <span class="fw-medium">TOTAL</span>
                    <span class="fs-3 fw-bold text-primary">Rp. {{ number_format($total) }}</span>
                </div>

                <form method="POST" action="{{ route('penjualan.checkout') }}" id="formCheckout">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Sales *</label>
                        <select name="sales_id" class="form-select" required>
                            <option value="">- Pilih Sales -</option>
                            @foreach ($daftarSales as $s)
                                <option value="{{ $s->id }}" {{ old('sales_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="opsional">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- Tombol dimatikan kalau keranjang masih kosong --}}
                    <button type="submit" class="btn btn-success btn-lg w-100 mb-3" id="tombolCheckout" {{ $keranjang->count() == 0 ? 'disabled' : '' }}>
                        <i class="icon-base bx bx-check"></i> Checkout
                    </button>
                </form>

                <button type="button" class="btn btn-outline-danger w-100 tombol-hapus" {{ $keranjang->count() == 0 ? 'disabled' : '' }}
                    data-alamat="{{ route('penjualan.batal') }}"
                    data-judul="Batalkan transaksi ini">
                    <i class="icon-base bx bx-trash"></i> Batal Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================= KOTAK PASSWORD (muncul saat klik Hapus / Batal) ================= --}}
<div class="modal fade" id="modalPassword">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="formPassword" class="modal-content" autocomplete="off">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="judulModal">Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Masukkan Password Anda</label>
                <input type="password" name="password_hapus" id="inputPasswordHapus" class="form-control" autocomplete="new-password" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-danger">Hapus</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    // Kotak pilih barang yang bisa dicari
    $('#pilihBarang').select2({ width: '100%' });

    // Mengisi 1 tombol harga. Contoh: isiTombolHarga('#hargaMember', '#teksHargaMember', 15000)
    function isiTombolHarga(idRadio, idTeks, harga) {
        $(idRadio).val(harga).prop('disabled', false).prop('checked', false);
        $(idTeks).text('Rp. ' + formatRibuan(String(harga)));
    }

    // Saat barang dipilih: tampilkan stok dan isi 3 tombol harga
    function isiHarga() {
        var pilihan = $('#pilihBarang option:selected');

        if (pilihan.val() == '') {
            $('.pilihan-harga').val('').prop('disabled', true).prop('checked', false);
            $('#teksHargaMember, #teksHargaUser, #teksHargaOngkir').text('-');
            $('#qtyStock').val('-');
        } else {
            isiTombolHarga('#hargaMember', '#teksHargaMember', pilihan.data('member'));
            isiTombolHarga('#hargaUser', '#teksHargaUser', pilihan.data('user'));
            isiTombolHarga('#hargaOngkir', '#teksHargaOngkir', pilihan.data('ongkir'));
            $('#qtyStock').val(pilihan.data('qty'));
            $('#qtyBeli').attr('max', pilihan.data('qty'));
        }

        hitungSubtotal();
    }

    // Subtotal = qty x harga. Harga custom (kalau diisi) menang dari tombol harga.
    function hitungSubtotal() {
        var harga = Number($('.pilihan-harga:checked').val() || 0);
        var hargaCustom = Number(($('#hargaCustom').val() || '').replace(/[^0-9]/g, ''));

        if (hargaCustom > 0) {
            harga = hargaCustom;
        }

        var qty = Number($('#qtyBeli').val() || 0);
        $('#teksSubtotal').text('Rp. ' + formatRibuan(String(harga * qty)));
    }

    $('#pilihBarang').on('change', isiHarga);
    $('.pilihan-harga').on('change', hitungSubtotal);
    $('#qtyBeli, #hargaCustom').on('input', hitungSubtotal);
    isiHarga();

    // Tombol Hapus / Batal: tanya dulu, baru buka kotak password.
    // Setelah password diisi, form dikirim ke alamat milik tombol yang diklik.
    $('.tombol-hapus').on('click', function () {
        if (! confirm($(this).data('judul') + '?')) {
            return;
        }

        $('#formPassword').attr('action', $(this).data('alamat'));
        $('#judulModal').text($(this).data('judul'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPassword')).show();
    });

    // Kursor langsung ada di kotak password saat kotaknya terbuka
    $('#modalPassword').on('shown.bs.modal', function () {
        $('#inputPasswordHapus').val('').trigger('focus');
    });

    // Tombol Checkout dimatikan setelah diklik, supaya transaksi tidak tersimpan 2x
    $('#formCheckout').on('submit', function () {
        $('#tombolCheckout').prop('disabled', true);
    });
</script>
@endsection
