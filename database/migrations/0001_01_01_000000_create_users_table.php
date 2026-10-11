<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel user (dulu: ms_user)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username', 100)->unique();
            $table->string('password');                 // disimpan dalam bentuk hash (bukan teks biasa lagi)
            $table->string('role', 20);                 // bos / karyawan / sales  (dulu: status 1 / 2 / 3)
            $table->boolean('aktif')->default(true);    // user nonaktif tidak bisa login
            $table->rememberToken();
            $table->timestamps();
        });

        // Tabel session bawaan Laravel (untuk menyimpan data login)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
