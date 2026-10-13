@extends('layouts.app')

{{-- Halaman ini dipakai untuk 2 hal: Terima Service Baru dan Ubah Data Service. --}}
{{-- $service->id kosong = service baru. $service->id ada isinya = ubah data. --}}
@section('judul', $service->id ? 'Ubah Data Service ' . $service->kode : 'Terima Service Baru')

@section('isi')
<form method="POST" action="{{ $service->id ? route('service.update', $service->id) : route('service.store') }}">
    @csrf
    @if ($service->id)
        @method('PUT')
    @endif

    <div class="row">

        {{-- KIRI: pelanggan dan HP-nya --}}
        <div class="col-lg-6">
            <div class="card mb-6">
                <h5 class="card-header">Pelanggan dan HP</h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7 mb-4">
                            <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pelanggan" class="form-control" value="{{ old('nama_pelanggan', $service->nama_pelanggan) }}" maxlength="100" required autofocus>
                        </div>
                        <div class="col-md-5 mb-4">
                            <label class="form-label">No. HP Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" name="hp_pelanggan" class="form-control" value="{{ old('hp_pelanggan', $service->hp_pelanggan) }}" maxlength="30" placeholder="08xxxxxxxxxx" inputmode="tel" required>
                            <div class="form-text">Untuk menghubungi saat selesai.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7 mb-4">
                            <label class="form-label">Merk / Tipe HP <span class="text-danger">*</span></label>
                            <input type="text" name="merk_tipe" class="form-control" value="{{ old('merk_tipe', $service->merk_tipe) }}" maxlength="150" placeholder="Contoh: Samsung A51" required>
                        </div>
                        <div class="col-md-5 mb-4">
                            <label class="form-label">IMEI</label>
                            <input type="text" name="imei" class="form-control" value="{{ old('imei', $service->imei) }}" maxlength="50" placeholder="opsional">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Kelengkapan yang ditinggal</label>
                        <input type="text" name="kelengkapan" class="form-control" value="{{ old('kelengkapan', $service->kelengkapan) }}" maxlength="255" placeholder="Contoh: kartu SIM, memori, casing">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Kondisi fisik saat diterima</label>
                        <input type="text" name="kondisi_fisik" class="form-control" value="{{ old('kondisi_fisik', $service->kondisi_fisik) }}" maxlength="255" placeholder="Contoh: layar retak, bodi lecet">
                        <div class="form-text">Dicatat supaya tidak jadi perdebatan saat HP diambil.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: kerusakan dan perkiraan --}}
        <div class="col-lg-6">
            <div class="card mb-6">
                <h5 class="card-header">Kerusakan dan Perkiraan</h5>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Keluhan pelanggan <span class="text-danger">*</span></label>
                        <textarea name="keluhan" class="form-control" rows="2" maxlength="1000" placeholder="Contoh: HP mati total setelah jatuh" required>{{ old('keluhan', $service->keluhan) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Kerusakan (hasil pengecekan)</label>
                        <textarea name="kerusakan" class="form-control" rows="2" maxlength="1000" placeholder="Contoh: LCD pecah, konektor charger longgar">{{ old('kerusakan', $service->kerusakan) }}</textarea>
                        <div class="form-text">Boleh diisi belakangan lewat tombol Ubah Data.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Perkiraan biaya</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="perkiraan_biaya" class="form-control rupiah" value="{{ old('perkiraan_biaya', $service->perkiraan_biaya ?: '') }}" inputmode="numeric" placeholder="opsional">
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Perkiraan selesai</label>
                            <input type="date" name="perkiraan_selesai" class="form-control" value="{{ old('perkiraan_selesai', $service->perkiraan_selesai ? $service->perkiraan_selesai->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Teknisi yang mengerjakan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_teknisi" class="form-control" value="{{ old('nama_teknisi', $service->nama_teknisi) }}" maxlength="100" placeholder="Nama teknisi" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Catatan (tidak dicetak)</label>
                        <textarea name="catatan" class="form-control" rows="2" maxlength="1000" placeholder="opsional, untuk catatan sendiri">{{ old('catatan', $service->catatan) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-6">
        <a href="{{ $service->id ? route('service.show', $service->id) : route('service.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> {{ $service->id ? 'Simpan Perubahan' : 'Simpan dan Buat Tanda Terima' }}</button>
    </div>
</form>
@endsection
