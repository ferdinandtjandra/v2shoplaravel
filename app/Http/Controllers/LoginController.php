<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    // Menampilkan halaman login
    public function form()
    {
        return view('auth.login');
    }

    // Memproses login
    public function masuk(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Pembatas tebak-tebakan password: maksimal 5x salah untuk username yang sama dari komputer yang sama.
        // Setelah itu harus menunggu 1 menit (password yang benar pun ditolak dulu).
        // (md5 dipakai supaya kuncinya selalu pendek, walau username yang diketik sangat panjang)
        $kunci = 'login:' . md5(strtolower($request->username)) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);

            return back()->with('error', 'Terlalu banyak percobaan login yang salah. Coba lagi dalam ' . $detik . ' detik.');
        }

        $dataLogin = [
            'username' => $request->username,
            'password' => $request->password,
            'aktif' => 1, // user nonaktif tidak bisa login
        ];

        if (Auth::attempt($dataLogin)) {
            RateLimiter::clear($kunci); // login benar: hitungan salah dihapus
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        RateLimiter::hit($kunci, 60); // catat 1x salah, diingat selama 60 detik

        // Tanpa withInput(): username tidak diisi ulang, harus diketik lagi dari awal
        return back()->with('error', 'Username / Password salah');
    }

    // Logout
    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
