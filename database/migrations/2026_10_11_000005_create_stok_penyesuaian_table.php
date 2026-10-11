<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat menu "Adjust Stock": setiap penambahan / pengurangan stok di luar penjualan.
        // Baris di tabel ini tidak pernah diedit atau dihapus.
        Schema::create('stok_penyesuaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang');
            $table->foreignId('user_id')->constrained('users');     // siapa yang melakukan
            $table->string('jenis', 10);                            // tambah / kurang
            $table->integer('qty');                                 // jumlah yang ditambah / dikurangi
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->string('alasan', 50);
            $table->text('keterangan');
            $table->timestamps();                                   // created_at = kapan dilakukan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_penyesuaian');
    }
};
