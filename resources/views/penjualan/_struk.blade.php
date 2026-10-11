{{-- ====================================================================
     BENTUK STRUK untuk printer POS kertas 80mm (lebar cetak 72mm).
     Dipakai di 2 tempat: halaman Struk Penjualan dan preview di menu Template Struk.
     Butuh 2 data: $template (header, catatan, penutup) dan $penjualan (tanggal, kode, detail, diskon, total).

     Yang paling sering perlu disesuaikan setelah tes cetak:
       - lebar kertas  : "width: 72mm" di .ticket   (kalau sisi kanan terpotong, kecilkan, misalnya 70mm)
       - ukuran huruf  : "font-size: 9pt" di .ticket
       - jarak pinggir : "padding: 0 2mm" di .ticket
     ==================================================================== --}}
<style>
    .ticket { width: 72mm; max-width: 72mm; padding: 0 2mm; background: #fff; box-sizing: border-box; }
    .ticket, .ticket * { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; line-height: 1.35; color: #000; }

    /* Garis putus-putus pemisah antar bagian */
    .ticket .garis { border-top: 1px dashed #000; margin: 2mm 0; }

    /* Baris berisi 2 tulisan: satu di kiri, satu di kanan */
    .ticket .baris { display: flex; justify-content: space-between; gap: 3mm; }
    .ticket .baris .kanan { text-align: right; white-space: nowrap; }

    /* HEADER: rata tengah. Baris pertamanya (nama toko) dibuat besar dan tebal. */
    .ticket .header { text-align: center; }
    .ticket .header::first-line { font-size: 13pt; font-weight: 700; }

    /* DAFTAR BARANG */
    .ticket .barang { margin-bottom: 1.5mm; }
    .ticket .barang .nama { font-weight: 700; }
    .ticket .barang .rincian { padding-left: 3mm; }
    .ticket .barang .garansi { padding-left: 3mm; font-size: 8pt; }

    /* TOTAL */
    .ticket .total, .ticket .total * { font-size: 12pt; font-weight: 700; }

    /* FOOTER */
    .ticket .catatan, .ticket .catatan * { font-size: 8.5pt; line-height: 1.3; }
    .ticket .catatan div { margin-bottom: 1mm; }
    /* Poin (baris yang diawali tanda "-"): kalau kalimatnya panjang dan turun ke baris berikutnya,
       lanjutannya lurus dengan tulisan, bukan mundur ke bawah tanda "-". */
    .ticket .catatan .poin { padding-left: 2.5mm; text-indent: -2.5mm; }
    .ticket .penutup { text-align: center; font-weight: 700; margin-top: 2mm; }
    .ticket .penutup:empty { display: none; }

    /* Kotak abu-abu di belakang struk, hanya hiasan di layar */
    .alas-struk { background: #f5f5f9; padding: 24px; display: flex; justify-content: center; border-radius: 8px; }
    .alas-struk .ticket { padding: 4mm 3mm; box-shadow: 0 2px 8px rgba(0, 0, 0, .15); }
</style>

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
