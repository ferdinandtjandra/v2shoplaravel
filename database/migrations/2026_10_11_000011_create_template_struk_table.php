<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template struk: tulisan bagian atas (header) dan bawah (footer) struk penjualan.
        // Tabel ini hanya berisi 1 baris, yang diedit Bos lewat menu "Template Struk".
        Schema::create('template_struk', function (Blueprint $table) {
            $table->id();
            $table->text('header');                     // nama toko, alamat, no HP (boleh beberapa baris)
            $table->text('catatan')->nullable();        // footer: catatan di bawah daftar barang
            $table->string('penutup', 100)->nullable(); // footer: baris paling bawah, contoh "--TERIMA KASIH--"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_struk');
    }
};
