<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nota yang dihapus sekarang tidak benar-benar hilang dari database.
        // Hanya ditandai: kapan dihapus (deleted_at) dan siapa yang menghapus (deleted_by).
        Schema::table('nota_jatuh_tempo', function (Blueprint $table) {
            $table->foreignId('deleted_by')->nullable()->after('user_id')->constrained('users');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('nota_jatuh_tempo', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropSoftDeletes();
        });
    }
};
