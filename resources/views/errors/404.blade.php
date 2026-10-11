<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman tidak ditemukan - {{ config('toko.nama') }}</title>
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}">
</head>
<body>
<div class="container text-center" style="padding-top: 15vh;">
    <h1 class="display-1 fw-bold text-primary">404</h1>
    <h4 class="mb-2">Halaman atau data tidak ditemukan</h4>
    <p class="mb-6">Alamatnya salah, atau datanya sudah dihapus.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Dashboard</a>
</div>
</body>
</html>
