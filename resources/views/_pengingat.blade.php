{{-- Pengingat di bagian atas dashboard (Bos dan staff). Butuh data: $pengingat
     Hanya berisi pengingat yang tanggalnya HARI INI.
     Sengaja dibuat mencolok: kotak besar merah, ikon lonceng, dan berkedip pelan. --}}
@if (count($pengingat) > 0)
    <style>
        .pengingat { display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-radius: 10px; margin-bottom: 12px; color: #fff;
            background: linear-gradient(135deg, #ff3e1d, #d81b3c); animation: kedip 1.6s ease-in-out infinite; }
        .pengingat .lonceng { font-size: 2.2rem; line-height: 1; }
        .pengingat .kapan { font-size: 12px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; background: rgba(255, 255, 255, .25); padding: 2px 10px; border-radius: 20px; display: inline-block; margin-bottom: 4px; }
        .pengingat .pesan { font-size: 1.15rem; font-weight: 600; line-height: 1.35; }

        @keyframes kedip {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 62, 29, .55); }
            50% { box-shadow: 0 0 0 12px rgba(255, 62, 29, 0); }
        }

        /* Untuk pengguna yang mematikan animasi di perangkatnya */
        @media (prefers-reduced-motion: reduce) { .pengingat { animation: none; } }
    </style>

    <div class="mb-6">
        @foreach ($pengingat as $p)
            <div class="pengingat" role="alert">
                <i class="lonceng icon-base bx bx-bell"></i>
                <div>
                    <span class="kapan">Pengingat hari ini &middot; {{ $p->tanggal->translatedFormat('j F Y') }}</span>
                    <div class="pesan">{{ $p->pesan }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif
