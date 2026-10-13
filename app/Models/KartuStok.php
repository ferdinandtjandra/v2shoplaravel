<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Kartu stok: catatan SETIAP perubahan stok suatu barang, dari mana pun asalnya
// (barang masuk, penjualan, batal penjualan, retur, penyesuaian stok, edit barang).
// Baris di tabel ini tidak pernah diedit atau dihapus.
class KartuStok extends Model
{
    protected $table = 'kartu_stok';

    protected $guarded = [];

    // Jenis perubahan stok => tulisan yang ditampilkan
    public const DAFTAR_JENIS = [
        'masuk' => 'Barang masuk',
        'jual' => 'Penjualan',
        'batal_jual' => 'Batal penjualan',
        'retur' => 'Retur ke supplier',
        'retur_kembali' => 'Retur kembali',
        'adjust' => 'Penyesuaian stok',
        'service' => 'Dipakai service',
        'batal_service' => 'Batal dipakai service',
        'edit' => 'Edit barang',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Menyimpan 1 baris kartu stok.
    // Contoh: KartuStok::catat($barang, 'jual', -2, 5, 3, 'Penjualan V2S-2026/10-10557');
    public static function catat(Barang $barang, $jenis, $perubahan, $stokSebelum, $stokSesudah, $keterangan)
    {
        KartuStok::create([
            'barang_id' => $barang->id,
            'user_id' => Auth::id(),
            'jenis' => $jenis,
            'perubahan' => $perubahan,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'keterangan' => $keterangan,
        ]);
    }

    // Mengisi kartu stok dari data yang sudah ada SEBELUM fitur kartu stok dibuat.
    // Dipanggil 1 kali oleh migration, dan oleh perintah import:lama.
    // Untuk data lama, stok sebelum / sesudah tidak diketahui, jadi dikosongkan (NULL).
    public static function isiDariDataLama()
    {
        // 1. Barang masuk (jumlah awalnya tidak tercatat di data lama)
        DB::statement("
            INSERT INTO kartu_stok (barang_id, user_id, jenis, perubahan, keterangan, created_at, updated_at)
            SELECT id, created_by, 'masuk', NULL, 'Barang masuk (jumlah awal tidak tercatat)', tanggal_masuk, tanggal_masuk
            FROM barang
        ");

        // 2. Penjualan
        DB::statement("
            INSERT INTO kartu_stok (barang_id, user_id, jenis, perubahan, keterangan, created_at, updated_at)
            SELECT d.barang_id, p.kasir_id, 'jual', -d.qty, CONCAT('Penjualan ', p.kode_transaksi), p.tanggal, p.tanggal
            FROM penjualan_detail d
            JOIN penjualan p ON p.id = d.penjualan_id
            WHERE d.barang_id IS NOT NULL
        ");

        // 3. Retur ke supplier
        DB::statement("
            INSERT INTO kartu_stok (barang_id, user_id, jenis, perubahan, keterangan, created_at, updated_at)
            SELECT barang_id, user_id, 'retur', -qty, CONCAT('Retur ', kode), tanggal_retur, tanggal_retur
            FROM retur
        ");

        // 4. Retur yang sudah kembali
        DB::statement("
            INSERT INTO kartu_stok (barang_id, user_id, jenis, perubahan, keterangan, created_at, updated_at)
            SELECT barang_id, user_id, 'retur_kembali', qty, CONCAT('Retur ', kode, ' kembali dari supplier'), tanggal_kembali, tanggal_kembali
            FROM retur
            WHERE sudah_kembali = 1 AND tanggal_kembali IS NOT NULL
        ");

        // 5. Penyesuaian stok (yang ini stok sebelum / sesudahnya tercatat)
        DB::statement("
            INSERT INTO kartu_stok (barang_id, user_id, jenis, perubahan, stok_sebelum, stok_sesudah, keterangan, created_at, updated_at)
            SELECT barang_id, user_id, 'adjust', IF(jenis = 'tambah', qty, -qty), stok_sebelum, stok_sesudah, CONCAT(alasan, ': ', keterangan), created_at, created_at
            FROM stok_penyesuaian
        ");
    }
}
