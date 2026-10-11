<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    // SoftDeletes: saat dihapus, data tidak hilang. Hanya kolom deleted_at yang diisi.
    use SoftDeletes;

    protected $table = 'barang';

    protected $guarded = [];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
    ];

    // Mengubah stok barang SEKALIGUS mencatatnya di kartu stok.
    // Semua perubahan stok (jual, batal jual, retur, adjust) lewat fungsi ini,
    // supaya tidak ada perubahan stok yang tidak tercatat.
    //
    // Contoh: $barang->ubahStok(-2, 'jual', 'Penjualan V2S-2026/10-10557');
    //         $barang->ubahStok(+2, 'retur_kembali', 'Retur RTN001 kembali dari supplier');
    public function ubahStok($perubahan, $jenis, $keterangan)
    {
        $stokSebelum = $this->qty;

        $this->qty = $stokSebelum + $perubahan;
        $this->save();

        KartuStok::catat($this, $jenis, $perubahan, $stokSebelum, $this->qty, $keterangan);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
