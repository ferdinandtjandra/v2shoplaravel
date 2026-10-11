@extends('layouts.app')

@section('judul', 'Edit Kategori')

@section('isi')
<div class="row">
    <div class="col-lg-5">
        <div class="card mb-6">
            <h5 class="card-header">{{ $kategori->nama }}</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('kategori.update', $kategori->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" value="{{ old('kode', $kategori->kode) }}" maxlength="20" required>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $kategori->nama) }}" maxlength="100" required>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
