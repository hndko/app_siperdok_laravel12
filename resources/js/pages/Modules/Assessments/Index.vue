<template>
  <app-layout page-title="Penilaian Permohonan Dokumen">
    <div class="card card-outline card-warning shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-tasks text-warning mr-2"></i> Daftar Permohonan Masuk Untuk Penilaian
        </h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="loadProjects()" class="mb-4">
          <div class="row">
            <div class="col-md-4 mb-2">
              <form-input-group v-model="form.search" icon="fas fa-search" placeholder="Cari no. permohonan, judul, atau pemohon" aria-label="Cari permohonan penilaian" />
            </div>
            <div class="col-md-3 mb-2">
              <form-input-group v-model="form.status" icon="fas fa-tag" type="select" placeholder="Pilih status penilaian">
                <option value="">-- Semua Status Penilaian --</option>
                <option value="submitted">Telah Dikirim (Diproses)</option>
                <option value="in_review">Sedang Dalam Penilaian</option>
                <option value="revision">Perlu Revisi</option>
                <option value="approved">Disetujui</option>
                <option value="certificate_issued">Certificate Terbit</option>
                <option value="rejected">Ditolak</option>
              </form-input-group>
            </div>
            <div class="col-md-3 mb-2">
              <form-input-group v-model="form.document_type_id" icon="fas fa-file-alt" type="select" placeholder="Pilih jenis dokumen">
                <option value="">-- Semua Jenis Dokumen --</option>
                <option v-for="dt in documentTypes" :key="dt.id" :value="dt.id">
                  {{ dt.code }} - {{ dt.name }}
                </option>
              </form-input-group>
            </div>
            <div class="col-md-2 mb-2">
              <button type="submit" class="btn btn-warning btn-block font-weight-bold" :disabled="loading">
                <i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-filter'" class="mr-1"></i> Filter
              </button>
            </div>
          </div>
          <button type="button" class="btn btn-link btn-sm p-0" @click="resetFilters" :disabled="loading">
            Reset filter
          </button>
        </form>

        <div v-if="errorMessage" class="alert alert-danger">
          {{ errorMessage }}
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-striped border align-middle">
            <thead class="bg-light">
              <tr>
                <th>No. Permohonan</th>
                <th>Judul Permohonan</th>
                <th>Pemohon & Perusahaan</th>
                <th>Status</th>
                <th>Penilai</th>
                <th>Tgl Masuk</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="prj in projects.data" :key="prj.id">
                <td class="font-weight-bold text-primary">{{ prj.project_number }}</td>
                <td>
                  <div>{{ prj.title }}</div>
                  <small class="text-muted">{{ prj.document_type ? prj.document_type.name : '-' }}</small>
                </td>
                <td>
                  <div>{{ prj.applicant ? prj.applicant.name : '-' }}</div>
                  <small class="text-muted">{{ prj.applicant ? prj.applicant.company_name : '-' }}</small>
                </td>
                <td>
                  <status-badge :status="prj.status"></status-badge>
                </td>
                <td>
                  <span class="badge badge-light border">{{ prj.evaluator ? prj.evaluator.name : 'Belum Ditugaskan' }}</span>
                </td>
                <td class="small">{{ prj.submitted_at ? formatDate(prj.submitted_at) : '-' }}</td>
                <td class="text-center">
                  <div class="action-buttons">
                    <Link :href="`/projects/${prj.id}`" class="btn btn-outline-info btn-sm font-weight-bold">
                      <i class="fas fa-eye mr-1"></i> Detail
                    </Link>
                    <Link
                      v-if="assessmentAction(prj)"
                      :href="assessmentAction(prj).href"
                      :class="['btn btn-sm font-weight-bold', assessmentAction(prj).className]"
                    >
                      <i :class="assessmentAction(prj).icon" class="mr-1"></i> {{ assessmentAction(prj).label }}
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="loading">
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data penilaian...
                </td>
              </tr>
              <tr v-else-if="!projects.data || !projects.data.length">
                <td colspan="7" class="text-center text-muted py-4">Tidak ada permohonan yang perlu dinilai.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="small text-muted">Menampilkan {{ formatNumber(projects.data?.length || 0) }} permohonan pada halaman ini</div>
          <div>
            <button type="button" class="btn btn-outline-secondary btn-sm mr-1" :disabled="loading || !projects.links?.prev" @click="loadPage(projects.links.prev)">
              <i class="fas fa-chevron-left"></i> Sebelumnya
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="loading || !projects.links?.next" @click="loadPage(projects.links.next)">
              Berikutnya <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import AppLayout from '../../../layouts/AppLayout.vue';
import FormInputGroup from '../../../components/FormInputGroup.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import { useDebouncedRequest } from '../../../composables/useDebouncedRequest';
import { apiErrorMessage } from '../../../lib/feedback';

const props = defineProps({
  projects: { type: Object, default: () => ({ data: [] }) },
  documentTypes: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) }
});
const projects = ref(props.projects);
const documentTypes = ref(props.documentTypes);
const loading = ref(false);
const errorMessage = ref('');
const { run: debouncedLoad, cancel } = useDebouncedRequest(400);

const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  document_type_id: props.filters.document_type_id || '',
});

const loadDocumentTypes = async () => {
  const response = await window.axios.get('/api/v1/document-types');
  documentTypes.value = response.data.data || [];
};

const loadProjects = async (signal = null) => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await window.axios.get('/api/v1/projects', { params: form, signal });
    projects.value = response.data;
  } catch (error) {
    if (error.name !== 'CanceledError' && error.code !== 'ERR_CANCELED') {
      errorMessage.value = apiErrorMessage(error, 'Data penilaian gagal dimuat.');
    }
  } finally {
    loading.value = false;
  }
};

const loadPage = async (url) => {
  if (!url) return;

  cancel();
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await window.axios.get(url);
    projects.value = response.data;
  } catch (error) {
    errorMessage.value = apiErrorMessage(error, 'Halaman penilaian gagal dimuat.');
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  form.search = '';
  form.status = '';
  form.document_type_id = '';
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

const assessmentAction = (project) => {
  const actions = {
    submitted: {
      href: `/assessments/${project.id}/review`,
      className: 'btn-warning',
      icon: 'fas fa-play',
      label: 'Mulai Review',
    },
    in_review: {
      href: `/assessments/${project.id}/review`,
      className: 'btn-warning',
      icon: 'fas fa-gavel',
      label: 'Lanjut Review',
    },
    revision: {
      href: `/projects/${project.id}`,
      className: 'btn-outline-warning',
      icon: 'fas fa-history',
      label: 'Lihat Revisi',
    },
    approved: {
      href: `/exports/projects/${project.id}/certificate/preview`,
      className: 'btn-outline-success',
      icon: 'fas fa-award',
      label: 'Pratinjau Surat',
    },
    certificate_issued: {
      href: `/exports/projects/${project.id}/certificate/preview`,
      className: 'btn-success',
      icon: 'fas fa-file-pdf',
      label: 'Unduh PDF',
    },
    rejected: {
      href: `/projects/${project.id}`,
      className: 'btn-outline-danger',
      icon: 'fas fa-times-circle',
      label: 'Lihat Alasan',
    },
  };

  return actions[project.status] || null;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(async () => {
  await Promise.all([loadProjects(), loadDocumentTypes()]);
});

watch(form, () => {
  debouncedLoad((signal) => loadProjects(signal));
});
</script>

<style scoped>
.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  justify-content: center;
}
</style>
