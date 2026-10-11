<?php

namespace App\Http\Controllers;

use App\Models\LabaRugi;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabaRugiController extends Controller
{
    // Form input + daftar laporan laba rugi
    public function index()
    {
        $daftar = LabaRugi::with('user')->orderBy('periode', 'desc')->get();

        return view('laba_rugi.index', ['daftar' => $daftar]);
    }

    // Membuat laporan laba rugi untuk 1 bulan
    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'gaji' => 'required',
            'listrik' => 'required',
            'operasional' => 'required',
        ]);

        // Biaya: tolak minus, huruf, dan angka yang terlalu besar
        $this->periksaAngka($request, ['gaji' => 'Gaji', 'listrik' => 'Listrik', 'operasional' => 'Biaya Tak Terduga'], 999999999999);

        // Contoh periode: 2026-10
        $periode = $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT);

        if (LabaRugi::where('periode', $periode)->count() > 0) {
            return back()->with('error', 'Periode Laporan Sudah Pernah dibuatkan.')->withInput();
        }

        // Ambil total penjualan dan total keuntungan pada bulan tersebut
        // (transaksi yang dibatalkan tidak dihitung)
        $penjualanBulanIni = Penjualan::whereNull('dibatalkan_at')->whereYear('tanggal', $request->tahun)->whereMonth('tanggal', $request->bulan);
        $totalPenjualan = (int) $penjualanBulanIni->sum('total');
        $totalKeuntungan = (int) $penjualanBulanIni->sum('keuntungan');

        $gaji = $this->angka($request->gaji);
        $listrik = $this->angka($request->listrik);
        $operasional = $this->angka($request->operasional);

        // Rumus:
        // Harga Pokok Penjualan (modal barang yang terjual) = Total Penjualan - Keuntungan
        // Laba Kotor  = Total Penjualan - Harga Pokok Penjualan  (sama dengan total keuntungan)
        // Total Biaya = Gaji + Listrik + Operasional
        // Laba Bersih = Laba Kotor - Total Biaya
        $hargaPokok = $totalPenjualan - $totalKeuntungan;
        $labaKotor = $totalPenjualan - $hargaPokok;
        $totalBiaya = $gaji + $listrik + $operasional;
        $labaBersih = $labaKotor - $totalBiaya;

        LabaRugi::create([
            'periode' => $periode,
            'total_penjualan' => $totalPenjualan,
            'harga_pokok_penjualan' => $hargaPokok,
            'laba_kotor' => $labaKotor,
            'gaji' => $gaji,
            'listrik' => $listrik,
            'operasional' => $operasional,
            'total_biaya' => $totalBiaya,
            'laba_bersih' => $labaBersih,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('laba_rugi.index')->with('info', 'Laporan periode ' . $periode . ' berhasil dibuat.');
    }

    // Detail laporan (untuk dicetak)
    public function show($id)
    {
        $laporan = LabaRugi::findOrFail($id);

        return view('laba_rugi.show', ['laporan' => $laporan]);
    }
}
