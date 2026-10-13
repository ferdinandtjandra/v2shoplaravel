{{-- Menampilkan catatan struk: tiap baris menjadi 1 blok, baris yang diawali "-" dianggap poin. Butuh data: $teks --}}
<div class="catatan">
    @foreach (preg_split('/\r\n|\r|\n/', (string) $teks) as $barisCatatan)
        @if (trim($barisCatatan) != '')
            <div class="{{ str_starts_with(trim($barisCatatan), '-') ? 'poin' : '' }}">{{ trim($barisCatatan) }}</div>
        @endif
    @endforeach
</div>
