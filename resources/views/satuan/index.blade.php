@extends('layouts.app')

@section('judul', 'Master Satuan')

@section('isi')
<div class="row">

    {{-- KIRI: form tambah --}}
    <div class="col-lg-4">
        <div class="card mb-6">
            <h5 class="card-header">Tambah Satuan</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('satuan.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Kode Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" value="{{ old('kode') }}" maxlength="20" placeholder="Contoh: LCD" required>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" maxlength="100" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- KANAN: daftar --}}
    <div class="col-lg-8">
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Satuan</h5>
                <span class="badge bg-label-primary">{{ $daftar->count() }} data</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr><th>No</th><th>Kode</th><th>Nama Satuan</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $baris)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-label-secondary">{{ $baris->kode }}</span></td>
                                <td>{{ $baris->nama }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('satuan.edit', $baris->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="icon-base bx bx-edit"></i></a>
                                    <form method="POST" action="{{ route('satuan.destroy', $baris->id) }}" class="d-inline konfirmasi" data-pesan="Hapus {{ $baris->nama }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="icon-base bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
