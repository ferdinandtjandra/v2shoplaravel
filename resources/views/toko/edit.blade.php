@extends('layouts.app')

@section('judul', 'Edit Toko')

@section('isi')
<div class="row">
    <div class="col-lg-5">
        <div class="card mb-6">
            <h5 class="card-header">{{ $toko->nama }}</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('toko.update', $toko->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-6">
                        <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $toko->nama) }}" maxlength="100" required>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('toko.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
