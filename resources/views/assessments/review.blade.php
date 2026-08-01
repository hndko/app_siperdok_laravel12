@extends('layouts.app-modules')

@section('title', 'Penilaian Dokumen: ' . $project->project_number)

@section('content')
<div class="row">
    <!-- Left Column: Project & Applicant Information -->
    <div class="col-md-7">
        <div class="card card-outline card-warning shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-file-signature text-warning mr-2"></i> Berkas & Informasi Permohonan</h3>
                <div class="card-tools">
                    <status-badge status="{{ $project->status }}"></status-badge>
                </div>
            </div>
            <div class="card-body">
                <h4 class="font-weight-bold text-dark mb-2">{{ $project->title }}</h4>
                <p class="text-muted small">No. Reg: <strong class="text-primary">{{ $project->project_number }}</strong> | Tgl Pengajuan: {{ $project->submitted_at ? $project->submitted_at->format('d M Y H:i') : '-' }}</p>

                <div class="card bg-light border-0 mb-4">
                    <div class="card-body p-3">
                        <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-building mr-1"></i> Data Pemohon & Perusahaan</h6>
                        <div class="row small">
                            <div class="col-6 mb-1"><strong>Nama Pemohon:</strong> {{ $project->applicant->name ?? '-' }}</div>
                            <div class="col-6 mb-1"><strong>Perusahaan:</strong> {{ $project->applicant->company_name ?? '-' }}</div>
                            <div class="col-6 mb-1"><strong>NIK / NIP:</strong> {{ $project->applicant->nip_nik ?? '-' }}</div>
                            <div class="col-6 mb-1"><strong>No. Telp / WA:</strong> {{ $project->applicant->phone ?? '-' }}</div>
                            <div class="col-12 mt-1"><strong>Email:</strong> {{ $project->applicant->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold">Rincian & Deskripsi Kegiatan Proyek:</label>
                    <div class="p-3 bg-white border rounded text-secondary">{{ $project->description ?? 'Tidak ada rincian.' }}</div>
                </div>

                <h5 class="font-weight-bold mb-3"><i class="fas fa-folder-open text-primary mr-2"></i> Berkas Dokumen Diunggah</h5>
                <div class="list-group mb-4">
                    @forelse($project->documents as $doc)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf text-danger fa-2x mr-3"></i>
                                <span class="font-weight-bold">{{ $doc->document_name }}</span>
                                <span class="badge badge-primary ml-2">Versi {{ $doc->version }}</span>
                                <small class="text-muted d-block mt-1">Diunggah: {{ $doc->created_at->format('d M Y H:i') }}</small>
                            </div>
                            <div>
                                @if(Storage::disk('public')->exists($doc->file_path))
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-primary btn-sm font-weight-bold">
                                        <i class="fas fa-download mr-1"></i> Unduh & Periksa
                                    </a>
                                @else
                                    <span class="badge badge-success p-2"><i class="fas fa-check-circle mr-1"></i> Dokumen Terverifikasi</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-secondary">Belum ada berkas dokumen.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Decision Action Form (Vue 3 Component) & Audit Trail -->
    <div class="col-md-5">
        <!-- Vue 3 Interactive Decision Component -->
        <decision-modal 
            action-url="{{ route('assessments.process', $project->id) }}" 
            csrf-token="{{ csrf_token() }}"
            class="mb-4"
        ></decision-modal>

        <!-- History Log Card -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i> Log Penilaian Terkait</h3>
            </div>
            <div class="card-body p-3">
                <div class="timeline timeline-inverse mb-0">
                    @foreach($project->assessmentLogs as $log)
                        <div>
                            <i class="fas fa-comment-dots bg-info"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $log->created_at->format('d M Y H:i') }}</span>
                                <h3 class="timeline-header font-weight-bold text-sm">{{ strtoupper($log->action) }}</h3>
                                <div class="timeline-body small text-muted">
                                    {{ $log->notes }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
