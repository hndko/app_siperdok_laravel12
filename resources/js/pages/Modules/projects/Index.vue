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
        <form @submit.prevent="filter" class="mb-4">
          <div class="row">
            <div class="col-md-4 mb-2">
              <input type="text" v-model="form.search" class="form-control" placeholder="Cari No. Permohonan / Judul...">
            </div>
            <div class="col-md-3 mb-2">
              <select v-model="form.status" class="form-control">
                <option value="">-- Semua Status --</option>
                <option value="draft">Draft</option>
                <option value="submitted">Telah Dikirim</option>
                <option value="in_review">Dalam Penilaian</option>
                <option value="revision">Perlu Revisi</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <select v-model="form.document_type_id" class="form-control">
                <option value="">-- Semua Jenis Dokumen --</option>
                <option v-for="dt in documentTypes" :key="dt.id" :value="dt.id">
                  {{ dt.code }} - {{ dt.name }}
                </option>
              </select>
            </div>
            <div class="col-md-2 mb-2">
              <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
            </div>
          </div>
        </form>

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
                  <Link :href="`/projects/${prj.id}`" class="btn btn-info btn-xs mr-1"><i class="fas fa-eye"></i> Detail</Link>
                  <Link v-if="['draft', 'revision'].includes(prj.status)" :href="`/projects/${prj.id}/edit`" class="btn btn-warning btn-xs">
                    <i class="fas fa-edit"></i> {{ prj.status === 'revision' ? 'Perbaiki' : 'Edit' }}
                  </Link>
                </td>
              </tr>
              <tr v-if="!projects.data || !projects.data.length">
                <td colspan="6" class="text-center text-muted py-4">Tidak ada permohonan dokumen yang ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3" v-if="projects.total">
          <div class="small text-muted">Menampilkan {{ projects.from || 0 }} - {{ projects.to || 0 }} dari {{ formatNumber(projects.total) }} permohonan</div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../../layouts/AppLayout.vue';
import StatusBadge from '../../../components/StatusBadge.vue';

const props = defineProps({
  projects: { type: Object, required: true },
  documentTypes: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) }
});
const projects = ref(props.projects);
const documentTypes = ref(props.documentTypes);

const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  document_type_id: props.filters.document_type_id || '',
});

const loadDocumentTypes = async () => {
  const response = await window.axios.get('/api/v1/document-types');
  documentTypes.value = response.data.data || [];
};

const filter = async () => {
  const response = await window.axios.get('/api/v1/projects', { params: form });
  projects.value = response.data;
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);
const truncate = (str, len) => str && str.length > len ? str.substring(0, len) + '...' : str;

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(async () => {
  await Promise.all([filter(), loadDocumentTypes()]);
});
</script>
