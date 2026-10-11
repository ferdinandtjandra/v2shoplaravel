<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class LaporanPenjualanController extends Controller
{
    // Laporan Penjualan: pilih bulan, tahun, sales -> tampil daftar transaksinya.
    // Ada 2 halaman: penjualan biasa (default) dan penjualan yang dibatalkan (?tampil=batal).
    public function index(Request $request)
    {
        // Semua user yang pernah menjadi sales (termasuk yang sudah nonaktif, supaya data lama tetap bisa dilihat)
        $daftarSales = User::where('role', 'sales')->orderBy('name')->get();

        $tampilBatal = $request->tampil == 'batal';
        $daftar = null;
        $jumlahBatal = 0;

        // Laporan baru ditampilkan setelah bulan dan tahun dipilih (dan keduanya harus berupa angka)
        if (is_numeric($request->bulan) && is_numeric($request->tahun)) {
            $daftar = $this->queryPenjualan($request)->get();

            // Jumlah transaksi batal pada periode yang sama, untuk ditampilkan di tombol "Dibatalkan (..)"
            $jumlahBatal = $this->queryPeriode($request)->whereNotNull('dibatalkan_at')->count();
        }

        return view('laporan.penjualan', [
            'daftarSales' => $daftarSales,
            'daftar' => $daftar,
            'tampilBatal' => $tampilBatal,
            'jumlahBatal' => $jumlahBatal,
        ]);
    }

    // Export Laporan Penjualan ke file Excel (.xlsx), sesuai bulan, tahun, dan sales yang dipilih.
    // Isinya 1 baris per barang yang terjual.
    public function export(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
        ]);

        $daftar = $this->queryPenjualan($request)->get();

        // Contoh nama file: penjualan-2026-10.xlsx atau penjualan-batal-2026-10.xlsx
        $namaFile = ($request->tampil == 'batal' ? 'penjualan-batal-' : 'penjualan-')
            . $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT) . '.xlsx';

        // streamDownload: file langsung dikirim ke browser, tidak disimpan di server
        return response()->streamDownload(function () use ($daftar) {
            $writer = new Writer();
            $writer->openToFile('php://output');

            // Baris judul kolom
            $writer->addRow(Row::fromValues([
                'Kode Transaksi', 'Tanggal', 'Jam', 'Sales', 'Kasir', 'Status',
                'Nama Barang', 'Qty', 'Harga Satuan', 'Subtotal', 'Keuntungan Barang', 'Garansi (hari)',
                'Diskon Transaksi', 'Total Transaksi', 'Keterangan',
            ]));

            foreach ($daftar as $penjualan) {
                foreach ($penjualan->detail as $item) {
                    $writer->addRow(Row::fromValues([
                        $penjualan->kode_transaksi,
                        $penjualan->tanggal->format('Y-m-d'),
                        $penjualan->tanggal->format('H:i'),
                        $penjualan->sales->name ?? '',
                        $penjualan->kasir->name ?? '',
                        $penjualan->sudahDibatalkan() ? 'DIBATALKAN' : 'Selesai',
                        $item->nama_barang,
                        $item->qty,
                        $item->harga_satuan,
                        $item->subtotal,
                        $item->keuntungan,
                        $item->garansi_hari,
                        $penjualan->diskon,
                        $penjualan->total,
                        $penjualan->keterangan ?? '',
                    ]));
                }
            }

            $writer->close();
        }, $namaFile);
    }

    // Menyusun query penjualan yang ditampilkan: periode + pilihan halaman (biasa / batal).
    // Dipakai oleh index() dan export(), supaya isi Excel selalu sama dengan yang tampil di layar.
    private function queryPenjualan(Request $request)
    {
        $query = $this->queryPeriode($request)->with('detail', 'sales', 'kasir', 'pembatal');

        if ($request->tampil == 'batal') {
            $query->whereNotNull('dibatalkan_at');  // hanya yang dibatalkan
        } else {
            $query->whereNull('dibatalkan_at');     // hanya yang tidak dibatalkan
        }

        return $query->orderBy('tanggal');
    }

    // Query penjualan pada periode yang dipilih (bulan, tahun, sales), belum dipisah batal / tidak
    private function queryPeriode(Request $request)
    {
        $query = Penjualan::whereYear('tanggal', $request->tahun)->whereMonth('tanggal', $request->bulan);

        // Sales boleh dikosongkan = semua sales
        if (is_numeric($request->sales_id)) {
            $query->where('sales_id', $request->sales_id);
        }

        return $query;
    }
}
