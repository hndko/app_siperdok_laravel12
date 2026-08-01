<template>
  <app-layout :page-title="`Penilaian Dokumen: ${project.project_number}`">
    <div class="row">
      <!-- Left Column: Project & Applicant Info -->
      <div class="col-md-7">
        <div class="card card-outline card-warning shadow-sm mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-file-signature text-warning mr-2"></i> Berkas & Informasi Permohonan
            </h3>
            <div>
              <status-badge :status="project.status"></status-badge>
            </div>
          </div>
          <div class="card-body">
            <h4 class="font-weight-bold text-dark mb-2">{{ project.title }}</h4>
            <p class="text-muted small">No. Reg: <strong class="text-primary">{{ project.project_number }}</strong> | Tgl Pengajuan: {{ project.submitted_at ? formatDate(project.submitted_at) : '-' }}</p>

            <div class="card bg-light border-0 mb-4">
              <div class="card-body p-3">
                <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-building mr-1"></i> Data Pemohon & Perusahaan</h6>
                <div class="row small">
                  <div class="col-6 mb-1"><strong>Nama Pemohon:</strong> {{ project.applicant ? project.applicant.name : '-' }}</div>
                  <div class="col-6 mb-1"><strong>Perusahaan:</strong> {{ project.applicant ? project.applicant.company_name : '-' }}</div>
                  <div class="col-6 mb-1"><strong>NIK / NIP:</strong> {{ project.applicant ? project.applicant.nip_nik : '-' }}</div>
                  <div class="col-6 mb-1"><strong>No. Telp / WA:</strong> {{ project.applicant ? project.applicant.phone : '-' }}</div>
                  <div class="col-12 mt-1"><strong>Email:</strong> {{ project.applicant ? project.applicant.email : '-' }}</div>
                </div>
              </div>
            </div>

            <div class="form-group mb-4">
              <label class="font-weight-bold">Rincian & Deskripsi Kegiatan Proyek:</label>
              <div class="p-3 bg-white border rounded text-secondary">{{ project.description || 'Tidak ada rincian.' }}</div>
            </div>

            <h5 class="font-weight-bold mb-3"><i class="fas fa-folder-open text-primary mr-2"></i> Berkas Dokumen Diunggah</h5>
            <div class="list-group mb-4">
              <div v-for="doc in project.documents" :key="doc.id" class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <i class="fas fa-file-pdf text-danger fa-2x mr-3"></i>
                  <span class="font-weight-bold">{{ doc.document_name }}</span>
                  <span class="badge badge-primary ml-2">Versi {{ doc.version }}</span>
                  <small class="text-muted d-block mt-1">Diunggah: {{ formatDate(doc.created_at) }}</small>
                </div>
                <div>
                  <a :href="`/storage/${doc.file_path}`" target="_blank" class="btn btn-primary btn-sm font-weight-bold">
                    <i class="fas fa-download mr-1"></i> Unduh & Periksa
                  </a>
                </div>
              </div>
              <div v-if="!project.documents || !project.documents.length" class="alert alert-secondary mb-0">Belum ada berkas dokumen.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Decision Action Form & Audit Trail -->
      <div class="col-md-5">
        <!-- Vue 3 Interactive Decision Form -->
        <div v-if="canStartReview" class="card card-outline card-info shadow-sm mb-4">
          <div class="card-header bg-light">
            <h3 class="card-title font-weight-bold text-info">
              <i class="fas fa-search mr-2"></i> Verifikasi Administrasi
            </h3>
          </div>
          <div class="card-body">
            <p class="text-muted small mb-3">Mulai review untuk mengunci permohonan ini ke penilai yang sedang memproses.</p>
            <button
              type="button"
              :disabled="startReviewForm.processing"
              class="btn btn-info btn-block font-weight-bold"
              @click="startReview"
            >
              <i class="fas fa-play mr-1"></i> {{ startReviewForm.processing ? 'Memulai...' : 'Mulai Review' }}
            </button>
          </div>
        </div>

        <div class="card card-outline card-success shadow-sm mb-4">
          <div class="card-header bg-light">
            <h3 class="card-title font-weight-bold text-success">
              <i class="fas fa-gavel mr-2"></i> Form Keputusan Penilaian
            </h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="submitDecision">
              <div class="form-group mb-3">
                <label class="font-weight-bold">Pilih Keputusan Penilaian <span class="text-danger">*</span></label>
                <div class="btn-group-toggle d-flex">
                  <button 
                    type="button" 
                    :class="['btn flex-fill mr-1 font-weight-bold', form.decision === 'approved' ? 'btn-success' : 'btn-outline-success']"
                    @click="form.decision = 'approved'"
                  >
                    <i class="fas fa-check-circle mr-1"></i> SETUJU
                  </button>

                  <button 
                    type="button" 
                    :class="['btn flex-fill mr-1 font-weight-bold', form.decision === 'revision' ? 'btn-warning' : 'btn-outline-warning']"
                    @click="form.decision = 'revision'"
                  >
                    <i class="fas fa-edit mr-1"></i> REVISI
                  </button>

                  <button 
                    type="button" 
                    :class="['btn flex-fill font-weight-bold', form.decision === 'rejected' ? 'btn-danger' : 'btn-outline-danger']"
                    @click="form.decision = 'rejected'"
                  >
                    <i class="fas fa-times-circle mr-1"></i> DITOLAK
                  </button>
                </div>
              </div>

              <div class="form-group mb-4">
                <label class="font-weight-bold">Catatan Penilai / Alasan Decision <span class="text-danger">*</span></label>
                <textarea 
                  v-model="form.notes" 
                  class="form-control" 
                  rows="5" 
                  placeholder="Tuliskan catatan evaluasi, poin revisi yang wajib diperbaiki, atau alasan penolakan..." 
                  required
                ></textarea>
              </div>

              <button 
                type="submit" 
                :disabled="form.processing || !canAssess || !form.notes.trim()"
                class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm"
              >
                <i class="fas fa-paper-plane mr-2"></i> {{ form.processing ? 'Memproses...' : 'Simpan Keputusan Penilaian' }}
              </button>
              <p v-if="!canAssess" class="text-muted small text-center mt-2 mb-0">
                Keputusan hanya dapat diberikan setelah review dimulai oleh penilai yang menangani.
              </p>
            </form>
          </div>
        </div>

        <!-- History Log Card -->
        <div class="card card-outline card-secondary shadow-sm">
          <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i> Log Penilaian Terkait</h3>
          </div>
          <div class="card-body p-3">
            <div class="timeline timeline-inverse mb-0">
              <div v-for="log in project.assessment_logs" :key="log.id">
                <i class="fas fa-comment-dots bg-info"></i>
                <div class="timeline-item">
                  <span class="time"><i class="far fa-clock"></i> {{ formatDate(log.created_at) }}</span>
                  <h3 class="timeline-header font-weight-bold text-sm">{{ log.action.toUpperCase() }}</h3>
                  <div class="timeline-body small text-muted">
                    "{{ log.notes }}"
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../layouts/AppLayout.vue';
import StatusBadge from '../../../components/StatusBadge.vue';

const props = defineProps({
  project: { type: Object, required: true },
  canStartReview: { type: Boolean, default: false },
  canAssess: { type: Boolean, default: false }
});
const project = ref(props.project);
const currentRole = ref('pemohon');
const canStartReview = computed(() => ['admin', 'penilai'].includes(currentRole.value) && project.value.status === 'submitted');
const canAssess = computed(() => ['admin', 'penilai'].includes(currentRole.value) && project.value.status === 'in_review');

const startReviewForm = useForm({
  notes: '',
});

const form = useForm({
  decision: 'approved',
  notes: '',
});

const startReview = async () => {
  await window.axios.post(`/api/v1/assessments/${project.value.id}/start-review`, {
    notes: startReviewForm.notes,
  });
  await loadProject();
};

const submitDecision = async () => {
  await window.axios.post(`/api/v1/assessments/${project.value.id}`, {
    decision: form.decision,
    notes: form.notes,
  });
  await loadProject();
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const loadProject = async () => {
  const segments = window.location.pathname.split('/').filter(Boolean);
  const id = segments[1];
  const response = await window.axios.get(`/api/v1/projects/${id}`);
  project.value = response.data.data;
};

const loadMe = async () => {
  const response = await window.axios.get('/api/v1/me');
  currentRole.value = response.data.data.role || 'pemohon';
};

onMounted(async () => {
  await Promise.all([loadProject(), loadMe()]);
});
</script>
