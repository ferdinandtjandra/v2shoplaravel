<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'service';

    protected $guarded = [];

    protected $casts = [
        'tanggal_terima' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'tanggal_diambil' => 'datetime',
        'perkiraan_selesai' => 'date',
        'batal_at' => 'datetime',
    ];

    // Status => tulisan yang ditampilkan
    public const DAFTAR_STATUS = [
        'diterima' => 'Diterima',
        'dikerjakan' => 'Dikerjakan',
        'selesai' => 'Selesai, belum diambil',
        'diambil' => 'Sudah diambil',
        'batal' => 'Batal',
    ];

    // Sparepart dan jasa di dalam service ini
    public function item()
    {
        return $this->hasMany(ServiceItem::class, 'service_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    public function penyerah()
    {
        return $this->belongsTo(User::class, 'diserahkan_oleh');
    }

    public function pembatal()
    {
        return $this->belongsTo(User::class, 'batal_oleh');
    }

    // true kalau sparepart / jasa masih boleh ditambah atau dihapus
    // (belum diambil pelanggan dan belum dibatalkan)
    public function masihBisaDiubah()
    {
        return in_array($this->status, ['diterima', 'dikerjakan', 'selesai']);
    }

    // Menghitung ulang semua angka uang dari isi service_item. Dipanggil setiap ada item ditambah / dihapus.
    public function hitungUlangTotal()
    {
        $totalSparepart = 0;
        $totalJasa = 0;
        $modal = 0;

        foreach ($this->item()->get() as $item) {
            if ($item->jenis == 'jasa') {
                $totalJasa += $item->subtotal;
            } else {
                $totalSparepart += $item->subtotal;
            }

            $modal += $item->modal_satuan * $item->qty;
        }

        $this->total_sparepart = $totalSparepart;
        $this->total_jasa = $totalJasa;
        $this->modal = $modal;
        $this->total = $totalSparepart + $totalJasa - $this->diskon;   // yang dibayar pelanggan
        $this->keuntungan = $this->total - $modal;
        $this->save();
    }

    // Nomor HP pelanggan dalam bentuk yang dipakai link WhatsApp. Contoh: 0812-3456 -> 628123456
    public function nomorWhatsapp()
    {
        $angka = preg_replace('/[^0-9]/', '', (string) $this->hp_pelanggan);

        if (str_starts_with($angka, '0')) {
            $angka = '62' . substr($angka, 1);
        }

        return $angka;
    }
}
