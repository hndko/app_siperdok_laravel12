<template>
  <app-layout :page-title="`Detail Permohonan: ${project.project_number}`">
    <div class="row">
      <div class="col-md-8">
        <!-- Main Project Info Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-file-alt text-primary mr-2"></i> {{ project.project_number }}
            </h3>
            <div>
              <status-badge :status="project.status"></status-badge>
            </div>
          </div>
          <div class="card-body">
            <h4 class="font-weight-bold text-dark mb-3">{{ project.title }}</h4>
            <p class="text-muted">{{ project.description || 'Tidak ada deskripsi rincian.' }}</p>

            <div class="row bg-light p-3 rounded mb-4">
              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">Jenis Dokumen:</small>
                <strong>{{ project.document_type ? project.document_type.code : '-' }} - {{ project.document_type ? project.document_type.name : '-' }}</strong>
              </div>
              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">Pemohon / Instansi:</small>
                <strong>{{ project.applicant ? project.applicant.name : '-' }} ({{ project.applicant ? project.applicant.company_name : '-' }})</strong>
              </div>
              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">Penilai Dokumen:</small>
                <strong>{{ project.evaluator ? project.evaluator.name : 'Belum Ditugaskan' }}</strong>
              </div>
              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">Tanggal Pengajuan:</small>
                <strong>{{ project.submitted_at ? formatDate(project.submitted_at) : 'Draft' }}</strong>
              </div>
            </div>

            <h5 class="font-weight-bold mb-3"><i class="fas fa-paperclip text-secondary mr-2"></i> Berkas Dokumen Pendukung</h5>
            <div class="list-group mb-4">
              <div v-for="doc in project.documents" :key="doc.id" class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <i class="fas fa-file-pdf text-danger fa-lg mr-2"></i>
                  <span class="font-weight-bold">{{ doc.document_name }}</span>
                  <span class="badge badge-info ml-2">Versi {{ doc.version }}</span>
                  <small class="text-muted d-block mt-1">Diunggah pada {{ formatDate(doc.created_at) }} ({{ (doc.file_size / 1024).toFixed(1) }} KB)</small>
                </div>
                <div>
                  <a :href="`/storage/${doc.file_path}`" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download mr-1"></i> Unduh Berkas
                  </a>
                </div>
              </div>
              <div v-if="!project.documents || !project.documents.length" class="alert alert-secondary mb-0">Belum ada dokumen yang diunggah.</div>
            </div>

            <div v-if="project.status === 'approved'" class="p-3 bg-success-soft border border-success rounded text-center mb-3" style="background-color: #f8fff9;">
              <i class="fas fa-award text-success fa-3x mb-2"></i>
              <h5 class="font-weight-bold text-success">DOKUMEN TELAH DISETUJUI & DITERBITKAN</h5>
              <p class="small text-muted mb-3">Dokumen kelayakan ini telah memenuhi seluruh kriteria dan disahkan oleh Penilai.</p>
              <div class="d-flex justify-content-center gap-2">
                <Link :href="`/exports/projects/${project.id}/certificate/preview`" class="btn btn-outline-success font-weight-bold mr-2">
                  <i class="fas fa-eye mr-1"></i> Pratinjau Surat (Vue 3)
                </Link>
                <button type="button" class="btn btn-success font-weight-bold" @click="downloadCertificate">
                  <i class="fas fa-file-pdf mr-1"></i> Unduh Surat Pengesahan Dokumen (PDF)
                </button>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-between">
            <Link href="/projects" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</Link>
            <div>
              <Link v-if="['draft', 'revision'].includes(project.status) && user && user.id === project.applicant_id" :href="`/projects/${project.id}/edit`" class="btn btn-warning font-weight-bold">
                <i class="fas fa-edit mr-1"></i> {{ project.status === 'revision' ? 'Perbaiki Dokumen (Submit Ulang)' : 'Edit Draft' }}
              </Link>
              <Link v-if="userRole === 'penilai' || userRole === 'admin'" :href="`/assessments/${project.id}/review`" class="btn btn-primary font-weight-bold ml-2">
                <i class="fas fa-tasks mr-1"></i> Lakukan Penilaian Dokumen
              </Link>
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
              <div v-for="log in project.assessment_logs" :key="log.id">
                <i :class="getLogIcon(log.action)"></i>
                <div class="timeline-item">
                  <span class="time"><i class="far fa-clock"></i> {{ formatDate(log.created_at) }}</span>
                  <h3 class="timeline-header font-weight-bold text-sm">
                    {{ log.action.toUpperCase() }} oleh <span class="text-primary">{{ log.user ? log.user.name : 'Sistem' }}</span>
                  </h3>
                  <div v-if="log.notes" class="timeline-body small text-secondary">
                    "{{ log.notes }}"
                  </div>
                </div>
              </div>
              <p v-if="!project.assessment_logs || !project.assessment_logs.length" class="text-muted text-center py-3">Belum ada riwayat proses.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';

const props = defineProps({
  project: { type: Object, required: true }
});

const page = usePage();
const user = computed(() => page.props.auth ? page.props.auth.user : null);
const userRole = computed(() => page.props.auth ? page.props.auth.role : 'pemohon');

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getLogIcon = (action) => {
  switch (action) {
    case 'create_draft': return 'fas fa-pen bg-secondary';
    case 'submit': return 'fas fa-paper-plane bg-info';
    case 'start_review': return 'fas fa-search bg-warning';
    case 'request_revision': return 'fas fa-exclamation-triangle bg-orange';
    case 'approve': return 'fas fa-check bg-success';
    case 'reject': return 'fas fa-times bg-danger';
    case 'resubmit': return 'fas fa-redo bg-primary';
    default: return 'fas fa-info bg-secondary';
  }
};

const downloadCertificate = async () => {
  const response = await window.axios.get(`/api/v1/exports/projects/${props.project.id}/certificate`, {
    responseType: 'blob',
  });
  const url = URL.createObjectURL(new Blob([response.data]));
  const link = document.createElement('a');
  link.href = url;
  link.download = `Surat_Pengesahan_${props.project.project_number}.pdf`;
  link.click();
  URL.revokeObjectURL(url);
};
</script>
