{{-- CSS bentuk struk 80mm. Dipakai oleh struk penjualan, tanda terima service, dan nota service. --}}
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

    /* Judul dokumen (dipakai tanda terima dan nota service) */
    .ticket .judul-dokumen, .ticket .judul-dokumen * { text-align: center; font-weight: 700; font-size: 10.5pt; letter-spacing: .5px; }
    /* Tulisan kecil di atas sebuah isian, contoh "Keluhan" */
    .ticket .label-kecil { font-size: 8pt; font-weight: 700; margin-top: 1mm; }
    .ticket .sub-judul { font-weight: 700; text-decoration: underline; margin: 1mm 0; }
</style>
