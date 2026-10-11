<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dulu: barang_masuk
        //
        // Perubahan dari tabel lama:
        // - kategori / satuan / supplier sekarang disimpan sebagai ID (dulu teks nama).
        // - kode barang sekarang unik (dulu bisa dobel).
        // - kolom stock_flg dihapus. Stok habis cukup dilihat dari qty = 0.
        // - kolom "user" (teks nama) diganti created_by (ID user).
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->nullable()->unique();   // contoh: STCK5374 (diisi otomatis setelah disimpan)
            $table->string('nama', 150);
            $table->foreignId('kategori_id')->nullable()->constrained('kategori');
            $table->foreignId('satuan_id')->nullable()->constrained('satuan');
            $table->foreignId('supplier_id')->nullable()->constrained('supplier');
            $table->integer('qty')->default(0);
            $table->integer('harga_distributor')->default(0);   // harga modal
            $table->integer('harga_member')->default(0);        // dulu: harga_jual_min
            $table->integer('harga_user')->default(0);          // dulu: harga_jual_max
            $table->integer('harga_ongkir')->default(0);        // dulu: harga_jual_ongkir
            $table->dateTime('tanggal_masuk');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();                              // kolom deleted_at (barang dihapus tidak benar-benar hilang)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
