<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Memindahkan data dari database LAMA (vshopcom_shop) ke database BARU (Laravel).
//
// Cara pakai:
//   1. php artisan migrate:fresh      (membuat tabel baru yang masih kosong)
//   2. php artisan import:lama
//
// Database lama hanya DIBACA, tidak ada yang diubah di sana.
// Alamat database lama diatur di file .env (DB_LAMA_...).
class ImportDataLama extends Command
{
    protected $signature = 'import:lama';

    protected $description = 'Memindahkan data dari database lama ke database baru';

    // "Kamus" untuk mengubah teks di database lama menjadi ID di database baru.
    // Contoh: $idUser['sanny'] = 1
    private $idUser = [];
    private $idKategori = [];
    private $idSatuan = [];
    private $idSupplier = [];
    private $idBarang = [];      // kode barang    => id barang
    private $idPenjualan = [];   // kode transaksi => id penjualan

    public function handle()
    {
        // Import hanya boleh ke database yang masih kosong, supaya data tidak dobel
        if (DB::table('users')->count() > 0 || DB::table('barang')->count() > 0) {
            $this->error('Database baru sudah ada isinya. Jalankan dulu: php artisan migrate:fresh');

            return 1;
        }

        $lama = DB::connection('mysql_lama');

        $this->importUser($lama);
        $this->importPasswordHapus($lama);
        $this->importKategori($lama);
        $this->importSatuan($lama);
        $this->importSupplier($lama);
        $this->importBarang($lama);
        $this->importPenjualan($lama);
        $this->importPenjualanDetail($lama);
        $this->importRetur($lama);
        $this->importNota($lama);
        $this->importLabaRugi($lama);
        $this->lanjutkanNomorTransaksi($lama);
        $this->isiRiwayatBarang();
        $this->isiTemplateStrukPenjualan();

        \App\Models\KartuStok::isiDariDataLama();
        $this->line('Kartu stok      : ' . DB::table('kartu_stok')->count());

        $this->info('SELESAI. Semua data lama sudah dipindahkan.');

        return 0;
    }

    private function importUser($lama)
    {
        $roleDariStatus = [1 => 'bos', 2 => 'karyawan', 3 => 'sales'];

        foreach ($lama->table('ms_user')->orderBy('user_id')->get() as $u) {
            DB::table('users')->insert([
                'id' => $u->user_id,
                'name' => trim($u->name),
                'username' => trim($u->username),
                // Password lama disimpan sebagai teks biasa. Sekarang di-hash.
                // User tetap login dengan password yang sama seperti sebelumnya.
                'password' => Hash::make($u->password),
                'password_terlihat' => Crypt::encryptString($u->password), // salinan terenkripsi, untuk ditampilkan ke Bos
                'role' => $roleDariStatus[$u->status] ?? 'karyawan',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->idUser[$this->kunci($u->name)] = $u->user_id;
        }

        $this->line('User            : ' . count($this->idUser));
    }

    private function importPasswordHapus($lama)
    {
        $jumlah = 0;

        foreach ($lama->table('ms_pass_delete')->get() as $p) {
            if ($p->password == null || $p->password == '') {
                continue;
            }

            DB::table('password_hapus')->insert([
                'keterangan' => 'Dari data lama',
                'password' => Hash::make($p->password),
                'password_terlihat' => Crypt::encryptString($p->password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $jumlah++;
        }

        $this->line('Password hapus  : ' . $jumlah);
    }

    private function importKategori($lama)
    {
        foreach ($lama->table('ms_kategori')->orderBy('id_kategori')->get() as $k) {
            DB::table('kategori')->insert([
                'id' => $k->id_kategori,
                'kode' => trim($k->kode_kategori),
                'nama' => trim($k->nama_kategori),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->idKategori[$this->kunci($k->nama_kategori)] = $k->id_kategori;
        }

        $this->line('Kategori        : ' . count($this->idKategori));
    }

    private function importSatuan($lama)
    {
        foreach ($lama->table('ms_satuan')->orderBy('id_satuan')->get() as $s) {
            DB::table('satuan')->insert([
                'id' => $s->id_satuan,
                'kode' => trim($s->kode_satuan),
                'nama' => trim($s->nama_satuan),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->idSatuan[$this->kunci($s->nama_satuan)] = $s->id_satuan;
        }

        $this->line('Satuan          : ' . count($this->idSatuan));
    }

    private function importSupplier($lama)
    {
        foreach ($lama->table('ms_supplier')->orderBy('id_supplier')->get() as $s) {
            DB::table('supplier')->insert([
                'id' => $s->id_supplier,
                'perusahaan' => trim($s->perusahaan),
                'pic' => trim($s->pic),
                'alamat' => $s->alamat,
                'telp' => $s->telp,
                'hp' => $s->hp,
                'email' => $s->email,
                'bank' => $s->bank,
                'rek' => $s->rek,
                'atasnama' => $s->atasnama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->idSupplier[$this->kunci($s->perusahaan)] = $s->id_supplier;
        }

        $this->line('Supplier        : ' . count($this->idSupplier));
    }

    private function importBarang($lama)
    {
        $dilewati = 0;

        // chunk(500): data dibaca 500 baris sekali jalan, supaya memori tidak penuh
        $lama->table('barang_masuk')->orderBy('id_barang_masuk')->chunk(500, function ($baris) use (&$dilewati) {
            $data = [];

            foreach ($baris as $b) {
                $kode = trim($b->kode_barang_masuk);

                // Di data lama ada kode barang yang dobel (form tersubmit 2x).
                // Yang dipakai hanya baris pertama, sisanya dilewati.
                if (isset($this->idBarang[$kode])) {
                    $dilewati++;

                    continue;
                }

                $tanggalMasuk = $this->tambahDuaJam($b->tanggal_masuk) ?? date('Y-m-d H:i:s');

                $data[] = [
                    'id' => $b->id_barang_masuk,
                    'kode' => $kode,
                    'nama' => $b->nama_barang,
                    'kategori_id' => $this->idKategori[$this->kunci($b->kategori)] ?? null,
                    'satuan_id' => $this->idSatuan[$this->kunci($b->satuan)] ?? null,
                    'supplier_id' => $this->idSupplier[$this->kunci($b->pic_supplier)] ?? null,
                    'qty' => $b->qty,
                    'harga_distributor' => $b->harga_distributor ?? 0,
                    'harga_member' => $b->harga_jual_min ?? 0,
                    'harga_user' => $b->harga_jual_max ?? 0,
                    'harga_ongkir' => $b->harga_jual_ongkir ?? 0,
                    'tanggal_masuk' => $tanggalMasuk,
                    'created_by' => $this->cariUser($b->user),
                    'updated_by' => null,
                    'deleted_by' => $b->deleted_at ? $this->idUserKalauAda($b->deleted_user_id) : null,
                    'created_at' => $tanggalMasuk,
                    'updated_at' => $b->updated_at ?? $tanggalMasuk,
                    'deleted_at' => $b->deleted_at,
                ];

                $this->idBarang[$kode] = $b->id_barang_masuk;
            }

            DB::table('barang')->insert($data);
        });

        $this->line('Barang          : ' . count($this->idBarang) . '  (kode dobel dilewati: ' . $dilewati . ')');
    }

    private function importPenjualan($lama)
    {
        $dilewati = 0;

        $lama->table('penjualan')->orderBy('id_penjualan')->chunk(500, function ($baris) use (&$dilewati) {
            $data = [];

            foreach ($baris as $p) {
                $kode = trim($p->kode_transaksi);

                // Di data lama ada kode transaksi yang dobel (tombol checkout terklik 2x).
                // Baris kedua tidak punya daftar barang dan tidak mengurangi stok, jadi dilewati.
                if (isset($this->idPenjualan[$kode])) {
                    $dilewati++;

                    continue;
                }

                $tanggal = $this->tambahDuaJam($p->tanggal_transaksi) ?? date('Y-m-d H:i:s');

                $data[] = [
                    'id' => $p->id_penjualan,
                    'kode_transaksi' => $kode,
                    'tanggal' => $tanggal,
                    'total' => $p->total,
                    'keuntungan' => $p->keuntungan_transaksi,
                    'keterangan' => $p->keterangan_transaksi,
                    'sales_id' => $this->cariUser($p->sales),
                    'kasir_id' => $this->cariUser($p->pic),
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ];

                $this->idPenjualan[$kode] = $p->id_penjualan;
            }

            DB::table('penjualan')->insert($data);
        });

        $this->line('Penjualan       : ' . count($this->idPenjualan) . '  (transaksi dobel dilewati: ' . $dilewati . ')');
    }

    private function importPenjualanDetail($lama)
    {
        $jumlah = 0;
        $tanpaBarang = 0;

        $lama->table('penjualan_list')->orderBy('id_list_penjualan')->chunk(500, function ($baris) use (&$jumlah, &$tanpaBarang) {
            $data = [];

            foreach ($baris as $d) {
                $penjualanId = $this->idPenjualan[trim($d->kode_transaksi)] ?? null;
                if ($penjualanId == null) {
                    continue; // detail tanpa transaksi induk, tidak bisa dipakai
                }

                // Barangnya mungkin sudah tidak ada di data lama. Detail tetap disimpan (nama barang masih ada).
                $barangId = $this->idBarang[trim($d->kode_barang)] ?? null;
                if ($barangId == null) {
                    $tanpaBarang++;
                }

                // Data lama hanya menyimpan total harga per baris. Harga satuan = total / qty.
                $hargaSatuan = $d->qty_pembelian > 0 ? intdiv($d->harga_pembelian, $d->qty_pembelian) : $d->harga_pembelian;

                $data[] = [
                    'id' => $d->id_list_penjualan,
                    'penjualan_id' => $penjualanId,
                    'barang_id' => $barangId,
                    'nama_barang' => $d->nama_barang,
                    'qty' => $d->qty_pembelian,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $d->harga_pembelian,
                    'keuntungan' => $d->keuntungan,
                ];
                $jumlah++;
            }

            DB::table('penjualan_detail')->insert($data);
        });

        $this->line('Penjualan detail: ' . $jumlah . '  (barangnya sudah tidak ada: ' . $tanpaBarang . ')');
    }

    private function importRetur($lama)
    {
        $jumlah = 0;
        $dilewati = 0;

        foreach ($lama->table('barang_retur')->orderBy('id_barang_retur')->get() as $r) {
            $barangId = $this->idBarang[trim($r->kode_barang_masuk)] ?? null;
            if ($barangId == null) {
                $dilewati++;

                continue;
            }

            $tanggalRetur = $this->tambahDuaJam($r->tanggal_retur) ?? date('Y-m-d H:i:s');

            DB::table('retur')->insert([
                'id' => $r->id_barang_retur,
                'kode' => 'RTN' . str_pad($r->id_barang_retur, 3, '0', STR_PAD_LEFT),
                'barang_id' => $barangId,
                'qty' => $r->qty_retur,
                'keterangan' => $r->keterangan_retur,
                'tanggal_retur' => $tanggalRetur,
                'sudah_kembali' => $r->status_return == 1 ? 1 : 0,
                'tanggal_kembali' => $this->tambahDuaJam($r->date_return),
                'user_id' => $this->cariUser($r->user),
                'created_at' => $tanggalRetur,
                'updated_at' => $tanggalRetur,
            ]);
            $jumlah++;
        }

        $this->line('Retur           : ' . $jumlah . '  (dilewati karena barangnya tidak ada: ' . $dilewati . ')');
    }

    private function importNota($lama)
    {
        $jumlah = 0;

        foreach ($lama->table('nota_jatuh_tempo')->orderBy('id_nota')->get() as $n) {
            $tanggalInput = $this->tambahDuaJam($n->date_input);

            DB::table('nota_jatuh_tempo')->insert([
                'id' => $n->id_nota,
                'nomor_nota' => $n->nomor_nota,
                'tanggal_jatuh_tempo' => $n->tanggal_jatuh_tempo,
                'informasi' => $n->informasi,
                'nominal' => $n->nominal,
                'user_id' => $this->cariUser($n->pic),
                'created_at' => $tanggalInput,
                'updated_at' => $tanggalInput,
            ]);
            $jumlah++;
        }

        $this->line('Nota jatuh tempo: ' . $jumlah);
    }

    private function importLabaRugi($lama)
    {
        $jumlah = 0;
        $sudahAda = [];

        foreach ($lama->table('ms_laba_rugi')->orderBy('id_laba_rugi')->get() as $l) {
            if ($l->periode == null || isset($sudahAda[$l->periode])) {
                continue;
            }
            $sudahAda[$l->periode] = true;

            // Di program lama, isi 2 kolom ini tertukar:
            //   kolom harga_pokok_penjualan berisi total KEUNTUNGAN
            //   kolom laba_kotor            berisi total MODAL (penjualan - keuntungan)
            // Di sini dibetulkan, lalu laba bersih dihitung ulang.
            $labaKotor = (int) $l->harga_pokok_penjualan;
            $hargaPokok = (int) $l->laba_kotor;
            $totalBiaya = (int) $l->gaji + (int) $l->listrik + (int) $l->operasional;
            $tanggalInput = $this->tambahDuaJam($l->input_date);

            DB::table('laba_rugi')->insert([
                'periode' => $l->periode,
                'total_penjualan' => (int) $l->total_penjualan,
                'harga_pokok_penjualan' => $hargaPokok,
                'laba_kotor' => $labaKotor,
                'gaji' => (int) $l->gaji,
                'listrik' => (int) $l->listrik,
                'operasional' => (int) $l->operasional,
                'total_biaya' => $totalBiaya,
                'laba_bersih' => $labaKotor - $totalBiaya,
                'user_id' => $this->cariUser($l->user),
                'created_at' => $tanggalInput,
                'updated_at' => $tanggalInput,
            ]);
            $jumlah++;
        }

        $this->line('Laba rugi       : ' . $jumlah);
    }

    // Nomor urut transaksi baru melanjutkan nomor terakhir di program lama.
    // Program lama menyimpan nomor terakhir di tabel ms_parameter.
    // Program baru memakai ID penjualan sebagai nomor urut, jadi ID berikutnya diatur di sini.
    private function lanjutkanNomorTransaksi($lama)
    {
        $nomorTerakhir = (int) $lama->table('ms_parameter')->where('parameter', 'penjualan')->value('angka');
        $idTerakhir = (int) DB::table('penjualan')->max('id');

        $berikutnya = max($nomorTerakhir, $idTerakhir) + 1;

        DB::statement('ALTER TABLE penjualan AUTO_INCREMENT = ' . $berikutnya);

        $this->line('Nomor transaksi berikutnya: ' . $berikutnya);
    }

    // Riwayat awal tiap barang ("dibuat" dan "dihapus") diisi dari data barang yang baru diimport
    private function isiRiwayatBarang()
    {
        DB::statement("
            INSERT INTO barang_riwayat (barang_id, user_id, aksi, keterangan, created_at, updated_at)
            SELECT id, created_by, 'dibuat', 'Barang masuk (data sebelum fitur riwayat ada)', tanggal_masuk, tanggal_masuk
            FROM barang
        ");

        DB::statement("
            INSERT INTO barang_riwayat (barang_id, user_id, aksi, keterangan, created_at, updated_at)
            SELECT id, deleted_by, 'dihapus', 'Barang dihapus (data sebelum fitur riwayat ada)', deleted_at, deleted_at
            FROM barang
            WHERE deleted_at IS NOT NULL
        ");

        $this->line('Riwayat barang  : ' . DB::table('barang_riwayat')->count());
    }

    // Setiap penjualan menyimpan header dan footer struknya sendiri.
    // Penjualan dari data lama diisi dengan template struk awal.
    private function isiTemplateStrukPenjualan()
    {
        $template = \App\Models\TemplateStruk::ambil();

        DB::table('penjualan')->update([
            'struk_header' => $template->header,
            'struk_catatan' => $template->catatan,
            'struk_penutup' => $template->penutup,
        ]);
    }

    // ------------------------------------------------------------------
    // Fungsi bantuan
    // ------------------------------------------------------------------

    // Menyamakan teks supaya gampang dicocokkan: huruf kecil semua + spasi depan/belakang dibuang.
    // Contoh: "VISCOM " dan "viscom" dianggap sama.
    private function kunci($teks)
    {
        return strtolower(trim((string) $teks));
    }

    // Mencari ID user dari namanya (database lama menyimpan nama, bukan ID).
    // Kalau namanya tidak ada di tabel user (usernya sudah dihapus), dibuatkan user NONAKTIF
    // supaya riwayat penjualannya tetap punya nama.
    private function cariUser($nama)
    {
        $kunci = $this->kunci($nama);

        if ($kunci == '') {
            return null;
        }

        if (! isset($this->idUser[$kunci])) {
            $this->idUser[$kunci] = DB::table('users')->insertGetId([
                'name' => trim($nama),
                'username' => 'lama_' . Str::slug($nama, '_') . '_' . (count($this->idUser) + 1),
                'password' => Hash::make(Str::random(30)), // password acak, user ini memang tidak untuk login
                'role' => 'sales',
                'aktif' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->warn('User "' . trim($nama) . '" tidak ada di data lama, dibuatkan sebagai user nonaktif.');
        }

        return $this->idUser[$kunci];
    }

    // Mengembalikan ID user hanya kalau user itu memang ada (untuk kolom deleted_by)
    private function idUserKalauAda($id)
    {
        if ($id != null && in_array($id, $this->idUser)) {
            return $id;
        }

        return null;
    }

    // Program lama menyimpan jam memakai zona waktu yang salah (tertinggal 2 jam dari WITA),
    // lalu menambah 2 jam setiap kali ditampilkan. Di sini jamnya dibetulkan sekali saja,
    // jadi program baru tidak perlu menambah 2 jam lagi.
    private function tambahDuaJam($tanggal)
    {
        if ($tanggal == null || str_starts_with($tanggal, '0000')) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime($tanggal . ' +2 hour'));
    }
}
