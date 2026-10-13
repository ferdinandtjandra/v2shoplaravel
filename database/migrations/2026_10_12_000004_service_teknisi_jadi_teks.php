<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Teknisi yang mengerjakan sekarang diketik bebas (nama), bukan dipilih dari daftar pengguna.
        Schema::table('service', function (Blueprint $table) {
            $table->string('nama_teknisi', 100)->nullable()->after('diterima_oleh');
        });

        // Service yang sudah ada: nama teknisinya diisi dari pengguna yang dulu dipilih
        DB::statement('UPDATE service SET nama_teknisi = (SELECT name FROM users WHERE users.id = service.teknisi_id) WHERE teknisi_id IS NOT NULL');

        Schema::table('service', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teknisi_id');
        });
    }

    public function down(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->foreignId('teknisi_id')->nullable()->constrained('users');
            $table->dropColumn('nama_teknisi');
        });
    }
};
