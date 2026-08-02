<template>
  <app-layout page-title="Daftar Permohonan Saya">
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-folder-open text-primary mr-2"></i> Daftar Permohonan Dokumen Kelayakan
        </h3>
        <div class="card-tools">
          <Link href="/projects/create" class="btn btn-success btn-sm font-weight-bold">
            <i class="fas fa-plus mr-1"></i> Buat Permohonan Baru
          </Link>
        </div>
      </div>
      <div class="card-body">
        <!-- Filter Form -->
        <form @submit.prevent="loadProjects()" class="mb-4">
          <div class="row">
            <div class="col-md-4 mb-2">
              <form-input-group v-model="form.search" icon="fas fa-search" placeholder="Cari no. permohonan atau judul" aria-label="Cari permohonan" />
            </div>
            <div class="col-md-3 mb-2">
              <form-input-group v-model="form.status" icon="fas fa-tag" type="select" placeholder="Pilih status">
                <option value="">-- Semua Status --</option>
                <option value="draft">Draft</option>
                <option value="submitted">Telah Dikirim</option>
                <option value="in_review">Dalam Penilaian</option>
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
              <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
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
                <th>Jenis Dokumen</th>
                <th>Status</th>
                <th>Tgl Pengajuan</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="prj in projects.data" :key="prj.id">
                <td class="font-weight-bold text-primary">{{ prj.project_number }}</td>
                <td>
                  <div>{{ prj.title }}</div>
                  <small class="text-muted">{{ truncate(prj.description, 60) }}</small>
                </td>
                <td><span class="badge badge-light border">{{ prj.document_type ? prj.document_type.code : '-' }}</span></td>
                <td>
                  <status-badge :status="prj.status"></status-badge>
                </td>
                <td class="small">{{ prj.submitted_at ? formatDate(prj.submitted_at) : 'Draft (Belum Dikirim)' }}</td>
                <td class="text-center">
                  <div class="action-buttons">
                    <Link :href="`/projects/${prj.id}`" class="btn btn-info btn-xs"><i class="fas fa-eye"></i> Detail</Link>
                    <Link v-if="['draft', 'revision'].includes(prj.status)" :href="`/projects/${prj.id}/edit`" class="btn btn-warning btn-xs">
                      <i class="fas fa-edit"></i> {{ prj.status === 'revision' ? 'Perbaiki' : 'Edit' }}
                    </Link>
                    <Link v-if="['approved', 'certificate_issued'].includes(prj.status)" :href="`/exports/projects/${prj.id}/certificate/preview`" class="btn btn-success btn-xs">
                      <i :class="prj.status === 'certificate_issued' ? 'fas fa-file-pdf' : 'fas fa-award'"></i>
                      {{ prj.status === 'certificate_issued' ? 'Unduh PDF' : 'Pratinjau Surat' }}
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="loading">
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data permohonan...
                </td>
              </tr>
              <tr v-else-if="!projects.data || !projects.data.length">
                <td colspan="6" class="text-center text-muted py-4">Tidak ada permohonan dokumen yang ditemukan.</td>
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
      errorMessage.value = apiErrorMessage(error, 'Data permohonan gagal dimuat.');
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
    errorMessage.value = apiErrorMessage(error, 'Halaman permohonan gagal dimuat.');
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
const truncate = (str, len) => str && str.length > len ? str.substring(0, len) + '...' : str;

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
