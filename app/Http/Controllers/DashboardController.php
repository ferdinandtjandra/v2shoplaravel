<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\NotaJatuhTempo;
use App\Models\Pengingat;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Bos dan staff melihat dashboard yang berbeda.
        // Angka uang (penjualan, keuntungan, nota) hanya untuk Bos.
        if (Auth::user()->isBos()) {
            return $this->dashboardBos();
        }

        return $this->dashboardStaff();
    }

    private function dashboardBos()
    {
        // Catatan: transaksi yang dibatalkan (dibatalkan_at terisi) tidak ikut dihitung.

        // ---------- 1. Penjualan hari ini ----------
        $penjualanHariIni = Penjualan::whereNull('dibatalkan_at')->whereDate('tanggal', date('Y-m-d'))->get();

        // ---------- 2. Penjualan bulan ini dan bulan lalu ----------
        $penjualanBulanIni = Penjualan::whereNull('dibatalkan_at')->whereYear('tanggal', date('Y'))->whereMonth('tanggal', date('n'))->get();

        $bulanLalu = strtotime('first day of last month');
        $totalBulanLalu = Penjualan::whereNull('dibatalkan_at')->whereYear('tanggal', date('Y', $bulanLalu))->whereMonth('tanggal', date('n', $bulanLalu))->sum('total');

        // ---------- 3. Grafik: total penjualan per tanggal di bulan ini ----------
        // Siapkan dulu semua tanggal (1 sampai akhir bulan) dengan isi 0, lalu diisi dari data penjualan.
        $perHari = [];
        for ($tanggal = 1; $tanggal <= date('t'); $tanggal++) {
            $perHari[$tanggal] = ['total' => 0, 'jumlah' => 0];
        }

        foreach ($penjualanBulanIni as $penjualan) {
            $tanggal = (int) $penjualan->tanggal->format('j');
            $perHari[$tanggal]['total'] += $penjualan->total;
            $perHari[$tanggal]['jumlah'] += 1;
        }

        // Nilai tertinggi, dipakai untuk menghitung tinggi batang grafik (yang tertinggi = 100%)
        $tertinggi = max(array_column($perHari, 'total'));

        // ---------- 4. 5 barang terlaris bulan ini ----------
        $terlaris = PenjualanDetail::whereIn('penjualan_id', $penjualanBulanIni->pluck('id'))
            ->get()
            ->groupBy('nama_barang')                    // kelompokkan per nama barang
            ->map(function ($baris) {
                return $baris->sum('qty');              // jumlahkan qty tiap kelompok
            })
            ->sortDesc()
            ->take(5);

        // ---------- 5. Nota: yang sudah lewat + yang jatuh tempo dalam 7 hari ----------
        $notaJatuhTempo = NotaJatuhTempo::where('tanggal_jatuh_tempo', '<=', date('Y-m-d', strtotime('+7 day')))
            ->orderBy('tanggal_jatuh_tempo')
            ->get();

        return view('dashboard', [
            'serviceAktif' => $this->serviceAktif(),
            'serviceSiapDiambil' => $this->serviceSiapDiambil(),
            'pengingat' => Pengingat::untukDashboard(),
            'penjualanHariIni' => $penjualanHariIni,
            'penjualanBulanIni' => $penjualanBulanIni,
            'totalBulanLalu' => $totalBulanLalu,
            'perHari' => $perHari,
            'tertinggi' => $tertinggi,
            'terlaris' => $terlaris,
            'notaJatuhTempo' => $notaJatuhTempo,
            'transaksiTerakhir' => Penjualan::with('sales')->whereNull('dibatalkan_at')->orderBy('id', 'desc')->limit(5)->get(),
            'stokMenipis' => $this->stokMenipis(),
            'jumlahStokMenipis' => Barang::where('qty', '>', 0)->where('qty', '<=', 2)->count(),
            'jumlahBarang' => Barang::where('qty', '>', 0)->count(),
        ]);
    }

    private function dashboardStaff()
    {
        // Staff hanya melihat transaksi yang dia input sendiri hari ini
        $transaksiSaya = Penjualan::whereNull('dibatalkan_at')->where('kasir_id', Auth::id())->whereDate('tanggal', date('Y-m-d'))->orderBy('id', 'desc')->get();

        return view('dashboard_staff', [
            'serviceAktif' => $this->serviceAktif(),
            'serviceSiapDiambil' => $this->serviceSiapDiambil(),
            'pengingat' => Pengingat::untukDashboard(),
            'transaksiSaya' => $transaksiSaya,
            'stokMenipis' => $this->stokMenipis(),
            'jumlahBarang' => Barang::where('qty', '>', 0)->count(),
        ]);
    }

    // Jumlah service yang sedang dikerjakan
    private function serviceAktif()
    {
        return Service::whereIn('status', ['diterima', 'dikerjakan'])->count();
    }

    // Service yang sudah selesai tapi belum diambil pelanggan, yang paling lama menunggu di atas
    private function serviceSiapDiambil()
    {
        return Service::where('status', 'selesai')->orderBy('tanggal_selesai')->get();
    }

    // 10 barang dengan stok paling sedikit (yang masih ada stoknya)
    private function stokMenipis()
    {
        return Barang::where('qty', '>', 0)->orderBy('qty')->orderBy('nama')->limit(10)->get();
    }
}
