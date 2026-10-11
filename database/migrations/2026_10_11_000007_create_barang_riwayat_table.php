<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat barang: siapa yang membuat, mengubah, dan menghapus barang, serta kapan.
        // Baris di tabel ini tidak pernah diedit atau dihapus.
        Schema::create('barang_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang');
            $table->foreignId('user_id')->nullable()->constrained('users');   // kosong = tidak diketahui (data lama)
            $table->string('aksi', 10);                                       // dibuat / diubah / dihapus
            $table->text('keterangan')->nullable();                           // apa saja yang berubah
            $table->timestamps();                                             // created_at = kapan terjadi
        });

        // Barang yang sudah ada sebelum fitur ini dibuat: riwayat "dibuat" dan "dihapus"-nya
        // diisi dari data yang tersimpan di tabel barang.
        DB::statement("
            INSERT INTO barang_riwayat (barang_id, user_id, aksi, keterangan, created_at, updated_at)
            SELECT id, created_by, 'dibuat', 'Barang masuk (data sebelum fitur riwayat ada)', tanggal_masuk, tanggal_masuk
            FROM barang
        ");

        DB::statement("
            INSERT INTO barang_riwayat (barang_id, user_id, aksi, keterangan, created_at, updated_at)
            SELECT id, deleted_by, 'dihapus', 'Barang dihapus (data sebelum fitur riwayat ada)', deleted_at, deleted_at
            FROM barang
            WHERE deleted_at IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_riwayat');
    }
};
