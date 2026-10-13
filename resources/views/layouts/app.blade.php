<!DOCTYPE html>
<html lang="id" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('sneat') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul') - {{ config('toko.nama') }}</title>

    {{-- Template admin gratis: Sneat (Bootstrap 5). File-filenya ada di folder public/sneat --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/img/favicon/favicon.ico') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">

    {{-- Tambahan dari CDN: kotak pilih yang bisa dicari (Select2) dan tabel pintar (DataTables) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <style>
        .select2-container .select2-selection--single { height: 38px; padding-top: 4px; border-color: #ced1d5; }
        .select2-container .select2-selection--single .select2-selection__arrow { height: 36px; }

        /* Tabel laporan yang padat: huruf lebih kecil dan jarak antar baris lebih rapat,
           supaya lebih banyak data terlihat di satu layar. Pakai dengan class "tabel-padat". */
        .tabel-padat { font-size: 13px; }
        .tabel-padat th, .tabel-padat td { padding: .4rem .6rem !important; vertical-align: middle; }
        .tabel-padat thead th { font-size: 11px; white-space: nowrap; }
        .tabel-padat small { font-size: 11px; }
        .tabel-padat .btn-sm { padding: .15rem .4rem; }

        /* Grafik batang (dipakai di Dashboard dan Laba / Rugi). Dibuat dengan HTML + CSS biasa, tanpa library. */
        .grafik { display: flex; align-items: flex-end; gap: 2px; height: 180px; border-bottom: 1px solid #e4e6e8; }
        .grafik .kolom { flex: 1; height: 100%; display: flex; align-items: flex-end; cursor: default; border-radius: 4px 4px 0 0; }
        .grafik .kolom:hover { background: #f2f2f6; }
        .grafik .batang { width: 100%; background: #696cff; border-radius: 4px 4px 0 0; }
        .grafik .kolom:hover .batang { background: #5457e0; }
        .grafik-tanggal { display: flex; gap: 2px; font-size: 11px; color: #8592a3; margin-top: 4px; }
        .grafik-tanggal span { flex: 1; text-align: center; }
        .grafik-tanggal .hari-ini { color: #384551; font-weight: 700; }

        /* Tabel "datatable" disembunyikan dulu sampai DataTables selesai menyiapkannya (saat itu ia mendapat class "dataTable"),
           supaya tabel, kotak Search dan "Show entries" muncul bersamaan, tidak satu-satu. */
        table.datatable:not(.dataTable) { display: none; }
        .table-responsive:has(table.datatable:not(.dataTable))::before { content: 'Memuat data...'; display: block; padding: 1rem 0; color: #8592a3; }
    </style>

    <script>
        // Pengaman tombol Back setelah Logout.
        // Browser kadang menampilkan "foto" halaman lama dari memorinya (back/forward cache) tanpa bertanya ke server,
        // sehingga dashboard terlihat lagi padahal sudah logout. Kalau halaman ini muncul dari memori seperti itu,
        // halaman langsung disembunyikan dan dimuat ulang dari server. Server lalu mengecek login:
        // kalau sudah logout, otomatis pindah ke halaman login.
        window.addEventListener('pageshow', function (event) {
            var dariMemori = event.persisted;

            // Cara kedua untuk mendeteksi halaman yang dibuka lewat tombol Back / Forward
            var navigasi = performance.getEntriesByType ? performance.getEntriesByType('navigation')[0] : null;
            if (navigasi && navigasi.type == 'back_forward') {
                dariMemori = true;
            }

            if (dariMemori) {
                document.documentElement.style.visibility = 'hidden';
                window.location.reload();
            }
        });
    </script>

    <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('sneat/js/config.js') }}"></script>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        {{-- MENU KIRI --}}
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="{{ route('dashboard') }}" class="app-brand-link">
                    <span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('toko.nama') }}</span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                    <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
                </a>
            </div>

            <div class="menu-divider mt-0"></div>
            <div class="menu-inner-shadow"></div>

            {{-- ============================================================
                 MENU
                 - Menu yang punya anak (sub menu) bisa dibuka-tutup. Grup yang berisi halaman
                   yang sedang dibuka otomatis terbuka (class "active open").
                 - request()->routeIs('...') = true kalau halaman yang sedang dibuka cocok dengan nama route itu.
                 - Staff hanya punya 4 menu, jadi dibuat tanpa grup.
                 ============================================================ --}}
            <ul class="menu-inner py-1">
                <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-home"></i><div class="text-truncate">Dashboard</div></a>
                </li>

                @if (Auth::user()->isBos())

                    {{-- ---------- PENJUALAN ---------- --}}
                    <li class="menu-item {{ request()->routeIs('penjualan.*', 'laporan.penjualan*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-cart"></i><div class="text-truncate">Penjualan</div></a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('penjualan.*') ? 'active' : '' }}"><a href="{{ route('penjualan.index') }}" class="menu-link"><div class="text-truncate">Input Penjualan</div></a></li>
                            <li class="menu-item {{ request()->routeIs('laporan.penjualan*') ? 'active' : '' }}"><a href="{{ route('laporan.penjualan') }}" class="menu-link"><div class="text-truncate">Laporan Penjualan</div></a></li>
                        </ul>
                    </li>

                    {{-- ---------- SERVICE ---------- --}}
                    <li class="menu-item {{ request()->routeIs('service.*', 'laporan.service*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-wrench"></i><div class="text-truncate">Service</div></a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('service.*') && ! request()->routeIs('service.create') ? 'active' : '' }}"><a href="{{ route('service.index') }}" class="menu-link"><div class="text-truncate">Daftar Service</div></a></li>
                            <li class="menu-item {{ request()->routeIs('service.create') ? 'active' : '' }}"><a href="{{ route('service.create') }}" class="menu-link"><div class="text-truncate">Terima Service</div></a></li>
                            <li class="menu-item {{ request()->routeIs('laporan.service*') ? 'active' : '' }}"><a href="{{ route('laporan.service') }}" class="menu-link"><div class="text-truncate">Laporan Service</div></a></li>
                        </ul>
                    </li>

                    {{-- ---------- STOK ---------- --}}
                    <li class="menu-item {{ request()->routeIs('barang.*', 'adjust_stok.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-box"></i><div class="text-truncate">Stok</div></a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('barang.index', 'barang.edit') ? 'active' : '' }}"><a href="{{ route('barang.index') }}" class="menu-link"><div class="text-truncate">Laporan Stok</div></a></li>
                            <li class="menu-item {{ request()->routeIs('barang.create') ? 'active' : '' }}"><a href="{{ route('barang.create') }}" class="menu-link"><div class="text-truncate">Barang Masuk</div></a></li>
                            <li class="menu-item {{ request()->routeIs('adjust_stok.*') ? 'active' : '' }}"><a href="{{ route('adjust_stok.index') }}" class="menu-link"><div class="text-truncate">Penyesuaian Stok</div></a></li>
                        </ul>
                    </li>

                    {{-- ---------- RETUR ---------- --}}
                    <li class="menu-item {{ request()->routeIs('retur.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-undo"></i><div class="text-truncate">Retur</div></a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('retur.pilih', 'retur.create') ? 'active' : '' }}"><a href="{{ route('retur.pilih') }}" class="menu-link"><div class="text-truncate">Input Retur</div></a></li>
                            <li class="menu-item {{ request()->routeIs('retur.index') ? 'active' : '' }}"><a href="{{ route('retur.index') }}" class="menu-link"><div class="text-truncate">Laporan Retur</div></a></li>
                        </ul>
                    </li>

                    {{-- ---------- KEUANGAN ---------- --}}
                    <li class="menu-item {{ request()->routeIs('nota.*', 'laba_rugi.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-line-chart"></i><div class="text-truncate">Keuangan</div></a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('nota.*') ? 'active' : '' }}"><a href="{{ route('nota.index') }}" class="menu-link"><div class="text-truncate">Nota Jatuh Tempo</div></a></li>
                            <li class="menu-item {{ request()->routeIs('laba_rugi.*') ? 'active' : '' }}"><a href="{{ route('laba_rugi.index') }}" class="menu-link"><div class="text-truncate">Laba / Rugi</div></a></li>
                        </ul>
                    </li>

                    {{-- ---------- PENGINGAT ---------- --}}
                    <li class="menu-item {{ request()->routeIs('pengingat.*') ? 'active' : '' }}">
                        <a href="{{ route('pengingat.index') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-bell"></i><div class="text-truncate">Pengingat</div></a>
                    </li>

                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Pengaturan</span></li>

                    {{-- ---------- MASTER DATA ---------- --}}
                    <li class="menu-item {{ request()->routeIs('kategori.*', 'satuan.*', 'supplier.*', 'toko.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-purchase-tag"></i><div class="text-truncate">Master Data</div></a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('kategori.*') ? 'active' : '' }}"><a href="{{ route('kategori.index') }}" class="menu-link"><div class="text-truncate">Kategori</div></a></li>
                            <li class="menu-item {{ request()->routeIs('satuan.*') ? 'active' : '' }}"><a href="{{ route('satuan.index') }}" class="menu-link"><div class="text-truncate">Satuan</div></a></li>
                            <li class="menu-item {{ request()->routeIs('supplier.*') ? 'active' : '' }}"><a href="{{ route('supplier.index') }}" class="menu-link"><div class="text-truncate">Supplier</div></a></li>
                            <li class="menu-item {{ request()->routeIs('toko.*') ? 'active' : '' }}"><a href="{{ route('toko.index') }}" class="menu-link"><div class="text-truncate">Toko</div></a></li>
                        </ul>
                    </li>

                    {{-- ---------- USER & SISTEM ---------- --}}
                    <li class="menu-item {{ request()->routeIs('user.*', 'password_hapus.*', 'template_struk.*', 'catatan_login.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-cog"></i><div class="text-truncate">Pengguna &amp; Sistem</div></a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('user.*') ? 'active' : '' }}"><a href="{{ route('user.index') }}" class="menu-link"><div class="text-truncate">Pengguna</div></a></li>
                            <li class="menu-item {{ request()->routeIs('password_hapus.*') ? 'active' : '' }}"><a href="{{ route('password_hapus.index') }}" class="menu-link"><div class="text-truncate">Password Hapus</div></a></li>
                            <li class="menu-item {{ request()->routeIs('template_struk.*') ? 'active' : '' }}"><a href="{{ route('template_struk.edit') }}" class="menu-link"><div class="text-truncate">Template Struk</div></a></li>
                            <li class="menu-item {{ request()->routeIs('catatan_login.*') ? 'active' : '' }}"><a href="{{ route('catatan_login.index') }}" class="menu-link"><div class="text-truncate">Catatan Login</div></a></li>
                        </ul>
                    </li>

                @else

                    {{-- ---------- MENU STAFF (karyawan / sales) ---------- --}}
                    <li class="menu-item {{ request()->routeIs('penjualan.*') ? 'active' : '' }}">
                        <a href="{{ route('penjualan.index') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-cart"></i><div class="text-truncate">Penjualan</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('service.*') ? 'active' : '' }}">
                        <a href="{{ route('service.index') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-wrench"></i><div class="text-truncate">Service</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('barang.index') ? 'active' : '' }}">
                        <a href="{{ route('barang.index') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-box"></i><div class="text-truncate">Laporan Stok</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('adjust_stok.*') ? 'active' : '' }}">
                        <a href="{{ route('adjust_stok.index') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-transfer"></i><div class="text-truncate">Penyesuaian Stok</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('retur.pilih', 'retur.create') ? 'active' : '' }}">
                        <a href="{{ route('retur.pilih') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-undo"></i><div class="text-truncate">Input Retur</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('retur.index') ? 'active' : '' }}">
                        <a href="{{ route('retur.index') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-list-ul"></i><div class="text-truncate">Laporan Retur</div></a>
                    </li>

                @endif
            </ul>
        </aside>

        <div class="layout-page">

            {{-- BAGIAN ATAS --}}
            <nav class="layout-navbar container-fluid navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)"><i class="icon-base bx bx-menu icon-md"></i></a>
                </div>
                <div class="navbar-nav-right d-flex align-items-center justify-content-end w-100">
                    <span class="me-4"><i class="icon-base bx bx-user"></i> Halo, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-log-out"></i> Logout</button>
                    </form>
                </div>
            </nav>

            {{-- ISI HALAMAN --}}
            <div class="content-wrapper">
                <div class="container-fluid flex-grow-1 container-p-y">
                    <h4 class="mb-4">@yield('judul')</h4>

                    {{-- Pesan sukses / gagal dari controller: ->with('info', '...') atau ->with('error', '...') --}}
                    @if (session('info'))
                        <div class="alert alert-success">{{ session('info') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Pesan dari validasi form --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $pesan)
                                <div>{{ $pesan }}</div>
                            @endforeach
                        </div>
                    @endif

                    @yield('isi')
                </div>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    {{-- Lapisan gelap saat menu dibuka di layar HP --}}
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<script src="{{ asset('sneat/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('sneat/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('sneat/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('sneat/vendor/js/menu.js') }}"></script>
<script src="{{ asset('sneat/js/main.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Tabel dengan class "datatable" otomatis punya fitur cari, urut, dan halaman
    // Kalau file DataTables gagal dimuat (misalnya internet mati), tabel tetap ditampilkan apa adanya.
    if ($.fn.DataTable) {
        $('.datatable').DataTable({ pageLength: 25, order: [], autoWidth: false });
    } else {
        $('.datatable').addClass('dataTable');
    }

    // Input dengan class "rupiah" otomatis diberi koma ribuan saat diketik. Contoh: 1500000 -> 1,500,000
    function formatRibuan(teks) {
        var angka = teks.replace(/[^0-9]/g, '');
        return angka.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    $('.rupiah').each(function () { this.value = formatRibuan(this.value); });
    $(document).on('input', '.rupiah', function () { this.value = formatRibuan(this.value); });

    // Mengubah kotak pilih barang menjadi kotak PENCARIAN: daftar barang dicari ke server saat diketik,
    // jadi halaman tidak perlu memuat ribuan barang sekaligus. Dipakai di Penjualan dan Penyesuaian Stok.
    //   idKotak : contoh '#pilihBarang'
    //   alamat  : alamat pencarian di server (BarangController@cari)
    function aktifkanCariBarang(idKotak, alamat) {
        $(idKotak).select2({
            width: '100%',
            placeholder: 'Ketik kode atau nama barang',
            minimumInputLength: 2,
            ajax: {
                url: alamat,
                dataType: 'json',
                delay: 250, // tunggu 0,25 detik setelah berhenti mengetik, baru mencari
                data: function (yangDiketik) {
                    return { q: yangDiketik.term };
                },
                processResults: function (hasil) {
                    return { results: hasil };
                }
            },
            language: {
                inputTooShort: function () { return 'Ketik minimal 2 huruf'; },
                searching: function () { return 'Mencari...'; },
                noResults: function () { return 'Barang tidak ditemukan'; },
                errorLoading: function () { return 'Gagal memuat, coba lagi'; }
            }
        });
    }

    // Tombol / form dengan class "konfirmasi" akan bertanya dulu sebelum dijalankan
    $(document).on('submit', 'form.konfirmasi', function () {
        return confirm($(this).data('pesan') || 'Anda yakin?');
    });
</script>
@yield('script')
</body>
</html>
