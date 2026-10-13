@extends('layouts.app')

@section('judul', 'Template Struk')

@section('isi')
<div class="row">

    {{-- KIRI: form --}}
    <div class="col-lg-6">
        <div class="card mb-6">
            <h5 class="card-header">Tulisan di Struk</h5>
            <div class="card-body">
                <div class="alert alert-primary small" role="alert">
                    Perubahan di sini hanya berlaku untuk transaksi <strong>berikutnya</strong>. Struk transaksi yang sudah ada tetap memakai tulisan saat transaksi itu dibuat.
                </div>
                <form method="POST" action="{{ route('template_struk.update') }}">
                    @csrf
                    @method('PUT')

                    <h6 class="text-muted text-uppercase small mb-3">Header (bagian atas)</h6>
                    <div class="mb-4">
                        <label class="form-label">Nama toko, alamat, no. HP <span class="text-danger">*</span></label>
                        <textarea name="header" id="inputHeader" class="form-control" rows="4" required>{{ old('header', $template->header) }}</textarea>
                        <div class="form-text">Tekan Enter untuk baris baru. Tanggal dan kode transaksi otomatis muncul di bawahnya.</div>
                    </div>

                    <h6 class="text-muted text-uppercase small mb-3 mt-2">Footer (bagian bawah)</h6>
                    <div class="mb-4">
                        <label class="form-label">Catatan di bawah daftar barang</label>
                        <textarea name="catatan" id="inputCatatan" class="form-control" rows="4">{{ old('catatan', $template->catatan) }}</textarea>
                        <div class="form-text">Boleh dikosongkan. Satu baris untuk satu poin, awali dengan tanda "-" supaya tampil sebagai poin.</div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Kalimat penutup</label>
                        <input type="text" name="penutup" id="inputPenutup" class="form-control" value="{{ old('penutup', $template->penutup) }}" maxlength="100">
                    </div>

                    <h6 class="text-muted text-uppercase small mb-3 mt-2">Service</h6>
                    <div class="mb-4">
                        <label class="form-label">Catatan di surat tanda terima service</label>
                        <textarea name="catatan_tanda_terima" class="form-control" rows="4">{{ old('catatan_tanda_terima', $template->catatan_tanda_terima) }}</textarea>
                        <div class="form-text">Syarat toko saat HP ditinggal. Satu baris untuk satu poin, awali dengan tanda "-".</div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Catatan di nota service</label>
                        <textarea name="catatan_nota_service" class="form-control" rows="3">{{ old('catatan_nota_service', $template->catatan_nota_service) }}</textarea>
                        <div class="form-text">Misalnya syarat garansi service. Header dan kalimat penutup memakai yang di atas.</div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- KANAN: contoh struk, langsung berubah saat mengetik --}}
    <div class="col-lg-6">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Preview</h5>
                <small class="text-muted">Barang dan harga hanya contoh</small>
            </div>
            <div class="card-body">
                @include('penjualan._struk', ['template' => $template, 'penjualan' => $contoh])
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // Menyalin isi kotak ketik ke preview struk.
    // .text() dipakai supaya huruf seperti < dan > aman, lalu Enter diubah menjadi baris baru (<br>).
    function salinKePreview(idInput, idPreview) {
        var teksAman = $('<div>').text($(idInput).val()).html();
        $(idPreview).html(teksAman.replace(/\n/g, '<br>'));
    }

    // Catatan: tiap baris menjadi 1 blok, sama seperti di struk asli.
    // Baris yang diawali "-" diberi class "poin" supaya lanjutan kalimatnya lurus dengan tulisan.
    function salinCatatanKePreview() {
        var kotak = $('#strukCatatan').empty();

        $('#inputCatatan').val().split('\n').forEach(function (baris) {
            baris = baris.trim();

            if (baris == '') {
                return; // baris kosong dilewati
            }

            // .text() dipakai supaya huruf seperti < dan > aman
            $('<div>').addClass(baris.charAt(0) == '-' ? 'poin' : '').text(baris).appendTo(kotak);
        });
    }

    function perbaruiPreview() {
        salinKePreview('#inputHeader', '#strukHeader');
        salinCatatanKePreview();
        salinKePreview('#inputPenutup', '#strukPenutup');
    }

    $('#inputHeader, #inputCatatan, #inputPenutup').on('input', perbaruiPreview);
    perbaruiPreview();
</script>
@endsection
