@extends('layouts.app-modules')

@section('title', 'Histori Penilaian Dokumen')

@section('content')
<div class="card card-outline card-cyan shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-history text-cyan mr-2"></i> Log Audit Seluruh Histori Penilaian Dokumen</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('assessments.history') }}" class="mb-4">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari No. Permohonan / Judul Proyek..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-2">
                    <select name="action" class="form-control">
                        <option value="">-- Semua Aksi Evaluasi --</option>
                        <option value="submit" {{ request('action') === 'submit' ? 'selected' : '' }}>Submit Pengajuan</option>
                        <option value="request_revision" {{ request('action') === 'request_revision' ? 'selected' : '' }}>Request Revisi</option>
                        <option value="approve" {{ request('action') === 'approve' ? 'selected' : '' }}>Disetujui (Approve)</option>
                        <option value="reject" {{ request('action') === 'reject' ? 'selected' : '' }}>Ditolak (Reject)</option>
                        <option value="resubmit" {{ request('action') === 'resubmit' ? 'selected' : '' }}>Submit Ulang Perbaikan</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-cyan text-white btn-block font-weight-bold" style="background-color: #17a2b8;"><i class="fas fa-filter mr-1"></i> Filter Log</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped border align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Waktu Log</th>
                        <th>No. Permohonan</th>
                        <th>Pemohon / Perusahaan</th>
                        <th>User Pelaksana</th>
                        <th>Aksi</th>
                        <th>Status Baru</th>
                        <th>Catatan Penilaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="font-weight-bold text-primary">
                                <a href="{{ route('projects.show', $log->project_id) }}">{{ $log->project->project_number ?? '-' }}</a>
                            </td>
                            <td>{{ $log->project->applicant->name ?? '-' }}</td>
                            <td><span class="badge badge-light border">{{ $log->user->name ?? 'System' }}</span></td>
                            <td><span class="badge badge-info px-2 py-1">{{ strtoupper($log->action) }}</span></td>
                            <td><span class="badge badge-secondary px-2 py-1">{{ strtoupper($log->new_status) }}</span></td>
                            <td class="small">{{ Str::limit($log->notes, 80) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada histori penilaian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="small text-muted">Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ number_format($logs->total()) }} log</div>
            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</div>
@endsection
