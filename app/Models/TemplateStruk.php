<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateStruk extends Model
{
    protected $table = 'template_struk';

    protected $guarded = [];

    // Mengambil template struk (tabel ini hanya punya 1 baris).
    // Kalau belum ada (program baru dipasang), dibuatkan dari identitas toko di file .env.
    public static function ambil()
    {
        $template = TemplateStruk::first();

        if ($template == null) {
            $template = TemplateStruk::create([
                'header' => strtoupper(config('toko.nama')) . "\n" . config('toko.alamat') . "\nHp. " . config('toko.hp'),
                'catatan' => "- Barang yang dibeli harap di periksa dengan baik, tidak dapat dikembalikan.\n- Maaf kami tidak menerima rekapan nota apabila nota ini hilang.",
                'penutup' => '--TERIMA KASIH--',
            ]);
        }

        return $template;
    }
}
