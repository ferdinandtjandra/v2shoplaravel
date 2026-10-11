<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PasswordHapus extends Model
{
    protected $table = 'password_hapus';

    protected $guarded = [];

    protected $hidden = ['password', 'password_terlihat'];

    // 'encrypted' = otomatis dienkripsi saat disimpan dan dibuka lagi saat dibaca (untuk ditampilkan ke Bos)
    protected $casts = [
        'password_terlihat' => 'encrypted',
    ];

    // Mengecek apakah password yang diketik cocok dengan salah satu password hapus.
    // Hasilnya true (cocok) atau false (tidak cocok).
    public static function cocok($passwordDiketik)
    {
        if ($passwordDiketik == null || $passwordDiketik == '') {
            return false;
        }

        foreach (PasswordHapus::all() as $baris) {
            if (Hash::check($passwordDiketik, $baris->password)) {
                return true;
            }
        }

        return false;
    }
}
