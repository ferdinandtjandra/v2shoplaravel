<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dulu: penjualan
        // Kolom lama yang tidak pernah terisi (kode_barang, nama_barang, no_imei, garansi, status, qty_barang) dibuang.
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 50)->nullable()->unique(); // contoh: V2S-2026/10-10557 (diisi otomatis)
            $table->dateTime('tanggal');
            $table->integer('total')->default(0);
            $table->integer('keuntungan')->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('sales_id')->nullable()->constrained('users');  // dulu: sales (teks nama)
            $table->foreignId('kasir_id')->nullable()->constrained('users');  // dulu: pic (teks nama)
            $table->timestamps();
        });

        // Dulu: penjualan_list
        // Sekarang terhubung ke penjualan dan barang lewat ID (dulu lewat teks kode).
        Schema::create('penjualan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('barang');
            $table->string('nama_barang', 150);         // nama barang saat dijual (tetap aman walau nama barang diubah)
            $table->integer('qty');
            $table->integer('harga_satuan');            // kolom baru: harga per 1 barang
            $table->integer('subtotal');                // qty x harga_satuan (dulu: harga_pembelian)
            $table->integer('keuntungan');
        });

        // Dulu: penjualan_list_temp
        // Keranjang belanja sebelum checkout. Sekarang tiap user punya keranjang sendiri
        // (dulu semua user memakai satu keranjang yang sama).
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('barang_id')->constrained('barang');
            $table->integer('qty');
            $table->integer('harga_satuan');
            $table->integer('subtotal');
            $table->integer('keuntungan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang');
        Schema::dropIfExists('penjualan_detail');
        Schema::dropIfExists('penjualan');
    }
};
