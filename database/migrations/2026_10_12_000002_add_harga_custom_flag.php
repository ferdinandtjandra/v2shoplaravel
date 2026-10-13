<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penanda: apakah harga barang ini diketik sendiri oleh Bos (Harga Custom),
        // bukan dipilih dari 3 harga di database. Dipakai untuk label di Laporan Penjualan.
        // Transaksi sebelum kolom ini ada tidak punya catatannya, jadi dianggap bukan harga custom.
        Schema::table('keranjang', function (Blueprint $table) {
            $table->boolean('harga_custom')->default(false)->after('harga_satuan');
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->boolean('harga_custom')->default(false)->after('harga_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->dropColumn('harga_custom');
        });

        Schema::table('keranjang', function (Blueprint $table) {
            $table->dropColumn('harga_custom');
        });
    }
};
