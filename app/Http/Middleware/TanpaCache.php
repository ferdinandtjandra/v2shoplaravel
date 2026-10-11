<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Melarang browser menyimpan salinan halaman (cache).
// Gunanya: setelah Logout, tombol "Back" di browser tidak bisa menampilkan lagi
// halaman milik user sebelumnya. Penting karena satu laptop dipakai Bos dan staff bergantian.
class TanpaCache
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
