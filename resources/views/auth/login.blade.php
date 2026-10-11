<!DOCTYPE html>
<html lang="id" class="layout-wide customizer-hide" data-assets-path="{{ asset('sneat') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('toko.nama') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/img/favicon/favicon.ico') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/vendor/css/pages/page-auth.css') }}">
    <script src="{{ asset('sneat/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('sneat/js/config.js') }}"></script>
</head>
<body>
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <div class="app-brand justify-content-center">
                        <span class="app-brand-text demo text-heading fw-bold">{{ config('toko.nama') }}</span>
                    </div>
                    <h4 class="mb-1">Selamat datang di {{ config('toko.nama') }}</h4>
                    <p class="mb-6">Silakan login dengan username dan password Anda.</p>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">UserID / Password harus diisi</div>
                    @endif

                    {{-- ================================================================
                         Laptop dipakai bergantian oleh Bos dan staff, jadi browser TIDAK BOLEH
                         mengingat atau mengisi otomatis username dan password. Caranya:
                         1. autocomplete="off" di form dan semua kotak.
                         2. Kotak password sengaja memakai type="text" (bukan type="password").
                            Browser hanya menawarkan "simpan password" dan mengisi otomatis pada kotak
                            type="password". Hurufnya tetap disembunyikan jadi titik lewat CSS di bawah.
                         3. Username tidak diisi ulang setelah login gagal.
                         ================================================================ --}}
                    <style>
                        .sandi-tersembunyi { -webkit-text-security: disc; text-security: disc; }
                    </style>

                    <form method="POST" action="{{ route('login.masuk') }}" class="mb-6" autocomplete="off">
                        @csrf
                        <div class="mb-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value=""
                                autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" autofocus>
                        </div>
                        <div class="mb-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="text" class="form-control sandi-tersembunyi" id="password" name="password" value=""
                                autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Pengaman: kalau browser tidak mendukung CSS penyembunyi huruf di atas (browser lama),
    // kotak password dikembalikan ke type="password" supaya hurufnya tetap tidak terlihat.
    if (! (window.CSS && (CSS.supports('-webkit-text-security', 'disc') || CSS.supports('text-security', 'disc')))) {
        document.getElementById('password').type = 'password';
    }
</script>
</body>
</html>
