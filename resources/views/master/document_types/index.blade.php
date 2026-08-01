@extends('layouts.adminlte')

@section('title', 'Master Jenis Dokumen')

@section('content')
<div class="card card-outline card-info shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-file-alt text-info mr-2"></i> Master Jenis Dokumen Kelayakan</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped border align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Jenis Dokumen</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Jumlah Permohonan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($types as $type)
                        <tr>
                            <td><span class="badge badge-primary px-2 py-1 font-weight-bold">{{ $type->code }}</span></td>
                            <td class="font-weight-bold">{{ $type->name }}</td>
                            <td class="small text-muted">{{ $type->description }}</td>
                            <td>
                                <span class="badge {{ $type->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $type->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td><span class="badge badge-info">{{ number_format($type->projects_count) }} Proyek</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
