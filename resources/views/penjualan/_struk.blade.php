{{-- Bentuk struk (lebar 310px, untuk printer struk / POS).
     Dipakai di 2 tempat: halaman Struk Penjualan dan preview di menu Template Struk.
     Butuh 2 data: $template (header, catatan, penutup) dan $penjualan (tanggal, kode, detail, total). --}}
<style>
    .ticket { width: 310px; max-width: 310px; background: #fff; }
    .ticket, .ticket * { font-size: 12px; font-family: 'Times New Roman', serif; color: #000; line-height: 1.3; }
    .ticket table { border-collapse: collapse; width: 100%; }
    .ticket td, .ticket th, .ticket tr, .ticket table { border-top: 1px solid black; }
    .ticket td, .ticket th { padding: 1px 0; font-weight: normal; }
    .ticket th { font-weight: bold; }
    .ticket .description { width: 200px; max-width: 200px; text-align: left; }
    .ticket .quantity { width: 30px; max-width: 30px; text-align: center; }
    .ticket .price { width: 80px; max-width: 80px; text-align: right; }
    .ticket .centered { text-align: center; margin: 0 0 12px 0; }

    /* Kotak abu-abu di belakang struk, hanya hiasan di layar */
    .alas-struk { background: #f5f5f9; padding: 24px; display: flex; justify-content: center; border-radius: 8px; }
    .alas-struk .ticket { padding: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, .15); }
</style>

<div class="alas-struk">
    <div class="ticket">
        {{-- HEADER (dari Template Struk). nl2br: tiap baris yang diketik tampil di baris sendiri --}}
        <p class="centered">
            <span id="strukHeader">{!! nl2br(e($template->header)) !!}</span>
            <br>Tgl {{ $penjualan->tanggal->format('j-M-Y, H:i') }}
            <br>Kode. {{ $penjualan->kode_transaksi }}
        </p>

        <table>
            <thead>
                <tr><th class="description">Barang</th><th class="quantity">Qty</th><th class="price">Harga</th></tr>
            </thead>
            <tbody>
                @foreach ($penjualan->detail as $item)
                    <tr>
                        <td class="description">{{ $item->nama_barang }}</td>
                        <td class="quantity">{{ $item->qty }}</td>
                        <td class="price">{{ number_format($item->subtotal) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="description">TOTAL</td>
                    <td class="quantity"></td>
                    <td class="price">{{ number_format($penjualan->total) }}</td>
                </tr>
                {{-- FOOTER 1: catatan (dari Template Struk) --}}
                <tr>
                    <th colspan="3" style="text-align: left;" id="strukCatatan">{!! nl2br(e($template->catatan)) !!}</th>
                </tr>
            </tbody>
        </table>

        {{-- FOOTER 2: penutup (dari Template Struk) --}}
        <p class="centered" style="margin-top: 12px;" id="strukPenutup">{{ $template->penutup }}</p>

        {{-- Baris kosong supaya kertas keluar cukup panjang sebelum disobek --}}
        <br><br><br><br>
    </div>
</div>
