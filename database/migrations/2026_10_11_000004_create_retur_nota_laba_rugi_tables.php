<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dulu: barang_retur
        Schema::create('retur', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->nullable()->unique();   // contoh: RTN001 (diisi otomatis)
            $table->foreignId('barang_id')->constrained('barang');
            $table->integer('qty');
            $table->text('keterangan');
            $table->dateTime('tanggal_retur');
            $table->boolean('sudah_kembali')->default(false);   // dulu: status_return
            $table->dateTime('tanggal_kembali')->nullable();    // dulu: date_return
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Dulu: nota_jatuh_tempo
        Schema::create('nota_jatuh_tempo', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota', 100);
            $table->date('tanggal_jatuh_tempo');
            $table->text('informasi')->nullable();
            $table->bigInteger('nominal')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users'); // dulu: pic (teks nama)
            $table->timestamps();
        });

        // Dulu: ms_laba_rugi
        Schema::create('laba_rugi', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->unique();             // format: 2026-10
            $table->bigInteger('total_penjualan')->default(0);
            $table->bigInteger('harga_pokok_penjualan')->default(0);
            $table->bigInteger('laba_kotor')->default(0);
            $table->bigInteger('gaji')->default(0);
            $table->bigInteger('listrik')->default(0);
            $table->bigInteger('operasional')->default(0);      // biaya tak terduga
            $table->bigInteger('total_biaya')->default(0);      // dulu: laba_operasional (isinya memang total biaya)
            $table->bigInteger('laba_bersih')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laba_rugi');
        Schema::dropIfExists('nota_jatuh_tempo');
        Schema::dropIfExists('retur');
    }
};
