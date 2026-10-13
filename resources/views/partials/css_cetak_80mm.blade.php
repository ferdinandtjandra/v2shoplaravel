{{-- Aturan saat dicetak ke kertas 80mm: semua bagian admin disembunyikan, yang tercetak hanya struknya.
     Dipakai oleh halaman struk penjualan dan halaman cetak service. --}}
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
