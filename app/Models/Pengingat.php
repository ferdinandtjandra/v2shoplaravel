<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengingat extends Model
{
    protected $table = 'pengingat';

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Berapa hari lagi sampai tanggal pengingat. 0 = hari ini, 1 = besok, minus = sudah lewat.
    public function sisaHari()
    {
        $hariIni = strtotime(date('Y-m-d'));
        $tanggal = strtotime($this->tanggal->format('Y-m-d'));

        return (int) round(($tanggal - $hariIni) / 86400); // 86400 = jumlah detik dalam 1 hari
    }

    // Pengingat yang tampil di dashboard: HANYA yang tanggalnya hari ini.
    // Sebelum tanggalnya belum muncul, dan besoknya otomatis hilang.
    public static function untukDashboard()
    {
        return Pengingat::where('tanggal', date('Y-m-d'))->orderBy('id')->get();
    }
}
