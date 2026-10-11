<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salinan password yang bisa ditampilkan ke Bos di halaman User dan Password Delete.
        // Isinya terenkripsi (bukan teks biasa) dan hanya bisa dibuka oleh aplikasi ini memakai APP_KEY di file .env.
        // Kolom "password" yang lama tetap ada dan tetap dipakai untuk login / pengecekan.
        Schema::table('users', function (Blueprint $table) {
            $table->text('password_terlihat')->nullable()->after('password');
        });

        Schema::table('password_hapus', function (Blueprint $table) {
            $table->text('password_terlihat')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_terlihat');
        });

        Schema::table('password_hapus', function (Blueprint $table) {
            $table->dropColumn('password_terlihat');
        });
    }
};
