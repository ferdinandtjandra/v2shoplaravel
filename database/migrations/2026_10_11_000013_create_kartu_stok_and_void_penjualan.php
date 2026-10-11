<?php

use App\Models\KartuStok;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- Kartu stok ----------
        // Catatan setiap perubahan stok barang (masuk, jual, batal jual, retur, adjust, edit).
        Schema::create('kartu_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('jenis', 20);                    // masuk / jual / batal_jual / retur / retur_kembali / adjust / edit
            $table->integer('perubahan')->nullable();       // +2 atau -2. Kosong = tidak tercatat (data lama)
            $table->integer('stok_sebelum')->nullable();    // kosong = tidak tercatat (data lama)
            $table->integer('stok_sesudah')->nullable();
            $table->string('keterangan', 600)->nullable();
            $table->timestamps();                           // created_at = kapan terjadi

            $table->index(['barang_id', 'created_at']);     // supaya cepat saat menampilkan kartu stok 1 barang
        });

        // ---------- Batal (void) penjualan ----------
        // Penjualan yang dibatalkan tidak dihapus. Hanya ditandai: kapan, oleh siapa, dan alasannya.
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dateTime('dibatalkan_at')->nullable()->after('kasir_id');
            $table->foreignId('dibatalkan_oleh')->nullable()->after('dibatalkan_at')->constrained('users');
            $table->string('alasan_batal', 500)->nullable()->after('dibatalkan_oleh');
        });

        // Isi kartu stok dari data yang sudah ada
        KartuStok::isiDariDataLama();
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dibatalkan_oleh');
            $table->dropColumn(['dibatalkan_at', 'alasan_batal']);
        });

        Schema::dropIfExists('kartu_stok');
    }
};
