<?php

namespace App\Http\Controllers;

use App\Models\CatatanLogin;
use App\Models\User;
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

        // Pengguna pemilik username itu (kosong kalau username-nya tidak terdaftar). Dipakai untuk catatan login.
        $pemilik = User::where('username', $request->username)->first();
        $idPemilik = $pemilik ? $pemilik->id : null;

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $detik = RateLimiter::availableIn($kunci);

            CatatanLogin::catat($request, 'diblokir', $idPemilik, $request->username);

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

            CatatanLogin::catat($request, 'login', Auth::id(), Auth::user()->username);

            return redirect()->route('dashboard');
        }

        RateLimiter::hit($kunci, 60); // catat 1x salah, diingat selama 60 detik

        // Yang dicatat hanya username yang diketik. Password yang salah TIDAK pernah disimpan.
        CatatanLogin::catat($request, 'gagal', $idPemilik, $request->username);

        // Tanpa withInput(): username tidak diisi ulang, harus diketik lagi dari awal
        return back()->with('error', 'Username / Password salah');
    }

    // Logout
    public function keluar(Request $request)
    {
        if (Auth::check()) {
            CatatanLogin::catat($request, 'logout', Auth::id(), Auth::user()->username);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
