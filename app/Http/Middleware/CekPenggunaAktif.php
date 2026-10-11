<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Kalau pengguna dinonaktifkan oleh Bos saat dia masih login,
// dia langsung dikeluarkan pada klik berikutnya (tidak perlu menunggu dia logout sendiri).
class CekPenggunaAktif
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::user()->aktif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda sudah dinonaktifkan.');
        }

        return $next($request);
    }
}
