@extends('layouts.app-modules')

@section('title', 'Detail Permohonan: ' . $project->project_number)

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Main Project Info Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-file-alt text-primary mr-2"></i> {{ $project->project_number }}
                </h3>
                <div>
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
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-2 text-capitalize h6 mb-0">{{ $project->status }}</span>
                </div>
            </div>
            <div class="card-body">
                <h4 class="font-weight-bold text-dark mb-3">{{ $project->title }}</h4>
                <p class="text-muted">{{ $project->description ?? 'Tidak ada deskripsi rincian.' }}</p>

                <div class="row bg-light p-3 rounded mb-4">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Jenis Dokumen:</small>
                        <strong>{{ $project->documentType->code ?? '-' }} - {{ $project->documentType->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Pemohon / Instansi:</small>
                        <strong>{{ $project->applicant->name ?? '-' }} ({{ $project->applicant->company_name ?? '-' }})</strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Penilai Dokumen:</small>
                        <strong>{{ $project->evaluator->name ?? 'Belum Ditugaskan' }}</strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Tanggal Pengajuan:</small>
                        <strong>{{ $project->submitted_at ? $project->submitted_at->format('d F Y, H:i') : 'Draft' }}</strong>
                    </div>
                </div>

                <h5 class="font-weight-bold mb-3"><i class="fas fa-paperclip text-secondary mr-2"></i> Berkas Dokumen Pendukung</h5>
                <div class="list-group mb-4">
                    @forelse($project->documents as $doc)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf text-danger fa-lg mr-2"></i>
                                <span class="font-weight-bold">{{ $doc->document_name }}</span>
                                <span class="badge badge-info ml-2">Versi {{ $doc->version }}</span>
                                <small class="text-muted d-block mt-1">Diunggah pada {{ $doc->created_at->format('d M Y H:i') }} ({{ number_format($doc->file_size / 1024, 1) }} KB)</small>
                            </div>
                            <div>
                                @if(Storage::disk('public')->exists($doc->file_path))
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download mr-1"></i> Unduh Berkas
                                    </a>
                                @else
                                    <span class="badge badge-secondary p-2"><i class="fas fa-check-double mr-1"></i> Terverifikasi Sistem</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-secondary mb-0">Belum ada dokumen yang diunggah.</div>
                    @endforelse
                </div>

                @if($project->status === 'approved')
                    <div class="p-3 bg-success-soft border border-success rounded text-center mb-3">
                        <i class="fas fa-award text-success fa-3x mb-2"></i>
                        <h5 class="font-weight-bold text-success">DOKUMEN TELAH DISETUJUI & DITERBITKAN</h5>
                        <p class="small text-muted mb-3">Dokumen kelayakan ini telah memenuhi seluruh kriteria dan disahkan oleh Penilai.</p>
                        <a href="{{ route('export.certificate.pdf', $project->id) }}" class="btn btn-success font-weight-bold">
                            <i class="fas fa-file-pdf mr-1"></i> Unduh Surat Pengesahan Dokumen (PDF)
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                <div>
                    @if(in_array($project->status, ['draft', 'revision']) && Auth::user()->id === $project->applicant_id)
                        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning font-weight-bold">
                            <i class="fas fa-edit mr-1"></i> {{ $project->status === 'revision' ? 'Perbaiki Dokumen (Submit Ulang)' : 'Edit Draft' }}
                        </a>
                    @endif
                    @role('penilai|admin')
                        <a href="{{ route('assessments.review', $project->id) }}" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-tasks mr-1"></i> Lakukan Penilaian Dokumen
                        </a>
                    @endrole
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Timeline / History Log -->
    <div class="col-md-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-history text-info mr-2"></i> Riwayat Proses & Audit Log</h3>
            </div>
            <div class="card-body p-3">
                <div class="timeline timeline-inverse">
                    @forelse($project->assessmentLogs as $log)
                        <div>
                            @php
                                $iconClass = match($log->action) {
                                    'create_draft' => 'fas fa-pen bg-secondary',
                                    'submit' => 'fas fa-paper-plane bg-info',
                                    'start_review' => 'fas fa-search bg-warning',
                                    'request_revision' => 'fas fa-exclamation-triangle bg-orange',
                                    'approve' => 'fas fa-check bg-success',
                                    'reject' => 'fas fa-times bg-danger',
                                    'resubmit' => 'fas fa-redo bg-primary',
                                    default => 'fas fa-info bg-secondary'
                                };
                            @endphp
                            <i class="{{ $iconClass }}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $log->created_at->diffForHumans() }}</span>
                                <h3 class="timeline-header font-weight-bold text-sm">
                                    {{ strtoupper($log->action) }} oleh <span class="text-primary">{{ $log->user->name ?? 'Sistem' }}</span>
                                </h3>
                                @if($log->notes)
                                    <div class="timeline-body small text-secondary">
                                        "{{ $log->notes }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">Belum ada riwayat proses.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
