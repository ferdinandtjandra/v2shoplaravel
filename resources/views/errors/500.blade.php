<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terjadi kesalahan - {{ config('toko.nama') }}</title>
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}">
</head>
<body>
<div class="container text-center" style="padding-top: 15vh;">
    <h1 class="display-1 fw-bold text-danger">500</h1>
    <h4 class="mb-2">Terjadi kesalahan di aplikasi</h4>
    <p class="mb-6">Data Anda tidak berubah. Coba ulangi, dan kalau masih terjadi hubungi pengelola aplikasi.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Dashboard</a>
</div>
</body>
</html>
