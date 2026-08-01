@extends('layouts.app-modules')

@section('title', 'Dashboard Monitoring')

@section('content')
<!-- KPI Info Boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-folder"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Permohonan</span>
                <span class="info-box-number h4 font-weight-bold mb-0">{{ number_format($totalProjects) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
            <span class="info-box-icon bg-warning text-dark elevation-1"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Dalam Penilaian</span>
                <span class="info-box-number h4 font-weight-bold mb-0">{{ number_format($pendingCount) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Disetujui (Approved)</span>
                <span class="info-box-number h4 font-weight-bold mb-0">{{ number_format($approvedCount) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
            <span class="info-box-icon bg-orange text-white elevation-1"><i class="fas fa-edit"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Perlu Revisi</span>
                <span class="info-box-number h4 font-weight-bold mb-0">{{ number_format($revisionCount) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-chart-line text-primary mr-2"></i> Tren Pengajuan Dokumen Bulanan</h3>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" style="min-height: 260px; height: 260px; max-height: 260px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-chart-pie text-info mr-2"></i> Distribusi Status Dokumen</h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="min-height: 260px; height: 260px; max-height: 260px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Projects Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-list text-secondary mr-2"></i> Permohonan Dokumen Terbaru</h3>
        <div class="card-tools">
            @role('pemohon')
                <a href="{{ route('projects.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Buat Permohonan Baru</a>
            @endrole
            @role('penilai|admin')
                <a href="{{ route('assessments.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-tasks mr-1"></i> Lihat Semua Penilaian</a>
            @endrole
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th>No. Permohonan</th>
                    <th>Judul Permohonan</th>
                    <th>Jenis Dokumen</th>
                    @role('penilai|admin') <th>Pemohon / Perusahaan</th> @endrole
                    <th>Status</th>
                    <th>Tanggal Update</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentProjects as $project)
                    <tr>
                        <td class="font-weight-bold text-primary">{{ $project->project_number }}</td>
                        <td>{{ Str::limit($project->title, 45) }}</td>
                        <td><span class="badge badge-light border">{{ $project->documentType->code ?? '-' }}</span></td>
                        @role('penilai|admin')
                            <td>
                                <div>{{ $project->applicant->name ?? 'Pemohon' }}</div>
                                <small class="text-muted">{{ $project->applicant->company_name ?? '-' }}</small>
                            </td>
                        @endrole
                        <td>
                            @php
                                $badgeClass = match($project->status) {
                                    'draft' => 'badge-draft',
                                    'submitted' => 'badge-submitted',
                                    'in_review' => 'badge-in_review',
                                    'revision' => 'badge-revision',
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    default => 'badge-secondary'
                                };
                                $statusLabel = match($project->status) {
                                    'draft' => 'Draft',
                                    'submitted' => 'Telah Dikirim',
                                    'in_review' => 'Dalam Penilaian',
                                    'revision' => 'Perlu Revisi',
                                    'approved' => 'Disetujui (Approved)',
                                    'rejected' => 'Ditolak (Rejected)',
                                    default => strtoupper($project->status)
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">{{ $statusLabel }}</span>
                        </td>
                        <td class="small">{{ $project->updated_at->format('d M Y H:i') }}</td>
                        <td class="text-center">
                            @role('penilai|admin')
                                <a href="{{ route('assessments.review', $project->id) }}" class="btn btn-primary btn-xs"><i class="fas fa-search mr-1"></i> Review</a>
                            @else
                                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-info btn-xs"><i class="fas fa-eye mr-1"></i> Detail</a>
                            @endrole
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada permohonan dokumen terbaru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // 1. Line Chart: Monthly Submissions
    var monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: {!! json_encode($chartValues) !!},
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{ ticks: { beginAtZero: true } }]
            }
        }
    });

    // 2. Doughnut Chart: Status Breakdown
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusCounts)) !!},
                backgroundColor: ['#6c757d', '#ffc107', '#fd7e14', '#28a745', '#dc3545'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { position: 'bottom' }
        }
    });
});
</script>
@endpush
