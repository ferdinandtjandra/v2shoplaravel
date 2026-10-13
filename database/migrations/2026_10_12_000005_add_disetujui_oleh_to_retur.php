<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nama orang yang menyetujui (meng-ACC) retur. Diketik bebas dan wajib diisi di form Input Retur.
        // Retur yang dibuat sebelum kolom ini ada tidak punya catatannya (kosong).
        Schema::table('retur', function (Blueprint $table) {
            $table->string('disetujui_oleh', 100)->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('retur', function (Blueprint $table) {
            $table->dropColumn('disetujui_oleh');
        });
    }
};
