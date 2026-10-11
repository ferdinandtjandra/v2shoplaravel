@extends('layouts.app')

@section('judul', 'Master Supplier')

@section('isi')
<div class="card mb-6">
    <h5 class="card-header">Tambah Supplier</h5>
    <div class="card-body">
        <form method="POST" action="{{ route('supplier.store') }}">
            @csrf
            @include('supplier._form', ['supplier' => null])
            <div class="d-flex justify-content-end gap-2">
                <button type="reset" class="btn btn-outline-secondary">Kosongkan</button>
                <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Supplier</h5>
        <span class="badge bg-label-primary">{{ $daftar->count() }} data</span>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover datatable">
            <thead>
                <tr><th>No</th><th>Supplier</th><th>Kontak</th><th>Alamat</th><th>Rekening</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach ($daftar as $baris)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-medium">{{ $baris->perusahaan }}</div>
                            <small class="text-muted">PIC: {{ $baris->pic }}</small>
                        </td>
                        <td>
                            <div>{{ $baris->hp }}</div>
                            <small class="text-muted">{{ $baris->telp }} {{ $baris->email }}</small>
                        </td>
                        <td>{{ $baris->alamat }}</td>
                        <td>
                            @if ($baris->rek)
                                <div>{{ $baris->bank }} {{ $baris->rek }}</div>
                                <small class="text-muted">a/n {{ $baris->atasnama }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('supplier.edit', $baris->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="icon-base bx bx-edit"></i></a>
                            <form method="POST" action="{{ route('supplier.destroy', $baris->id) }}" class="d-inline konfirmasi" data-pesan="Hapus supplier {{ $baris->perusahaan }}?">
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
@endsection
