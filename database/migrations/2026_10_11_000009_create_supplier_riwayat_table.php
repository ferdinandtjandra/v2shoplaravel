<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat supplier: siapa yang membuat dan mengubah data supplier, serta kapan.
        Schema::create('supplier_riwayat', function (Blueprint $table) {
            $table->id();
            // cascadeOnDelete: kalau suppliernya dihapus, riwayatnya ikut terhapus
            $table->foreignId('supplier_id')->constrained('supplier')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('aksi', 10);                 // dibuat / diubah
            $table->text('keterangan')->nullable();     // apa saja yang berubah
            $table->timestamps();                       // created_at = kapan terjadi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_riwayat');
    }
};
