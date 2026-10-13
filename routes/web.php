<?php

use App\Http\Controllers\AdjustStokController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\CatatanLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LabaRugiController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\LaporanServiceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PasswordHapusController;
use App\Http\Controllers\PengingatController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TemplateStrukController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// LOGIN (bisa dibuka tanpa login)
// ---------------------------------------------------------------
// middleware('guest'): yang sudah login tidak perlu melihat halaman login lagi, langsung ke dashboard
Route::get('/login', [LoginController::class, 'form'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'masuk'])->name('login.masuk')->middleware('guest');
Route::post('/logout', [LoginController::class, 'keluar'])->name('logout');

// ---------------------------------------------------------------
// SEMUA USER YANG SUDAH LOGIN (bos, karyawan, sales)
// 'auth'  = harus sudah login
// 'aktif' = akunnya tidak sedang dinonaktifkan
// ---------------------------------------------------------------
Route::middleware(['auth', 'aktif'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Penjualan
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::post('/penjualan/tambah', [PenjualanController::class, 'tambah'])->name('penjualan.tambah');
    Route::post('/penjualan/hapus-item/{id}', [PenjualanController::class, 'hapusItem'])->name('penjualan.hapus_item');
    Route::post('/penjualan/batal', [PenjualanController::class, 'batal'])->name('penjualan.batal');
    Route::post('/penjualan/checkout', [PenjualanController::class, 'checkout'])->name('penjualan.checkout');
    Route::get('/penjualan/{id}/struk', [PenjualanController::class, 'struk'])->name('penjualan.struk');

    // Laporan Stok
    Route::get('/stok', [BarangController::class, 'index'])->name('barang.index');

    // Pencarian barang untuk kotak pilih di Penjualan dan Penyesuaian Stok (dipanggil JavaScript saat mengetik)
    Route::get('/barang/cari', [BarangController::class, 'cari'])->name('barang.cari');

    // Penyesuaian Stok (tambah / kurang stok dengan keterangan, misalnya pindah barang ke toko lain)
    Route::get('/adjust-stok', [AdjustStokController::class, 'index'])->name('adjust_stok.index');
    Route::post('/adjust-stok', [AdjustStokController::class, 'store'])->name('adjust_stok.store');

    // Service (perbaikan HP pelanggan). Semua level bisa memakainya; laporannya hanya Bos (ada di bawah).
    // Catatan: alamat yang tetap seperti /service/terima harus ditulis SEBELUM /service/{id}.
    Route::get('/service', [ServiceController::class, 'index'])->name('service.index');
    Route::get('/service/terima', [ServiceController::class, 'create'])->name('service.create');
    Route::post('/service', [ServiceController::class, 'store'])->name('service.store');
    Route::get('/service/{id}', [ServiceController::class, 'show'])->name('service.show');
    Route::get('/service/{id}/edit', [ServiceController::class, 'edit'])->name('service.edit');
    Route::put('/service/{id}', [ServiceController::class, 'update'])->name('service.update');
    Route::post('/service/{id}/sparepart-stok', [ServiceController::class, 'tambahStok'])->name('service.tambah_stok');
    Route::post('/service/{id}/sparepart-luar', [ServiceController::class, 'tambahLuar'])->name('service.tambah_luar');
    Route::post('/service/{id}/jasa', [ServiceController::class, 'tambahJasa'])->name('service.tambah_jasa');
    Route::post('/service/{id}/item/{itemId}/hapus', [ServiceController::class, 'hapusItem'])->name('service.hapus_item');
    Route::post('/service/{id}/selesai', [ServiceController::class, 'selesai'])->name('service.selesai');
    Route::post('/service/{id}/ambil', [ServiceController::class, 'ambil'])->name('service.ambil');
    Route::post('/service/{id}/batal', [ServiceController::class, 'batal'])->name('service.batal');
    Route::get('/service/{id}/tanda-terima', [ServiceController::class, 'tandaTerima'])->name('service.tanda_terima');
    Route::get('/service/{id}/nota', [ServiceController::class, 'nota'])->name('service.nota');

    // Retur. Semua level bisa memakainya: Input Retur, Laporan Retur, dan tombol "Barang Kembali".
    // Setiap retur mencatat siapa yang menginput dan siapa yang menyetujui ("Disetujui oleh").
    Route::get('/retur', [ReturController::class, 'index'])->name('retur.index');
    Route::get('/retur/pilih-barang', [ReturController::class, 'pilih'])->name('retur.pilih');
    Route::get('/retur/barang/{barangId}', [ReturController::class, 'create'])->name('retur.create');
    Route::post('/retur/barang/{barangId}', [ReturController::class, 'store'])->name('retur.store');
    Route::post('/retur/{id}/kembali', [ReturController::class, 'kembali'])->name('retur.kembali');

    // -----------------------------------------------------------
    // HANYA BOS
    // -----------------------------------------------------------
    Route::middleware('bos')->group(function () {

        // Master Kategori
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::get('/kategori/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        // Master Satuan
        Route::get('/satuan', [SatuanController::class, 'index'])->name('satuan.index');
        Route::post('/satuan', [SatuanController::class, 'store'])->name('satuan.store');
        Route::get('/satuan/{id}/edit', [SatuanController::class, 'edit'])->name('satuan.edit');
        Route::put('/satuan/{id}', [SatuanController::class, 'update'])->name('satuan.update');
        Route::delete('/satuan/{id}', [SatuanController::class, 'destroy'])->name('satuan.destroy');

        // Master Supplier
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
        Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
        Route::get('/supplier/{id}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
        Route::put('/supplier/{id}', [SupplierController::class, 'update'])->name('supplier.update');
        Route::delete('/supplier/{id}', [SupplierController::class, 'destroy'])->name('supplier.destroy');

        // Master Toko
        Route::get('/toko', [TokoController::class, 'index'])->name('toko.index');
        Route::post('/toko', [TokoController::class, 'store'])->name('toko.store');
        Route::get('/toko/{id}/edit', [TokoController::class, 'edit'])->name('toko.edit');
        Route::put('/toko/{id}', [TokoController::class, 'update'])->name('toko.update');
        Route::delete('/toko/{id}', [TokoController::class, 'destroy'])->name('toko.destroy');

        // Template Struk (header dan footer struk penjualan)
        Route::get('/template-struk', [TemplateStrukController::class, 'edit'])->name('template_struk.edit');
        Route::put('/template-struk', [TemplateStrukController::class, 'update'])->name('template_struk.update');

        // Master Pengguna
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::post('/user/{id}/ubah-aktif', [UserController::class, 'ubahAktif'])->name('user.ubah_aktif');

        // Master Password Hapus
        Route::get('/password-hapus', [PasswordHapusController::class, 'index'])->name('password_hapus.index');
        Route::post('/password-hapus', [PasswordHapusController::class, 'store'])->name('password_hapus.store');
        Route::delete('/password-hapus/{id}', [PasswordHapusController::class, 'destroy'])->name('password_hapus.destroy');

        // Batal (void) transaksi penjualan
        Route::post('/penjualan/{id}/batalkan', [PenjualanController::class, 'batalkan'])->name('penjualan.batalkan');

        // Export Excel
        Route::get('/stok/export', [BarangController::class, 'export'])->name('barang.export');
        Route::get('/laporan/penjualan/export', [LaporanPenjualanController::class, 'export'])->name('laporan.penjualan.export');

        // Barang Masuk + edit / hapus stok
        Route::get('/barang/masuk', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/barang/masuk', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{id}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');
        Route::post('/barang/{id}/kembalikan', [BarangController::class, 'kembalikan'])->name('barang.kembalikan');


        // Pengingat (tanggal + pesan yang muncul di dashboard Bos dan staff)
        Route::get('/pengingat', [PengingatController::class, 'index'])->name('pengingat.index');
        Route::post('/pengingat', [PengingatController::class, 'store'])->name('pengingat.store');
        Route::delete('/pengingat/{id}', [PengingatController::class, 'destroy'])->name('pengingat.destroy');

        // Nota Jatuh Tempo
        Route::get('/nota', [NotaController::class, 'index'])->name('nota.index');
        Route::post('/nota', [NotaController::class, 'store'])->name('nota.store');
        Route::delete('/nota/{id}', [NotaController::class, 'destroy'])->name('nota.destroy');
        Route::post('/nota/{id}/kembalikan', [NotaController::class, 'kembalikan'])->name('nota.kembalikan');

        // Laba / Rugi
        Route::get('/laba-rugi', [LabaRugiController::class, 'index'])->name('laba_rugi.index');
        Route::post('/laba-rugi', [LabaRugiController::class, 'store'])->name('laba_rugi.store');
        Route::get('/laba-rugi/cetak', [LabaRugiController::class, 'cetak'])->name('laba_rugi.cetak');
        Route::get('/laba-rugi/export', [LabaRugiController::class, 'export'])->name('laba_rugi.export');

        // Catatan login
        Route::get('/catatan-login', [CatatanLoginController::class, 'index'])->name('catatan_login.index');

        // Laporan Service
        Route::get('/laporan/service', [LaporanServiceController::class, 'index'])->name('laporan.service');
        Route::get('/laporan/service/export', [LaporanServiceController::class, 'export'])->name('laporan.service.export');

        // Laporan Penjualan
        Route::get('/laporan/penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan.penjualan');
    });
});
