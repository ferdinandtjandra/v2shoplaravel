<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Nama pendek middleware, dipakai di routes/web.php
        $middleware->alias([
            'bos' => \App\Http\Middleware\HanyaBos::class,
            'aktif' => \App\Http\Middleware\CekPenggunaAktif::class,
        ]);

        // Dijalankan di semua halaman: browser dilarang menyimpan salinan halaman
        $middleware->web(append: [
            \App\Http\Middleware\TanpaCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Error 419 = kode pengaman form sudah kedaluwarsa (halaman terlalu lama terbuka, atau sudah logout di tab lain).
        // Daripada menampilkan halaman error berbahasa Inggris, kembalikan ke halaman sebelumnya dengan pesan yang jelas.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() == 419) {
                $pesan = 'Sesi halaman ini sudah habis karena terlalu lama terbuka. Data belum tersimpan, silakan ulangi.';

                if (\Illuminate\Support\Facades\Auth::check()) {
                    return back()->with('error', $pesan);
                }

                return redirect()->route('login')->with('error', $pesan);
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
