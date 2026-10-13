{{-- Kartu ringkasan Service di dashboard (Bos dan staff).
     Butuh data: $serviceAktif (jumlah yang sedang dikerjakan), $serviceSiapDiambil (daftar yang selesai tapi belum diambil) --}}
<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Service</h5>
        <a href="{{ route('service.index') }}" class="small">Lihat semua</a>
    </div>
    <div class="card-body pb-2">
        <div class="d-flex gap-2 mb-2">
            <span class="badge bg-label-primary">{{ $serviceAktif }} sedang dikerjakan</span>
            <span class="badge bg-label-warning">{{ $serviceSiapDiambil->count() }} selesai, belum diambil</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover tabel-padat mb-0">
            <tbody>
                {{-- Yang paling lama menunggu di atas --}}
                @forelse ($serviceSiapDiambil->take(5) as $service)
                    <tr>
                        <td>
                            <a href="{{ route('service.show', $service->id) }}" class="fw-medium">{{ $service->merk_tipe }}</a>
                            <small class="text-muted d-block">{{ $service->nama_pelanggan }} · {{ $service->kode }}</small>
                        </td>
                        <td class="text-end text-nowrap">
                            <span class="badge bg-label-warning">menunggu {{ (int) $service->tanggal_selesai->diffInDays(now()) }} hari</span>
                        </td>
                    </tr>
                @empty
                    <tr><td class="text-center text-muted py-4">Tidak ada HP yang menunggu diambil.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
