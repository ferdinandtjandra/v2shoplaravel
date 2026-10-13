<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

// Laporan keuangan SERVICE (hanya Bos). Terpisah dari laporan penjualan dan Laba / Rugi toko.
// Pendapatan service dihitung saat HP DIAMBIL dan dibayar, jadi laporannya memakai tanggal diambil.
class LaporanServiceController extends Controller
{
    // Pilih periode (tanggal opsional, bulan, tahun) -> tampil daftar service yang sudah diambil.
    // ?tampil=batal -> daftar service yang dibatalkan pada periode itu.
    public function index(Request $request)
    {
        $daftar = null;
        $jumlahBatal = 0;

        // Laporan baru ditampilkan setelah bulan dan tahun dipilih (dan keduanya harus berupa angka)
        if (is_numeric($request->bulan) && is_numeric($request->tahun)) {
            $daftar = $this->queryService($request)->get();
            $jumlahBatal = $this->queryPeriode($request, 'batal_at')->where('status', 'batal')->count();
        }

        return view('laporan.service', [
            'daftar' => $daftar,
            'tampilBatal' => $request->tampil == 'batal',
            'jumlahBatal' => $jumlahBatal,
        ]);
    }

    // Export ke Excel (.xlsx): 1 baris per sparepart / jasa, mengikuti periode dan halaman yang sedang dibuka
    public function export(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
        ]);

        $daftar = $this->queryService($request)->get();

        $namaFile = ($request->tampil == 'batal' ? 'service-batal-' : 'service-')
            . $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT) . '.xlsx';

        // streamDownload: file langsung dikirim ke browser, tidak disimpan di server
        return response()->streamDownload(function () use ($daftar) {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'No. Service', 'Status', 'Tanggal Terima', 'Tanggal Diambil', 'Pelanggan', 'No. HP', 'Merk / Tipe', 'Teknisi',
                'Jenis', 'Nama Sparepart / Jasa', 'Qty', 'Harga Satuan', 'Subtotal', 'Modal Satuan',
                'Diskon Nota', 'Total Nota', 'Modal Nota', 'Keuntungan Nota', 'Garansi (hari)',
            ]));

            foreach ($daftar as $service) {
                foreach ($service->item as $item) {
                    $writer->addRow(Row::fromValues([
                        $service->kode,
                        Service::DAFTAR_STATUS[$service->status] ?? $service->status,
                        $service->tanggal_terima->format('Y-m-d'),
                        $service->tanggal_diambil ? $service->tanggal_diambil->format('Y-m-d') : '',
                        $service->nama_pelanggan,
                        $service->hp_pelanggan,
                        $service->merk_tipe,
                        $service->nama_teknisi ?? '',
                        \App\Models\ServiceItem::DAFTAR_JENIS[$item->jenis] ?? $item->jenis,
                        $item->nama,
                        $item->qty,
                        $item->harga_satuan,
                        $item->subtotal,
                        $item->modal_satuan,
                        $service->diskon,
                        $service->total,
                        $service->modal,
                        $service->keuntungan,
                        $service->garansi_hari,
                    ]));
                }
            }

            $writer->close();
        }, $namaFile);
    }

    // Query service yang ditampilkan: yang sudah diambil (default) atau yang batal.
    // Dipakai oleh index() dan export(), supaya isi Excel selalu sama dengan yang tampil di layar.
    private function queryService(Request $request)
    {
        if ($request->tampil == 'batal') {
            return $this->queryPeriode($request, 'batal_at')->where('status', 'batal')
                ->with('item', 'pembatal')->orderBy('batal_at');
        }

        return $this->queryPeriode($request, 'tanggal_diambil')->where('status', 'diambil')
            ->with('item')->orderBy('tanggal_diambil');
    }

    // Query service pada periode yang dipilih, berdasarkan kolom tanggal tertentu
    // ($kolomTanggal: tanggal_diambil untuk yang lunas, batal_at untuk yang batal)
    private function queryPeriode(Request $request, $kolomTanggal)
    {
        $query = Service::whereYear($kolomTanggal, $request->tahun)->whereMonth($kolomTanggal, $request->bulan);

        // Tanggal boleh dikosongkan = semua tanggal di bulan itu
        if (is_numeric($request->tanggal) && $request->tanggal >= 1 && $request->tanggal <= 31) {
            $query->whereDay($kolomTanggal, $request->tanggal);
        }

        return $query;
    }
}
