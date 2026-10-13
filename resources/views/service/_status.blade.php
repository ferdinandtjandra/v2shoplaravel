{{-- Label status service berwarna. Butuh data: $service --}}
@if ($service->status == 'diterima')
    <span class="badge bg-label-secondary">Diterima</span>
@elseif ($service->status == 'dikerjakan')
    <span class="badge bg-label-primary">Dikerjakan</span>
@elseif ($service->status == 'selesai')
    <span class="badge bg-label-warning">Selesai, belum diambil</span>
@elseif ($service->status == 'diambil')
    <span class="badge bg-label-success">Sudah diambil</span>
@else
    <span class="badge bg-label-danger">Batal</span>
@endif
