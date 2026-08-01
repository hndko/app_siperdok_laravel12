@extends('layouts.adminlte')

@section('title', 'Daftar Permohonan Saya')

@section('content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-folder-open text-primary mr-2"></i> Daftar Permohonan Dokumen Kelayakan</h3>
        <div class="card-tools">
            <a href="{{ route('projects.create') }}" class="btn btn-success btn-sm font-weight-bold">
                <i class="fas fa-plus mr-1"></i> Buat Permohonan Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter & Search Form -->
        <form method="GET" action="{{ route('projects.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari No. Permohonan / Judul..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Telah Dikirim</option>
                        <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>Dalam Penilaian</option>
                        <option value="revision" {{ request('status') === 'revision' ? 'selected' : '' }}>Perlu Revisi</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="document_type_id" class="form-control">
                        <option value="">-- Semua Jenis Dokumen --</option>
                        @foreach($documentTypes as $dt)
                            <option value="{{ $dt->id }}" {{ request('document_type_id') == $dt->id ? 'selected' : '' }}>{{ $dt->code }} - {{ $dt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped border align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No. Permohonan</th>
                        <th>Judul Permohonan</th>
                        <th>Jenis Dokumen</th>
                        <th>Status</th>
                        <th>Tgl Pengajuan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $prj)
                        <tr>
                            <td class="font-weight-bold text-primary">{{ $prj->project_number }}</td>
                            <td>
                                <div>{{ $prj->title }}</div>
                                <small class="text-muted">{{ Str::limit($prj->description, 60) }}</small>
                            </td>
                            <td><span class="badge badge-light border">{{ $prj->documentType->code ?? '-' }}</span></td>
                            <td>
                                @php
                                    $badgeClass = match($prj->status) {
                                        'draft' => 'badge-draft',
                                        'submitted' => 'badge-submitted',
                                        'in_review' => 'badge-in_review',
                                        'revision' => 'badge-revision',
                                        'approved' => 'badge-approved',
                                        'rejected' => 'badge-rejected',
                                        default => 'badge-secondary'
                                    };
                                    $statusLabel = match($prj->status) {
                                        'draft' => 'Draft',
                                        'submitted' => 'Dikirim',
                                        'in_review' => 'Proses Penilaian',
                                        'revision' => 'Perlu Revisi',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        default => strtoupper($prj->status)
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">{{ $statusLabel }}</span>
                            </td>
                            <td class="small">{{ $prj->submitted_at ? $prj->submitted_at->format('d M Y H:i') : 'Draft (Belum Dikirim)' }}</td>
                            <td class="text-center">
                                <a href="{{ route('projects.show', $prj->id) }}" class="btn btn-info btn-xs mr-1"><i class="fas fa-eye"></i> Detail</a>
                                @if(in_array($prj->status, ['draft', 'revision']))
                                    <a href="{{ route('projects.edit', $prj->id) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i> {{ $prj->status === 'revision' ? 'Perbaiki' : 'Edit' }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada permohonan dokumen yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="small text-muted">Menampilkan {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} dari {{ number_format($projects->total()) }} permohonan</div>
            <div>{{ $projects->links() }}</div>
        </div>
    </div>
</div>
@endsection
