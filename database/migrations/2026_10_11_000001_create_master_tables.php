<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dulu: ms_kategori
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20);
            $table->string('nama', 100);
            $table->timestamps();
        });

        // Dulu: ms_satuan
        Schema::create('satuan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20);
            $table->string('nama', 100);
            $table->timestamps();
        });

        // Dulu: ms_supplier
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('perusahaan', 150);
            $table->string('pic', 150);
            $table->text('alamat')->nullable();
            $table->string('telp', 20)->nullable();
            $table->string('hp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('bank', 100)->nullable();
            $table->string('rek', 50)->nullable();
            $table->string('atasnama', 150)->nullable();
            $table->timestamps();
        });

        // Dulu: ms_pass_delete
        // Password yang diminta saat menghapus item keranjang / membatalkan transaksi.
        Schema::create('password_hapus', function (Blueprint $table) {
            $table->id();
            $table->string('keterangan', 100)->nullable(); // catatan, supaya tahu ini password milik siapa
            $table->string('password');                    // disimpan dalam bentuk hash
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_hapus');
        Schema::dropIfExists('supplier');
        Schema::dropIfExists('satuan');
        Schema::dropIfExists('kategori');
    }
};
