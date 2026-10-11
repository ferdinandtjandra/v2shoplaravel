<?php

use App\Models\TemplateStruk;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setiap penjualan menyimpan sendiri header dan footer struk yang berlaku SAAT transaksi dibuat.
        // Jadi kalau Template Struk diubah di kemudian hari, struk transaksi lama tidak ikut berubah.
        Schema::table('penjualan', function (Blueprint $table) {
            $table->text('struk_header')->nullable()->after('keterangan');
            $table->text('struk_catatan')->nullable()->after('struk_header');
            $table->string('struk_penutup', 100)->nullable()->after('struk_catatan');
        });

        // Penjualan yang sudah ada diisi dengan template yang berlaku sekarang
        $template = TemplateStruk::ambil();

        DB::table('penjualan')->update([
            'struk_header' => $template->header,
            'struk_catatan' => $template->catatan,
            'struk_penutup' => $template->penutup,
        ]);
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['struk_header', 'struk_catatan', 'struk_penutup']);
        });
    }
};
