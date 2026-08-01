@extends('layouts.adminlte')

@section('title', 'Buat Permohonan Dokumen Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-file-signature text-success mr-2"></i> Form Permohonan Dokumen Kelayakan</h3>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Judul Proyek / Permohonan <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Permohonan AMDAL Pembangunan Kawasan Pabrik Tekstil" value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Jenis Dokumen Kelayakan <span class="text-danger">*</span></label>
                        <select name="document_type_id" class="form-control" required>
                            <option value="">-- Pilih Jenis Dokumen --</option>
                            @foreach($documentTypes as $dt)
                                <option value="{{ $dt->id }}" {{ old('document_type_id') == $dt->id ? 'selected' : '' }}>
                                    {{ $dt->code }} - {{ $dt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Deskripsi & Rincian Pengajuan</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan secara singkat mengenai latar belakang dan rencana usaha/kegiatan yang diajukan...">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold">Unggah Dokumen Berkas Permohonan <span class="text-danger">*</span></label>
                        <input type="file" name="document" class="form-control-file border p-2 rounded" required>
                        <small class="form-text text-muted">Format yang diperbolehkan: PDF, DOCX, DOC, JPG, PNG (Maksimal 10MB per file).</small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Batal</a>
                        <div>
                            <button type="submit" name="submit_action" value="draft" class="btn btn-outline-primary mr-2">
                                <i class="fas fa-save mr-1"></i> Simpan Sebagai Draft
                            </button>
                            <button type="submit" name="submit_action" value="submit" class="btn btn-success font-weight-bold">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Permohonan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
