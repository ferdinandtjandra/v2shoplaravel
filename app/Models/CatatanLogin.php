<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CatatanLogin extends Model
{
    protected $table = 'catatan_login';

    protected $guarded = [];

    // Jenis kejadian => tulisan yang ditampilkan
    public const DAFTAR_KEJADIAN = [
        'login' => 'Login',
        'logout' => 'Logout',
        'gagal' => 'Login gagal',
        'diblokir' => 'Diblokir (terlalu banyak salah)',
        'dikeluarkan' => 'Dikeluarkan (akun dinonaktifkan)',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Menyimpan 1 baris catatan.
    // Contoh: CatatanLogin::catat($request, 'login', $user->id, $user->username);
    public static function catat(Request $request, $kejadian, $userId, $username)
    {
        CatatanLogin::create([
            'user_id' => $userId,
            'username' => substr((string) $username, 0, 100),
            'kejadian' => $kejadian,
            'ip' => $request->ip(),
            'perangkat' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    // Mengubah keterangan browser yang panjang menjadi tulisan singkat. Contoh: "Chrome di Windows"
    public function namaPerangkat()
    {
        $teks = (string) $this->perangkat;

        if ($teks == '') {
            return '-';
        }

        // Urutan pengecekan penting: Edge dan Chrome sama-sama mengandung kata "Safari"
        $browser = 'Browser lain';
        if (str_contains($teks, 'Edg')) {
            $browser = 'Edge';
        } elseif (str_contains($teks, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($teks, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($teks, 'Safari')) {
            $browser = 'Safari';
        }

        $sistem = 'perangkat lain';
        if (str_contains($teks, 'Android')) {
            $sistem = 'HP Android';
        } elseif (str_contains($teks, 'iPhone') || str_contains($teks, 'iPad')) {
            $sistem = 'iPhone / iPad';
        } elseif (str_contains($teks, 'Windows')) {
            $sistem = 'Windows';
        } elseif (str_contains($teks, 'Mac')) {
            $sistem = 'Mac';
        }

        return $browser . ' di ' . $sistem;
    }
}
