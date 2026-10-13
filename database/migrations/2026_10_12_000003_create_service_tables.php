<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- Service (perbaikan HP pelanggan) ----------
        // 1 baris = 1 HP yang diservice. Nomornya dipakai di surat tanda terima DAN di nota service.
        // Alur status: diterima -> dikerjakan -> selesai -> diambil.  Bisa juga: batal.
        Schema::create('service', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->nullable()->unique();      // contoh: SRV-2026/10-00001 (diisi otomatis)
            $table->string('status', 20)->default('diterima');     // diterima / dikerjakan / selesai / diambil / batal

            // Pelanggan dan HP-nya
            $table->string('nama_pelanggan', 100);
            $table->string('hp_pelanggan', 30);
            $table->string('merk_tipe', 150);                      // contoh: Samsung A51
            $table->string('imei', 50)->nullable();
            $table->text('keluhan');                               // kata pelanggan
            $table->text('kerusakan')->nullable();                 // hasil pengecekan teknisi
            $table->string('kelengkapan', 255)->nullable();        // yang ikut ditinggal: kartu SIM, memori, casing
            $table->string('kondisi_fisik', 255)->nullable();      // lecet, layar retak, dll
            $table->integer('perkiraan_biaya')->default(0);
            $table->date('perkiraan_selesai')->nullable();
            $table->text('catatan')->nullable();

            // Siapa dan kapan
            $table->foreignId('diterima_oleh')->nullable()->constrained('users');
            $table->foreignId('teknisi_id')->nullable()->constrained('users');   // yang mengerjakan
            $table->dateTime('tanggal_terima');
            $table->dateTime('tanggal_selesai')->nullable();
            $table->dateTime('tanggal_diambil')->nullable();       // saat ini juga dibayar
            $table->foreignId('diserahkan_oleh')->nullable()->constrained('users');

            // Angka uang (dihitung ulang setiap sparepart / jasa berubah)
            $table->integer('total_sparepart')->default(0);
            $table->integer('total_jasa')->default(0);
            $table->integer('diskon')->default(0);
            $table->integer('total')->default(0);                  // sparepart + jasa - diskon = yang dibayar pelanggan
            $table->integer('modal')->default(0);                  // modal semua sparepart
            $table->integer('keuntungan')->default(0);             // total - modal
            $table->integer('garansi_hari')->default(0);           // 1 garansi untuk 1 nota. 0 = tanpa garansi

            // Pembatalan
            $table->dateTime('batal_at')->nullable();
            $table->foreignId('batal_oleh')->nullable()->constrained('users');
            $table->string('alasan_batal', 500)->nullable();

            // Tulisan header dan catatan yang berlaku saat service dibuat (supaya tidak berubah kalau template diedit)
            $table->text('struk_header')->nullable();
            $table->text('struk_catatan_tt')->nullable();          // catatan di surat tanda terima
            $table->text('struk_catatan_nota')->nullable();        // catatan di nota service
            $table->string('struk_penutup', 100)->nullable();

            $table->timestamps();
        });

        // ---------- Isi nota service: sparepart dan jasa ----------
        Schema::create('service_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service')->cascadeOnDelete();
            $table->string('jenis', 10);                           // stok = sparepart dari stok toko / luar = sparepart dari luar / jasa
            $table->foreignId('barang_id')->nullable()->constrained('barang');  // hanya untuk jenis "stok"
            $table->string('nama', 150);
            $table->integer('qty')->default(1);
            $table->integer('harga_satuan');                       // harga ke pelanggan
            $table->integer('subtotal');                           // qty x harga_satuan
            $table->integer('modal_satuan')->default(0);           // stok: harga distributor. luar: harga beli. jasa: 0
            $table->foreignId('user_id')->nullable()->constrained('users');     // siapa yang menginput
            $table->timestamps();
        });

        // ---------- Template: catatan untuk tanda terima dan nota service ----------
        Schema::table('template_struk', function (Blueprint $table) {
            $table->text('catatan_tanda_terima')->nullable()->after('catatan');
            $table->text('catatan_nota_service')->nullable()->after('catatan_tanda_terima');
        });

        // Isi awal (bisa diganti Bos di menu Template Struk)
        DB::table('template_struk')->update([
            'catatan_tanda_terima' => "- Tanda terima ini wajib dibawa saat mengambil barang.\n- Barang yang tidak diambil dalam 30 hari di luar tanggung jawab kami.\n- Data di dalam HP bukan tanggung jawab kami.",
            'catatan_nota_service' => "- Garansi hanya berlaku untuk kerusakan yang sama.\n- Garansi tidak berlaku jika terkena air, jatuh, atau dibongkar pihak lain.",
        ]);
    }

    public function down(): void
    {
        Schema::table('template_struk', function (Blueprint $table) {
            $table->dropColumn(['catatan_tanda_terima', 'catatan_nota_service']);
        });

        Schema::dropIfExists('service_item');
        Schema::dropIfExists('service');
    }
};
