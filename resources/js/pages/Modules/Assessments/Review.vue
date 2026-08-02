<template>
  <app-layout :page-title="pageTitle">
    <div v-if="loading" class="text-center py-5">
      <i class="fas fa-circle-notch fa-spin fa-2x text-primary mb-3"></i>
      <p class="text-muted mb-0">Memuat data penilaian...</p>
    </div>

    <div v-else class="row">
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
                  <a v-if="documentDownloadUrl(doc)" :href="documentDownloadUrl(doc)" target="_blank" class="btn btn-primary btn-sm font-weight-bold">
                    <i class="fas fa-download mr-1"></i> Unduh & Periksa
                  </a>
                  <span v-else class="badge badge-secondary">Berkas tidak tersedia</span>
                </div>
              </div>
              <div v-if="!project.documents || !project.documents.length" class="p-3 bg-light border rounded text-muted mb-0">Belum ada berkas dokumen.</div>
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

        <div class="card card-outline card-primary shadow-sm mb-4">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold text-primary">
              <i class="fas fa-clipboard-check mr-2"></i> Checklist Verifikasi
            </h3>
            <span class="badge badge-primary">{{ checklistSummary.progress_percent || 0 }}%</span>
          </div>
          <div class="card-body">
            <div class="progress mb-3" style="height: 8px;">
              <div class="progress-bar" :style="{ width: `${checklistSummary.progress_percent || 0}%` }"></div>
            </div>
            <div class="small text-muted mb-3">
              Passed: {{ checklistSummary.passed || 0 }} · Failed: {{ checklistSummary.failed || 0 }} · Pending: {{ checklistSummary.pending || 0 }} · N/A: {{ checklistSummary.not_applicable || 0 }}
            </div>

            <div v-if="checklistLoading" class="text-center text-muted py-3">
              <i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat checklist...
            </div>
            <div v-else class="list-group mb-3">
              <div v-for="item in checklistItems" :key="item.checklist_item_id" class="list-group-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <div class="font-weight-bold">
                      {{ item.item.name }}
                      <span v-if="item.item.is_required" class="text-danger">*</span>
                    </div>
                    <small class="text-muted">{{ item.item.description || '-' }}</small>
                  </div>
                  <form-input-group v-model="item.status" icon="fas fa-check-square" type="select" placeholder="Pilih status checklist" class="ml-2 checklist-status-control" :disabled="!canUpdateChecklist">
                    <option value="pending">Pending</option>
                    <option value="passed">Passed</option>
                    <option value="failed">Failed</option>
                    <option value="not_applicable">N/A</option>
                  </form-input-group>
                </div>
                <form-input-group
                  v-model="item.notes"
                  icon="fas fa-comment-alt"
                  type="textarea"
                  rows="2"
                  placeholder="Catatan checklist"
                  :disabled="!canUpdateChecklist"
                />
              </div>
            </div>

            <button type="button" class="btn btn-primary btn-block font-weight-bold" :disabled="!canUpdateChecklist || checklistSaving" @click="saveChecklist">
              <i :class="checklistSaving ? 'fas fa-circle-notch fa-spin mr-1' : 'fas fa-save mr-1'"></i>
              {{ checklistSaving ? 'Menyimpan...' : 'Simpan Checklist' }}
            </button>
            <p v-if="!canUpdateChecklist" class="text-muted small text-center mt-2 mb-0">
              Checklist hanya dapat diperbarui saat permohonan berstatus in_review oleh penilai/admin.
            </p>
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
                    :disabled="!canUseDecisionForm"
                    @click="setDecision('approved')"
                  >
                    <i class="fas fa-check-circle mr-1"></i> SETUJU
                  </button>

                  <button 
                    type="button" 
                    :class="['btn flex-fill mr-1 font-weight-bold', form.decision === 'revision' ? 'btn-warning' : 'btn-outline-warning']"
                    :disabled="!canUseDecisionForm"
                    @click="setDecision('revision')"
                  >
                    <i class="fas fa-edit mr-1"></i> REVISI
                  </button>

                  <button 
                    type="button" 
                    :class="['btn flex-fill font-weight-bold', form.decision === 'rejected' ? 'btn-danger' : 'btn-outline-danger']"
                    :disabled="!canUseDecisionForm"
                    @click="setDecision('rejected')"
                  >
                    <i class="fas fa-times-circle mr-1"></i> DITOLAK
                  </button>
                </div>
              </div>

              <div class="form-group mb-4">
                <label class="font-weight-bold">Catatan Penilai / Alasan Decision <span class="text-danger">*</span></label>
                <form-input-group
                  v-model="form.notes"
                  icon="fas fa-comment-dots"
                  type="textarea"
                  rows="5"
                  placeholder="Tuliskan catatan evaluasi, poin revisi wajib, atau alasan penolakan"
                  required
                  :disabled="!canUseDecisionForm"
                />
              </div>

              <button 
                type="submit" 
                :disabled="form.processing || !canUseDecisionForm || !form.notes.trim() || decisionBlocked"
                class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm"
              >
                <i class="fas fa-paper-plane mr-2"></i> {{ form.processing ? 'Memproses...' : 'Simpan Keputusan Penilaian' }}
              </button>
              <p v-if="!canAssess" class="text-muted small text-center mt-2 mb-0">
                Mulai review terlebih dahulu sebelum mengisi keputusan penilaian.
              </p>
              <p v-else-if="checklistLoading" class="text-muted small text-center mt-2 mb-0">
                Checklist sedang dimuat sebelum keputusan dapat diisi.
              </p>
              <p v-if="canAssess && decisionBlocked" class="text-danger small text-center mt-2 mb-0">
                Lengkapi checklist wajib sebelum menyimpan keputusan. Approval juga diblokir jika ada checklist wajib gagal.
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
import { reactive } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '../../../layouts/AppLayout.vue';
import FormInputGroup from '../../../components/FormInputGroup.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import { apiErrorMessage, confirmAction, toast } from '../../../lib/feedback';

const props = defineProps({
  project: { type: Object, default: () => ({}) },
  canStartReview: { type: Boolean, default: false },
  canAssess: { type: Boolean, default: false }
});
const project = ref(props.project);
const currentRole = ref('pemohon');
const loading = ref(!props.project?.id);
const route = useRoute();
const pageTitle = computed(() => project.value?.project_number ? `Penilaian Dokumen: ${project.value.project_number}` : 'Penilaian Dokumen');
const canStartReview = computed(() => ['admin', 'penilai'].includes(currentRole.value) && project.value.status === 'submitted');
const canAssess = computed(() => ['admin', 'penilai'].includes(currentRole.value) && project.value.status === 'in_review');
const checklistCanUpdate = ref(false);
const canUpdateChecklist = computed(() => ['admin', 'penilai'].includes(currentRole.value) && project.value.status === 'in_review' && checklistCanUpdate.value);
const checklistItems = ref([]);
const checklistSummary = ref({});
const originalChecklist = ref(new Map());
const checklistLoading = ref(false);
const checklistSaving = ref(false);
const decisionBlocked = computed(() => {
  const requiredPending = checklistSummary.value.required_pending || 0;
  const requiredFailed = checklistSummary.value.required_failed || 0;

  return requiredPending > 0 || (form.decision === 'approved' && requiredFailed > 0);
});
const canUseDecisionForm = computed(() => canAssess.value && !checklistLoading.value);

const startReviewForm = reactive({
  notes: '',
  processing: false,
});

const form = reactive({
  decision: 'approved',
  notes: '',
  processing: false,
});

const startReview = async () => {
  const confirmed = await confirmAction({
    title: 'Mulai review dokumen?',
    text: 'Permohonan akan dikunci ke penilai yang sedang memproses.',
    icon: 'question',
    confirmButtonText: 'Ya, mulai review',
    confirmButtonColor: '#17a2b8',
  });

  if (!confirmed) {
    return;
  }

  startReviewForm.processing = true;
  try {
    const response = await window.axios.post(`/api/v1/assessments/${project.value.id}/start-review`, {
      notes: startReviewForm.notes,
    });

    project.value = {
      ...project.value,
      ...response.data.data,
    };

    await loadChecklist();
    await loadProject();
    toast('success', 'Review dokumen berhasil dimulai.');
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Review dokumen gagal dimulai.'));
  } finally {
    startReviewForm.processing = false;
  }
};

const setDecision = (decision) => {
  if (!canUseDecisionForm.value) return;
  form.decision = decision;
};

const submitDecision = async () => {
  if (!canUseDecisionForm.value || decisionBlocked.value) {
    return;
  }

  const decisionLabels = {
    approved: 'menyetujui',
    revision: 'meminta revisi',
    rejected: 'menolak',
  };
  const confirmed = await confirmAction({
    title: 'Simpan keputusan penilaian?',
    text: `Anda akan ${decisionLabels[form.decision]} permohonan ini.`,
    icon: form.decision === 'approved' ? 'success' : 'warning',
    confirmButtonText: 'Ya, simpan keputusan',
    confirmButtonColor: form.decision === 'rejected' ? '#dc3545' : '#28a745',
  });

  if (!confirmed) {
    return;
  }

  form.processing = true;
  try {
    await window.axios.post(`/api/v1/assessments/${project.value.id}`, {
      decision: form.decision,
      notes: form.notes,
    });
    await loadProject();
    form.notes = '';
    toast('success', 'Keputusan penilaian berhasil disimpan.');
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Keputusan penilaian gagal disimpan.'));
  } finally {
    form.processing = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const documentDownloadUrl = (doc) => doc.download_url || (doc.file_path ? `/storage/${doc.file_path}` : null);

const loadProject = async () => {
  const id = route.params.id;
  loading.value = true;
  try {
    const response = await window.axios.get(`/api/v1/projects/${id}`);
    project.value = response.data.data;
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Data permohonan gagal dimuat.'));
  } finally {
    loading.value = false;
  }
};

const loadChecklist = async () => {
  if (!route.params.id) return;

  checklistLoading.value = true;
  try {
    const response = await window.axios.get(`/api/v1/projects/${route.params.id}/verification-checklists`);
    const data = response.data.data;
    checklistCanUpdate.value = Boolean(data.can_update);

    if (data.project_status) {
      project.value = {
        ...project.value,
        status: data.project_status,
      };
    }

    checklistItems.value = (data.items.data || data.items || []).map((item) => ({ ...item }));
    checklistSummary.value = data.summary || {};
    originalChecklist.value = new Map(checklistItems.value.map((item) => [item.checklist_item_id, item.status]));
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Checklist gagal dimuat.'));
  } finally {
    checklistLoading.value = false;
  }
};

const saveChecklist = async () => {
  const changedCheckedItems = checklistItems.value.some((item) => {
    const original = originalChecklist.value.get(item.checklist_item_id);
    return item.checked_at && original && original !== item.status;
  });

  if (changedCheckedItems) {
    const confirmed = await confirmAction({
      title: 'Ubah checklist yang sudah diperiksa?',
      text: 'Perubahan akan mengganti hasil pemeriksaan sebelumnya.',
      icon: 'warning',
      confirmButtonText: 'Ya, ubah',
    });

    if (!confirmed) {
      return;
    }
  }

  checklistSaving.value = true;
  try {
    await window.axios.put(`/api/v1/projects/${project.value.id}/verification-checklists`, {
      items: checklistItems.value.map((item) => ({
        checklist_item_id: item.checklist_item_id,
        status: item.status,
        notes: item.notes,
      })),
    });
    await loadChecklist();
    await loadProject();
    toast('success', 'Checklist berhasil disimpan.');
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Checklist gagal disimpan.'));
  } finally {
    checklistSaving.value = false;
  }
};

const loadMe = async () => {
  const response = await window.axios.get('/api/v1/me');
  currentRole.value = response.data.data.role || 'pemohon';
};

onMounted(async () => {
  await Promise.all([loadProject(), loadMe(), loadChecklist()]);
});
</script>

<style scoped>
.checklist-status-control {
  max-width: 190px;
  flex: 0 0 190px;
}

@media (max-width: 575.98px) {
  .checklist-status-control {
    max-width: 100%;
    flex-basis: 100%;
    margin-top: 8px;
    margin-left: 0 !important;
  }
}
</style>
