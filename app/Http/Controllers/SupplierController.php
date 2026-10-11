<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Supplier;
use App\Models\SupplierRiwayat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index()
    {
        $daftar = Supplier::orderBy('id', 'desc')->get();

        return view('supplier.index', ['daftar' => $daftar]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $supplier = Supplier::create($data);

        $this->catatRiwayat($supplier, 'dibuat', 'Supplier dibuat.');

        return redirect()->route('supplier.index')->with('info', 'Data berhasil disimpan.');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);

        $riwayat = SupplierRiwayat::with('user')->where('supplier_id', $supplier->id)->orderBy('id', 'desc')->get();

        return view('supplier.edit', [
            'supplier' => $supplier,
            'riwayat' => $riwayat,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validasi($request);

        $supplier = Supplier::findOrFail($id);

        // Simpan dulu isi supplier SEBELUM diubah, untuk dibandingkan setelah disimpan
        $lama = $supplier->replicate();

        $supplier->update($data);

        // Catat apa saja yang berubah. Kalau tidak ada yang berubah, tidak dicatat.
        $perubahan = $this->daftarPerubahan($lama, $supplier);
        if (count($perubahan) > 0) {
            $this->catatRiwayat($supplier, 'diubah', implode("\n", $perubahan));
        }

        // Tetap di halaman edit, supaya hasil perubahan dan riwayatnya langsung terlihat
        return redirect()->route('supplier.edit', $supplier->id)->with('info', 'Data berhasil diubah.');
    }

    public function destroy($id)
    {
        // Tidak boleh dihapus kalau masih dipakai oleh barang
        $dipakai = Barang::withTrashed()->where('supplier_id', $id)->count();
        if ($dipakai > 0) {
            return redirect()->route('supplier.index')->with('error', 'Tidak bisa dihapus, masih dipakai oleh ' . $dipakai . ' barang.');
        }

        // findOrFail: kalau datanya sudah tidak ada, tampil halaman 404 (bukan pesan "berhasil")
        Supplier::findOrFail($id)->delete();

        return redirect()->route('supplier.index')->with('info', 'Data berhasil dihapus.');
    }

    // Menyimpan 1 baris riwayat supplier. $aksi: dibuat / diubah
    private function catatRiwayat(Supplier $supplier, $aksi, $keterangan)
    {
        SupplierRiwayat::create([
            'supplier_id' => $supplier->id,
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'keterangan' => $keterangan,
        ]);
    }

    // Membandingkan supplier sebelum dan sesudah diedit.
    // Hasilnya daftar tulisan, contoh: ["No. HP: 0812... → 0813...", "Bank: - → BCA"]
    private function daftarPerubahan(Supplier $lama, Supplier $baru)
    {
        // nama kolom => tulisan yang ditampilkan
        $kolom = [
            'perusahaan' => 'Nama Perusahaan',
            'pic' => 'PIC',
            'alamat' => 'Alamat',
            'hp' => 'No. HP',
            'telp' => 'Telp',
            'email' => 'Email',
            'bank' => 'Bank',
            'rek' => 'No. Rekening',
            'atasnama' => 'Atas Nama',
        ];

        $perubahan = [];

        foreach ($kolom as $namaKolom => $label) {
            // (string) supaya kosong ("") dan null dianggap sama
            if ((string) $lama->$namaKolom != (string) $baru->$namaKolom) {
                $perubahan[] = $label . ': ' . ($lama->$namaKolom ?: '-') . ' → ' . ($baru->$namaKolom ?: '-');
            }
        }

        return $perubahan;
    }

    // Aturan validasi dipakai oleh store() dan update(), jadi ditulis satu kali di sini.
    // Hasilnya adalah array data yang sudah lolos validasi.
    private function validasi(Request $request)
    {
        return $request->validate([
            'perusahaan' => 'required|max:150',
            'pic' => 'required|max:150',
            'alamat' => 'nullable',
            'telp' => 'nullable|max:20',
            'hp' => 'required|max:20',
            'email' => 'nullable|max:100',
            'bank' => 'nullable|max:100',
            'rek' => 'nullable|max:50',
            'atasnama' => 'nullable|max:150',
        ]);
    }
}
