<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangRiwayat;
use App\Models\KartuStok;
use App\Models\Kategori;
use App\Models\Satuan;
use App\Models\StokPenyesuaian;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class BarangController extends Controller
{
    // Laporan Stok. Bisa dibuka semua user, tapi kolom supplier, harga distributor
    // dan tombol edit / hapus hanya tampil untuk Bos (diatur di view).
    //
    // Data dimuat PER HALAMAN (25 barang), bukan semuanya sekaligus, supaya halaman cepat terbuka.
    // Pencarian, filter, dan urutan diproses di server lewat alamat URL, contoh:
    //   /stok?cari=samsung&kategori_id=1&urut=qty_kecil&page=2
    public function index(Request $request)
    {
        $query = $this->queryStok($request);

        // Ringkasan dihitung dari SEMUA barang yang cocok, bukan hanya 25 yang tampil.
        // (clone = menyalin query, supaya query aslinya tidak ikut berubah)
        $jumlahPcs = (clone $query)->sum('qty');
        $nilaiModal = (clone $query)->sum(DB::raw('qty * harga_distributor'));

        // paginate(25): ambil 25 barang untuk halaman yang sedang dibuka.
        // withQueryString(): tombol halaman 2, 3, ... tetap membawa kata pencarian dan filter.
        $daftar = $query->paginate(25)->withQueryString();

        return view('barang.index', [
            'daftar' => $daftar,
            'jumlahPcs' => $jumlahPcs,
            'nilaiModal' => $nilaiModal,
            'tampilHabis' => $request->tampil == 'habis' && Auth::user()->isBos(),
            'tampilDihapus' => $request->tampil == 'dihapus' && Auth::user()->isBos(),
            'cari' => is_string($request->cari) ? $request->cari : '',
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    // Export Laporan Stok ke file Excel (.xlsx). Hanya Bos.
    // Isinya mengikuti pencarian dan filter yang sedang dipakai di halaman Laporan Stok.
    public function export(Request $request)
    {
        $daftar = $this->queryStok($request)->get();

        $namaFile = 'stok-' . date('Y-m-d') . '.xlsx';

        // streamDownload: file langsung dikirim ke browser, tidak disimpan di server
        return response()->streamDownload(function () use ($daftar) {
            $writer = new Writer();
            $writer->openToFile('php://output');

            // Baris judul kolom
            $writer->addRow(Row::fromValues([
                'Kode', 'Nama Barang', 'Kategori', 'Satuan', 'Supplier', 'Qty',
                'Harga Distributor', 'Harga Member', 'Harga User', 'Harga + Ongkir', 'Nilai Modal', 'Tanggal Masuk',
            ]));

            // 1 baris per barang
            foreach ($daftar as $barang) {
                $writer->addRow(Row::fromValues([
                    $barang->kode,
                    $barang->nama,
                    $barang->kategori->nama ?? '',
                    $barang->satuan->nama ?? '',
                    $barang->supplier->perusahaan ?? '',
                    $barang->qty,
                    $barang->harga_distributor,
                    $barang->harga_member,
                    $barang->harga_user,
                    $barang->harga_ongkir,
                    $barang->qty * $barang->harga_distributor,
                    $barang->tanggal_masuk->format('Y-m-d'),
                ]));
            }

            $writer->close();
        }, $namaFile);
    }

    // Menyusun query Laporan Stok dari pilihan di halaman (tampil, cari, kategori, urut).
    // Dipakai oleh index() dan export(), supaya isi Excel selalu sama dengan yang tampil di layar.
    private function queryStok(Request $request)
    {
        $query = Barang::with('kategori', 'satuan', 'supplier');

        // 1. Tampilan: stok tersedia (default) / stok habis / barang dihapus. Dua terakhir hanya untuk Bos.
        if ($request->tampil == 'dihapus' && Auth::user()->isBos()) {
            $query->onlyTrashed();
        } elseif ($request->tampil == 'habis' && Auth::user()->isBos()) {
            $query->where('qty', '<=', 0);
        } else {
            $query->where('qty', '>', 0);
        }

        // 2. Pencarian: cocok dengan nama ATAU kode barang
        //    (is_string: kalau kata pencarian dikirim dalam bentuk aneh, dianggap kosong)
        $cari = is_string($request->cari) ? $request->cari : '';
        if ($cari != '') {
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', '%' . $cari . '%')
                    ->orWhere('kode', 'like', '%' . $cari . '%');
            });
        }

        // 3. Filter kategori
        if (is_numeric($request->kategori_id)) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // 4. Urutan
        if ($request->urut == 'nama') {
            $query->orderBy('nama');
        } elseif ($request->urut == 'qty_kecil') {
            $query->orderBy('qty')->orderBy('nama');
        } elseif ($request->urut == 'qty_besar') {
            $query->orderBy('qty', 'desc')->orderBy('nama');
        } else {
            $query->orderBy('id', 'desc'); // default: barang terbaru di atas
        }

        return $query;
    }

    // Form Barang Masuk
    public function create()
    {
        return view('barang.form', [
            'barang' => new Barang(),
            'riwayat' => [],
            'kartuStok' => [],
            'kategori' => Kategori::orderBy('nama')->get(),
            'satuan' => Satuan::orderBy('nama')->get(),
            'supplier' => Supplier::orderBy('perusahaan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validasi($request);

        // Barang masuk harus ada jumlahnya (di halaman Edit, qty 0 boleh)
        if ($this->angka($request->qty) < 1) {
            return back()->withErrors(['qty' => 'Quantity minimal 1.'])->withInput();
        }

        $barang = new Barang();
        $this->isiDariForm($barang, $request);
        $barang->tanggal_masuk = now();
        $barang->created_by = Auth::id();
        $barang->save();

        // Kode barang dibuat dari ID, contoh: ID 5374 -> STCK5374.
        // Karena ID tidak pernah kembar, kode barang juga tidak akan kembar.
        $barang->kode = 'STCK' . str_pad($barang->id, 3, '0', STR_PAD_LEFT);
        $barang->save();

        $this->catatRiwayat($barang, 'dibuat', 'Barang masuk. Qty ' . $barang->qty
            . ', Harga Distributor ' . number_format($barang->harga_distributor)
            . ', Member ' . number_format($barang->harga_member)
            . ', User ' . number_format($barang->harga_user)
            . ', + Ongkir ' . number_format($barang->harga_ongkir));

        // Catat stok awal di kartu stok: dari 0 menjadi qty yang diinput
        KartuStok::catat($barang, 'masuk', $barang->qty, 0, $barang->qty, 'Barang masuk');

        return redirect()->route('barang.create')->with('info', 'Barang ' . $barang->kode . ' berhasil disimpan.');
    }

    public function edit($id)
    {
        // withTrashed: barang yang sudah dihapus tetap bisa dibuka, supaya riwayatnya bisa dilihat
        $barang = Barang::withTrashed()->findOrFail($id);

        $riwayat = BarangRiwayat::with('user')->where('barang_id', $barang->id)->orderBy('id', 'desc')->get();

        // Kartu stok: semua perubahan stok barang ini, yang terbaru di atas
        $kartuStok = KartuStok::with('user')->where('barang_id', $barang->id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();

        return view('barang.form', [
            'barang' => $barang,
            'riwayat' => $riwayat,
            'kartuStok' => $kartuStok,
            'kategori' => Kategori::orderBy('nama')->get(),
            'satuan' => Satuan::orderBy('nama')->get(),
            'supplier' => Supplier::orderBy('perusahaan')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validasi($request);

        $barang = Barang::findOrFail($id);
        $stokSebelum = $barang->qty;

        // Simpan dulu isi barang SEBELUM diubah, untuk dibandingkan setelah disimpan
        $lama = $barang->replicate();

        $this->isiDariForm($barang, $request);
        $barang->updated_by = Auth::id();
        $barang->save();

        // Catat apa saja yang berubah. Kalau tidak ada yang berubah, tidak dicatat.
        $perubahan = $this->daftarPerubahan($lama, $barang);
        if (count($perubahan) > 0) {
            $this->catatRiwayat($barang, 'diubah', implode("\n", $perubahan));
        }

        // Kalau qty diubah lewat halaman Edit Barang, perubahan itu ikut dicatat
        // di riwayat Penyesuaian Stok, supaya tidak ada perubahan stok yang tidak tercatat.
        if ($barang->qty != $stokSebelum) {
            StokPenyesuaian::create([
                'barang_id' => $barang->id,
                'user_id' => Auth::id(),
                'jenis' => $barang->qty > $stokSebelum ? 'tambah' : 'kurang',
                'qty' => abs($barang->qty - $stokSebelum),
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $barang->qty,
                'alasan' => 'Koreksi hitung stok',
                'keterangan' => 'Qty diubah lewat halaman Edit Barang',
            ]);

            KartuStok::catat($barang, 'edit', $barang->qty - $stokSebelum, $stokSebelum, $barang->qty, 'Qty diubah lewat halaman Edit Barang');
        }

        // Tetap di halaman edit, supaya hasil perubahan dan riwayatnya langsung terlihat
        return redirect()->route('barang.edit', $barang->id)->with('info', 'Barang berhasil diubah.');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);

        // Catat siapa yang menghapus, lalu hapus (soft delete: hanya mengisi deleted_at)
        $barang->deleted_by = Auth::id();
        $barang->save();
        $barang->delete();

        $this->catatRiwayat($barang, 'dihapus', 'Barang dihapus. Stok saat dihapus: ' . $barang->qty);

        return redirect()->route('barang.index')->with('info', 'Barang berhasil dihapus.');
    }

    // Mengembalikan barang yang sudah dihapus
    public function kembalikan($id)
    {
        $barang = Barang::onlyTrashed()->findOrFail($id);

        $barang->restore();             // mengosongkan deleted_at
        $barang->deleted_by = null;
        $barang->save();

        $this->catatRiwayat($barang, 'dipulihkan', 'Barang dikembalikan dari daftar barang dihapus.');

        return redirect()->route('barang.edit', $barang->id)->with('info', 'Barang ' . $barang->kode . ' berhasil dikembalikan.');
    }

    // Menyimpan 1 baris riwayat barang. $aksi: dibuat / diubah / dihapus / dipulihkan
    private function catatRiwayat(Barang $barang, $aksi, $keterangan)
    {
        BarangRiwayat::create([
            'barang_id' => $barang->id,
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'keterangan' => $keterangan,
        ]);
    }

    // Membandingkan barang sebelum dan sesudah diedit.
    // Hasilnya daftar tulisan, contoh: ["Qty: 5 → 7", "Harga Jual User: 20,000 → 25,000"]
    private function daftarPerubahan(Barang $lama, Barang $baru)
    {
        $perubahan = [];

        if ($lama->nama != $baru->nama) {
            $perubahan[] = 'Nama: ' . $lama->nama . ' → ' . $baru->nama;
        }

        if ($lama->kategori_id != $baru->kategori_id) {
            $perubahan[] = 'Kategori: ' . (Kategori::find($lama->kategori_id)->nama ?? '-') . ' → ' . (Kategori::find($baru->kategori_id)->nama ?? '-');
        }

        if ($lama->satuan_id != $baru->satuan_id) {
            $perubahan[] = 'Satuan: ' . (Satuan::find($lama->satuan_id)->nama ?? '-') . ' → ' . (Satuan::find($baru->satuan_id)->nama ?? '-');
        }

        if ($lama->supplier_id != $baru->supplier_id) {
            $perubahan[] = 'Supplier: ' . (Supplier::find($lama->supplier_id)->perusahaan ?? '-') . ' → ' . (Supplier::find($baru->supplier_id)->perusahaan ?? '-');
        }

        // Kolom angka: nama kolom => tulisan yang ditampilkan
        $kolomAngka = [
            'qty' => 'Qty',
            'harga_distributor' => 'Harga Distributor',
            'harga_member' => 'Harga Jual Member',
            'harga_user' => 'Harga Jual User',
            'harga_ongkir' => 'Harga Jual + Ongkir',
        ];

        foreach ($kolomAngka as $kolom => $label) {
            if ($lama->$kolom != $baru->$kolom) {
                $perubahan[] = $label . ': ' . number_format($lama->$kolom) . ' → ' . number_format($baru->$kolom);
            }
        }

        return $perubahan;
    }

    private function validasi(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:150',
            'kategori_id' => 'nullable|exists:kategori,id',
            'satuan_id' => 'required|exists:satuan,id',
            'supplier_id' => 'nullable|exists:supplier,id',
            'qty' => 'required',
            'harga_distributor' => 'required',
            'harga_member' => 'required',
            'harga_user' => 'required',
            'harga_ongkir' => 'required',
        ]);

        // Kotak angka: tolak minus, huruf, dan angka yang terlalu besar
        $this->periksaAngka($request, ['qty' => 'Quantity'], 1000000);
        $this->periksaAngka($request, [
            'harga_distributor' => 'Harga Distributor',
            'harga_member' => 'Harga Jual Member',
            'harga_user' => 'Harga Jual User',
            'harga_ongkir' => 'Harga Jual + Ongkir',
        ]);

        // Harga jual tidak boleh di bawah harga distributor (kalau lolos, barangnya tidak akan bisa dijual)
        $hargaJual = [
            'harga_member' => 'Harga Jual Member',
            'harga_user' => 'Harga Jual User',
            'harga_ongkir' => 'Harga Jual + Ongkir',
        ];

        foreach ($hargaJual as $namaKotak => $label) {
            if ($this->angka($request->input($namaKotak)) < $this->angka($request->harga_distributor)) {
                throw ValidationException::withMessages([$namaKotak => $label . ' tidak boleh lebih kecil dari Harga Distributor.']);
            }
        }
    }

    // Memindahkan isi form ke object barang. Dipakai oleh store() dan update().
    private function isiDariForm(Barang $barang, Request $request)
    {
        $barang->nama = $request->nama;
        $barang->kategori_id = $request->kategori_id;
        $barang->satuan_id = $request->satuan_id;
        $barang->supplier_id = $request->supplier_id;
        $barang->qty = $this->angka($request->qty);
        $barang->harga_distributor = $this->angka($request->harga_distributor);
        $barang->harga_member = $this->angka($request->harga_member);
        $barang->harga_user = $this->angka($request->harga_user);
        $barang->harga_ongkir = $this->angka($request->harga_ongkir);
    }
}
