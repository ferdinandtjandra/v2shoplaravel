<?php

namespace App\Http\Controllers;

use App\Models\LabaRugi;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

// Laba / Rugi dihitung OTOMATIS dari data penjualan, per bulan, untuk 1 tahun.
// Bos tidak perlu "membuat" laporan. Yang diisi Bos hanya BIAYA bulan itu (gaji, listrik, tak terduga),
// dan biaya itu boleh diisi kapan saja serta boleh diubah.
//
// Rumus:
//   Harga Pokok Penjualan (modal barang yang terjual) = Penjualan - Keuntungan
//   Laba Kotor  = total keuntungan penjualan
//   Laba Bersih = Laba Kotor - Biaya
// Transaksi yang dibatalkan tidak dihitung.
class LabaRugiController extends Controller
{
    // Halaman Laba / Rugi: grafik + tabel 12 bulan untuk tahun yang dipilih
    public function index(Request $request)
    {
        $tahun = is_numeric($request->tahun) ? (int) $request->tahun : (int) date('Y');

        // Pilihan tahun: dari tahun penjualan pertama sampai tahun ini
        $tanggalPertama = Penjualan::min('tanggal');
        $tahunPertama = $tanggalPertama ? (int) substr($tanggalPertama, 0, 4) : (int) date('Y');

        return view('laba_rugi.index', [
            'tahun' => $tahun,
            'tahunPertama' => $tahunPertama,
            'perBulan' => $this->hitungSetahun($tahun),
        ]);
    }

    // Menyimpan biaya 1 bulan. Kalau bulan itu sudah pernah diisi, isinya diganti.
    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'gaji' => 'required',
            'listrik' => 'required',
            'operasional' => 'required',
        ]);

        // Biaya: tolak minus, huruf, dan angka yang terlalu besar
        $this->periksaAngka($request, ['gaji' => 'Gaji', 'listrik' => 'Listrik', 'operasional' => 'Biaya Tak Terduga'], 999999999999);

        $gaji = $this->angka($request->gaji);
        $listrik = $this->angka($request->listrik);
        $operasional = $this->angka($request->operasional);
        $totalBiaya = $gaji + $listrik + $operasional;

        // Contoh periode: 2026-10
        $periode = $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT);

        // Angka penjualan bulan itu saat ini (ikut disimpan sebagai catatan; yang ditampilkan selalu dihitung ulang)
        $bulanIni = $this->hitungSetahun((int) $request->tahun)[(int) $request->bulan];

        // updateOrCreate: kalau periode itu sudah ada, diubah. Kalau belum ada, dibuat.
        LabaRugi::updateOrCreate(
            ['periode' => $periode],
            [
                'total_penjualan' => $bulanIni['penjualan'],
                'harga_pokok_penjualan' => $bulanIni['hpp'],
                'laba_kotor' => $bulanIni['laba_kotor'],
                'gaji' => $gaji,
                'listrik' => $listrik,
                'operasional' => $operasional,
                'total_biaya' => $totalBiaya,
                'laba_bersih' => $bulanIni['laba_kotor'] - $totalBiaya,
                'user_id' => Auth::id(),
            ]
        );

        return redirect()->route('laba_rugi.index', ['tahun' => $request->tahun])->with('info', 'Biaya periode ' . $periode . ' berhasil disimpan.');
    }

    // Export laba rugi 1 tahun ke file Excel (.xlsx): 1 baris per bulan + baris total.
    // Isinya sama dengan tabel "Rincian per Bulan" di halaman Laba / Rugi.
    public function export(Request $request)
    {
        $tahun = is_numeric($request->tahun) ? (int) $request->tahun : (int) date('Y');
        $perBulan = $this->hitungSetahun($tahun);

        $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // streamDownload: file langsung dikirim ke browser, tidak disimpan di server
        return response()->streamDownload(function () use ($perBulan, $namaBulan, $tahun) {
            $writer = new Writer();
            $writer->openToFile('php://output');

            // Baris judul kolom
            $writer->addRow(Row::fromValues([
                'Bulan', 'Transaksi', 'Penjualan', 'Harga Pokok', 'Laba Kotor',
                'Gaji', 'Listrik', 'Biaya Tak Terduga', 'Total Biaya', 'Laba Bersih', 'Biaya Sudah Diisi',
            ]));

            // 1 baris per bulan, sambil menjumlahkan untuk baris total
            $total = ['transaksi' => 0, 'penjualan' => 0, 'hpp' => 0, 'laba_kotor' => 0, 'gaji' => 0, 'listrik' => 0, 'operasional' => 0, 'biaya' => 0, 'laba_bersih' => 0];

            foreach ($perBulan as $bulan => $data) {
                $writer->addRow(Row::fromValues([
                    $namaBulan[$bulan] . ' ' . $tahun,
                    $data['transaksi'], $data['penjualan'], $data['hpp'], $data['laba_kotor'],
                    $data['gaji'], $data['listrik'], $data['operasional'], $data['biaya'], $data['laba_bersih'],
                    $data['biaya_diisi'] ? 'Ya' : 'Belum',
                ]));

                foreach ($total as $kolom => $nilai) {
                    $total[$kolom] = $nilai + $data[$kolom];
                }
            }

            $writer->addRow(Row::fromValues([
                'TOTAL ' . $tahun,
                $total['transaksi'], $total['penjualan'], $total['hpp'], $total['laba_kotor'],
                $total['gaji'], $total['listrik'], $total['operasional'], $total['biaya'], $total['laba_bersih'], '',
            ]));

            $writer->close();
        }, 'laba-rugi-' . $tahun . '.xlsx');
    }

    // Halaman cetak laba rugi 1 bulan (di dalam tampilan admin, dengan tombol Print, seperti halaman struk)
    public function cetak(Request $request)
    {
        // Halaman ini dibuka lewat link, bukan form. Kalau bulan / tahun di alamatnya tidak benar,
        // kembali ke halaman Laba / Rugi dengan pesan.
        $bulanBenar = is_numeric($request->bulan) && $request->bulan >= 1 && $request->bulan <= 12;
        $tahunBenar = is_numeric($request->tahun) && $request->tahun >= 2000 && $request->tahun <= 2100;

        if (! $bulanBenar || ! $tahunBenar) {
            return redirect()->route('laba_rugi.index')->with('error', 'Bulan atau tahun laporan tidak benar.');
        }

        return view('laba_rugi.show', [
            'bulan' => (int) $request->bulan,
            'tahun' => (int) $request->tahun,
            'periode' => $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT),
            'data' => $this->hitungSetahun((int) $request->tahun)[(int) $request->bulan],
        ]);
    }

    // Menghitung angka laba rugi tiap bulan (1 sampai 12) untuk 1 tahun.
    // Hasil: array dengan kunci nomor bulan, isinya jumlah transaksi, penjualan, hpp, laba kotor, biaya, laba bersih.
    private function hitungSetahun($tahun)
    {
        // 1. Siapkan dulu 12 bulan dengan isi 0
        $perBulan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $perBulan[$bulan] = [
                'transaksi' => 0, 'penjualan' => 0, 'hpp' => 0, 'laba_kotor' => 0,
                'gaji' => 0, 'listrik' => 0, 'operasional' => 0, 'biaya' => 0,
                'biaya_diisi' => false, 'laba_bersih' => 0,
            ];
        }

        // 2. Jumlahkan penjualan tahun itu ke bulannya masing-masing (yang dibatalkan tidak dihitung)
        $daftarPenjualan = Penjualan::whereNull('dibatalkan_at')->whereYear('tanggal', $tahun)->get(['tanggal', 'total', 'keuntungan']);

        foreach ($daftarPenjualan as $penjualan) {
            $bulan = (int) $penjualan->tanggal->format('n');
            $perBulan[$bulan]['transaksi'] += 1;
            $perBulan[$bulan]['penjualan'] += $penjualan->total;
            $perBulan[$bulan]['laba_kotor'] += $penjualan->keuntungan;
        }

        // 3. Masukkan biaya yang sudah diisi Bos
        foreach (LabaRugi::where('periode', 'like', $tahun . '-%')->get() as $baris) {
            $bulan = (int) substr($baris->periode, 5, 2);
            $perBulan[$bulan]['gaji'] = $baris->gaji;
            $perBulan[$bulan]['listrik'] = $baris->listrik;
            $perBulan[$bulan]['operasional'] = $baris->operasional;
            $perBulan[$bulan]['biaya'] = $baris->gaji + $baris->listrik + $baris->operasional;
            $perBulan[$bulan]['biaya_diisi'] = true;
        }

        // 4. Hitung harga pokok dan laba bersih
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $perBulan[$bulan]['hpp'] = $perBulan[$bulan]['penjualan'] - $perBulan[$bulan]['laba_kotor'];
            $perBulan[$bulan]['laba_bersih'] = $perBulan[$bulan]['laba_kotor'] - $perBulan[$bulan]['biaya'];
        }

        return $perBulan;
    }
}
