<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master Toko: daftar nama toko (V2 Shop, V2 Com, V88, ...).
        // Dipakai di Adjust Stock untuk mencatat barang pindah ke / diterima dari toko mana.
        Schema::create('toko', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->timestamps();
        });

        // Kolom baru di riwayat Adjust Stock: toko tujuan / toko asal.
        // Hanya terisi kalau alasannya "Pindah ke toko lain" atau "Terima dari toko lain".
        Schema::table('stok_penyesuaian', function (Blueprint $table) {
            $table->foreignId('toko_id')->nullable()->after('alasan')->constrained('toko');
        });
    }

    public function down(): void
    {
        Schema::table('stok_penyesuaian', function (Blueprint $table) {
            $table->dropConstrainedForeignId('toko_id');
        });

        Schema::dropIfExists('toko');
    }
};
