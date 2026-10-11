{{-- Kartu "Stok Paling Sedikit". Dipakai di dashboard Bos dan dashboard staff. Butuh data: $stokMenipis --}}
<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Stok Paling Sedikit</h5>
        <a href="{{ route('barang.index') }}" class="small">Lihat semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover tabel-padat mb-0">
            <tbody>
                @forelse ($stokMenipis as $barang)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $barang->nama }}</div>
                            <small class="text-muted">{{ $barang->kode }}</small>
                        </td>
                        <td class="text-end text-nowrap">
                            <span class="badge {{ $barang->qty <= 2 ? 'bg-label-warning' : 'bg-label-secondary' }}">sisa {{ $barang->qty }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td class="text-center text-muted py-6">Belum ada barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
