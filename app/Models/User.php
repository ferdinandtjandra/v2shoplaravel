<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    // Kolom yang boleh diisi lewat User::create([...])
    protected $fillable = ['name', 'username', 'password', 'password_terlihat', 'role', 'aktif'];

    // Kolom yang disembunyikan (tidak ikut tampil kalau data diubah jadi array / JSON)
    protected $hidden = ['password', 'password_terlihat', 'remember_token'];

    // 'hashed'    = password otomatis di-hash saat disimpan (dipakai untuk login, tidak bisa dibaca lagi)
    // 'encrypted' = otomatis dienkripsi saat disimpan dan dibuka lagi saat dibaca (dipakai untuk ditampilkan ke Bos)
    protected $casts = [
        'password' => 'hashed',
        'password_terlihat' => 'encrypted',
        'aktif' => 'boolean',
    ];

    // Daftar role. Dulu: status 1 = Bos, 2 = Karyawan, 3 = Sales
    public const DAFTAR_ROLE = [
        'bos' => 'Bos',
        'karyawan' => 'Karyawan',
        'sales' => 'Sales',
    ];

    // Dipakai untuk mengecek hak akses. Contoh: if (Auth::user()->isBos()) { ... }
    public function isBos()
    {
        return $this->role == 'bos';
    }
}
