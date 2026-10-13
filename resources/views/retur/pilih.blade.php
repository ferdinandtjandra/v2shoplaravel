@extends('layouts.app')

@section('judul', 'Retur - Pilih Nama Barang')

@section('isi')
<div class="card mb-6">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped datatable">
            <thead>
                <tr><th>No</th><th>Kode Stock</th><th>Nama Barang</th><th>Kategori</th>@if (Auth::user()->isBos()) <th>Supplier</th> @endif<th>Qty</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach ($daftar as $barang)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $barang->kode }}</td>
                        <td>{{ $barang->nama }}</td>
                        <td>{{ $barang->kategori->nama ?? '-' }}</td>
                        {{-- Supplier hanya terlihat oleh Bos, sama seperti di Laporan Stok --}}
                        @if (Auth::user()->isBos()) <td>{{ $barang->supplier->perusahaan ?? '-' }}</td> @endif
                        <td>{{ $barang->qty }}</td>
                        <td><a href="{{ route('retur.create', $barang->id) }}" class="btn btn-success btn-sm">Retur Barang Ini</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
