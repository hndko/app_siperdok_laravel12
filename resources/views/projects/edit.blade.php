@extends('layouts.app-modules')

@section('title', 'Perbaiki / Edit Permohonan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-edit text-warning mr-2"></i> Perbaiki Dokumen / Edit Permohonan: {{ $project->project_number }}
                </h3>
            </div>
            <div class="card-body">
                @if($project->status === 'revision')
                    <div class="alert alert-warning mb-4">
                        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Catatan Revisi dari Penilai:</h5>
                        <p class="mb-0">{{ $project->assessmentLogs->where('action', 'request_revision')->first()->notes ?? 'Mohon periksa dan unggah dokumen perbaikan sesuai catatan penilai.' }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Judul Proyek / Permohonan <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $project->title) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Jenis Dokumen Kelayakan <span class="text-danger">*</span></label>
                        <select name="document_type_id" class="form-control" required>
                            @foreach($documentTypes as $dt)
                                <option value="{{ $dt->id }}" {{ old('document_type_id', $project->document_type_id) == $dt->id ? 'selected' : '' }}>
                                    {{ $dt->code }} - {{ $dt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Deskripsi & Rincian Pengajuan</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $project->description) }}</textarea>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold">Unggah Dokumen Perbaikan (Optional jika tidak diubah)</label>
                        <input type="file" name="document" class="form-control-file border p-2 rounded">
                        <small class="form-text text-muted">Format: PDF, DOCX, DOC, JPG, PNG (Maksimal 10MB per file).</small>
                        @if($project->documents->first())
                            <div class="mt-2 text-muted small">
                                File Terakhir: <i class="fas fa-file-pdf text-danger mr-1"></i> {{ $project->documents->first()->document_name }} (Versi {{ $project->documents->first()->version }})
                            </div>
                        @endif
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Batal</a>
                        <div>
                            @if($project->status === 'draft')
                                <button type="submit" name="submit_action" value="draft" class="btn btn-outline-primary mr-2">
                                    <i class="fas fa-save mr-1"></i> Simpan Draft
                                </button>
                            @endif
                            <button type="submit" name="submit_action" value="submit" class="btn btn-success font-weight-bold">
                                <i class="fas fa-paper-plane mr-1"></i> {{ $project->status === 'revision' ? 'Kirim Ulang Perbaikan' : 'Kirim Permohonan' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
