<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengingat: tanggal + pesan yang dibuat Bos, muncul mencolok di dashboard Bos dan staff.
        Schema::create('pengingat', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('pesan', 500);
            $table->foreignId('user_id')->nullable()->constrained('users'); // siapa yang membuat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat');
    }
};
