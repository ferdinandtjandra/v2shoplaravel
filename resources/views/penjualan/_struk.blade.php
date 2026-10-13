{{-- ====================================================================
     BENTUK STRUK untuk printer POS kertas 80mm (lebar cetak 72mm).
     Dipakai di 2 tempat: halaman Struk Penjualan dan preview di menu Template Struk.
     Butuh 2 data: $template (header, catatan, penutup) dan $penjualan (tanggal, kode, detail, diskon, total).

     Yang paling sering perlu disesuaikan setelah tes cetak:
       - lebar kertas  : "width: 72mm" di .ticket   (kalau sisi kanan terpotong, kecilkan, misalnya 70mm)
       - ukuran huruf  : "font-size: 9pt" di .ticket
       - jarak pinggir : "padding: 0 2mm" di .ticket
     ==================================================================== --}}
@include('partials.css_struk')

<div class="alas-struk">
    <div class="ticket">

        {{-- ---------- HEADER (dari Template Struk). nl2br: tiap baris yang diketik tampil di baris sendiri ---------- --}}
        <div class="header" id="strukHeader">{!! nl2br(e($template->header)) !!}</div>

        <div class="garis"></div>

        {{-- ---------- INFO TRANSAKSI ---------- --}}
        <div class="baris"><span>No</span><span class="kanan">{{ $penjualan->kode_transaksi }}</span></div>
        <div class="baris"><span>Tanggal</span><span class="kanan">{{ $penjualan->tanggal->translatedFormat('j M Y, H:i') }}</span></div>

        <div class="garis"></div>

        {{-- ---------- DAFTAR BARANG: nama di baris pertama, "qty x harga" dan subtotal di baris kedua ---------- --}}
        @foreach ($penjualan->detail as $item)
            <div class="barang">
                <div class="nama">{{ $item->nama_barang }}</div>
                <div class="baris rincian">
                    <span>{{ $item->qty }} x {{ number_format($item->harga_satuan) }}</span>
                    <span class="kanan">{{ number_format($item->subtotal) }}</span>
                </div>
                {{-- Garansi: jumlah hari + sampai tanggal berapa (tanggal transaksi + jumlah hari) --}}
                @if (($item->garansi_hari ?? 0) > 0)
                    <div class="garansi">Garansi {{ $item->garansi_hari }} hari s/d {{ $penjualan->tanggal->copy()->addDays($item->garansi_hari)->translatedFormat('j M Y') }}</div>
                @endif
            </div>
        @endforeach

        <div class="garis"></div>

        {{-- ---------- SUBTOTAL, DISKON, TOTAL ---------- --}}
        {{-- Subtotal dan Diskon hanya tampil kalau transaksinya memakai diskon --}}
        @if (($penjualan->diskon ?? 0) > 0)
            <div class="baris"><span>Subtotal</span><span class="kanan">{{ number_format($penjualan->total + $penjualan->diskon) }}</span></div>
            <div class="baris"><span>Diskon</span><span class="kanan">-{{ number_format($penjualan->diskon) }}</span></div>
        @endif
        <div class="baris total"><span>TOTAL</span><span class="kanan">{{ number_format($penjualan->total) }}</span></div>

        <div class="garis"></div>

        {{-- ---------- FOOTER (dari Template Struk): catatan, lalu kalimat penutup ---------- --}}
        {{-- Tiap baris catatan menjadi 1 blok. Baris yang diawali "-" dianggap poin. Baris kosong dilewati. --}}
        <div class="catatan" id="strukCatatan">
            @foreach (preg_split('/\r\n|\r|\n/', (string) $template->catatan) as $barisCatatan)
                @if (trim($barisCatatan) != '')
                    <div class="{{ str_starts_with(trim($barisCatatan), '-') ? 'poin' : '' }}">{{ trim($barisCatatan) }}</div>
                @endif
            @endforeach
        </div>
        <div class="penutup" id="strukPenutup">{{ $template->penutup }}</div>

        {{-- Baris kosong supaya kertas keluar cukup panjang sebelum disobek --}}
        <br><br><br>
    </div>
</div>
