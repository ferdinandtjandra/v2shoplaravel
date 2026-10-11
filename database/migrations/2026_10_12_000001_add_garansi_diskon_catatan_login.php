<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- Garansi (per barang) ----------
        // Jumlah hari garansi. 0 = tanpa garansi. Dipilih kasir saat barang dimasukkan ke keranjang.
        Schema::table('keranjang', function (Blueprint $table) {
            $table->integer('garansi_hari')->default(0)->after('keuntungan');
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->integer('garansi_hari')->default(0)->after('keuntungan');
        });

        // ---------- Diskon (per transaksi) ----------
        // Potongan dalam rupiah. Kolom "total" tetap berarti uang yang benar-benar dibayar (sudah dipotong diskon).
        Schema::table('penjualan', function (Blueprint $table) {
            $table->integer('diskon')->default(0)->after('total');
        });

        // ---------- Catatan login ----------
        // Siapa login / logout / gagal login, kapan, dan dari perangkat apa.
        Schema::create('catatan_login', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');  // kosong kalau username-nya tidak dikenal
            $table->string('username', 100)->nullable();                     // username yang diketik
            $table->string('kejadian', 20);                                  // login / gagal / diblokir / logout / dikeluarkan
            $table->string('ip', 45)->nullable();
            $table->string('perangkat', 255)->nullable();                    // keterangan browser dari perangkat yang dipakai
            $table->timestamps();                                            // created_at = kapan terjadi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_login');

        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('diskon');
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->dropColumn('garansi_hari');
        });

        Schema::table('keranjang', function (Blueprint $table) {
            $table->dropColumn('garansi_hari');
        });
    }
};
