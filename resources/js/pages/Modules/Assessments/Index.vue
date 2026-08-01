<template>
  <app-layout page-title="Penilaian Permohonan Dokumen">
    <div class="card card-outline card-warning shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-tasks text-warning mr-2"></i> Daftar Permohonan Masuk Untuk Penilaian
        </h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="filter" class="mb-4">
          <div class="row">
            <div class="col-md-4 mb-2">
              <input type="text" v-model="form.search" class="form-control" placeholder="Cari No. Permohonan / Judul / Pemohon...">
            </div>
            <div class="col-md-3 mb-2">
              <select v-model="form.status" class="form-control">
                <option value="">-- Semua Status Penilaian --</option>
                <option value="submitted">Telah Dikirim (Diproses)</option>
                <option value="in_review">Sedang Dalam Penilaian</option>
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
              <button type="submit" class="btn btn-warning btn-block font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter</button>
            </div>
          </div>
        </form>

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
                  <Link :href="`/assessments/${prj.id}/review`" class="btn btn-warning btn-sm font-weight-bold">
                    <i class="fas fa-gavel mr-1"></i> Review & Penilaian
                  </Link>
                </td>
              </tr>
              <tr v-if="!projects.data || !projects.data.length">
                <td colspan="7" class="text-center text-muted py-4">Tidak ada permohonan yang perlu dinilai.</td>
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
import AppLayout from '../../../layouts/AppLayout.vue';
import StatusBadge from '../../../components/StatusBadge.vue';

const props = defineProps({
  projects: { type: Object, default: () => ({ data: [] }) },
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

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(async () => {
  await Promise.all([filter(), loadDocumentTypes()]);
});
</script>
