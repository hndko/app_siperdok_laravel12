@extends('layouts.app-modules')

@section('title', 'Penilaian Permohonan Dokumen')

@section('content')
<div class="card card-outline card-warning shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-tasks text-warning mr-2"></i> Daftar Permohonan Masuk untuk Penilaian</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('assessments.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari Permohonan / Pemohon / Perusahaan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">-- Semua Status Penilaian --</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Telah Dikirim (Pending)</option>
                        <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>Dalam Proses Penilaian</option>
                        <option value="revision" {{ request('status') === 'revision' ? 'selected' : '' }}>Perlu Revisi</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
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
                    <button type="submit" class="btn btn-warning btn-block font-weight-bold"><i class="fas fa-search mr-1"></i> Cari</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped border align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No. Permohonan</th>
                        <th>Judul Permohonan</th>
                        <th>Pemohon / Perusahaan</th>
                        <th>Jenis Dokumen</th>
                        <th>Status</th>
                        <th>Tgl Pengajuan</th>
                        <th class="text-center">Aksi Penilaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $prj)
                        <tr>
                            <td class="font-weight-bold text-primary">{{ $prj->project_number }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $prj->title }}</div>
                                <small class="text-muted">{{ Str::limit($prj->description, 50) }}</small>
                            </td>
                            <td>
                                <div>{{ $prj->applicant->name ?? 'Pemohon' }}</div>
                                <small class="text-muted">{{ $prj->applicant->company_name ?? '-' }}</small>
                            </td>
                            <td><span class="badge badge-light border">{{ $prj->documentType->code ?? '-' }}</span></td>
                            <td>
                                @php
                                    $badgeClass = match($prj->status) {
                                        'submitted' => 'badge-submitted',
                                        'in_review' => 'badge-in_review',
                                        'revision' => 'badge-revision',
                                        'approved' => 'badge-approved',
                                        'rejected' => 'badge-rejected',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1 text-capitalize">{{ $prj->status }}</span>
                            </td>
                            <td class="small">{{ $prj->submitted_at ? $prj->submitted_at->format('d M Y H:i') : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('assessments.review', $prj->id) }}" class="btn btn-primary btn-sm font-weight-bold">
                                    <i class="fas fa-search mr-1"></i> Review & Nilai
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada permohonan dokumen yang memerlukan penilaian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="small text-muted">Menampilkan {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} dari {{ number_format($projects->total()) }} data</div>
            <div>{{ $projects->links() }}</div>
        </div>
    </div>
</div>
@endsection
