<template>
  <app-layout page-title="Histori Penilaian Dokumen">
    <div class="card card-outline card-cyan shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-history text-cyan mr-2"></i> Audit Trail & Catatan Histori Penilaian
        </h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="filter" class="mb-4">
          <div class="row">
            <div class="col-md-5 mb-2">
              <input type="text" v-model="form.search" class="form-control" placeholder="Cari No. Permohonan / Judul...">
            </div>
            <div class="col-md-4 mb-2">
              <select v-model="form.action" class="form-control">
                <option value="">-- Semua Jenis Aksi --</option>
                <option value="create_draft">Buat Draft</option>
                <option value="submit">Kirim Permohonan</option>
                <option value="approve">Disetujui (Approve)</option>
                <option value="request_revision">Minta Revisi</option>
                <option value="reject">Ditolak (Reject)</option>
                <option value="resubmit">Kirim Ulang Revisi</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <button type="submit" class="btn btn-info btn-block font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter Log</button>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover table-striped border align-middle">
            <thead class="bg-light">
              <tr>
                <th>Waktu Log</th>
                <th>No. Permohonan</th>
                <th>Aksi</th>
                <th>User / Pelaku</th>
                <th>Catatan Evaluasi / Keterangan</th>
                <th class="text-center">Permohonan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in logs.data" :key="log.id">
                <td class="small">{{ formatDate(log.created_at) }}</td>
                <td class="font-weight-bold text-primary">{{ log.project ? log.project.project_number : '-' }}</td>
                <td>
                  <span :class="['badge', getActionBadge(log.action)]">{{ log.action.toUpperCase() }}</span>
                </td>
                <td>{{ log.user ? log.user.name : 'Sistem' }}</td>
                <td class="small text-secondary">{{ log.notes || '-' }}</td>
                <td class="text-center">
                  <Link v-if="log.project_id" :href="`/projects/${log.project_id}`" class="btn btn-default btn-xs">
                    <i class="fas fa-eye mr-1"></i> Lihat
                  </Link>
                </td>
              </tr>
              <tr v-if="!logs.data || !logs.data.length">
                <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat audit log.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3" v-if="logs.total">
          <div class="small text-muted">Menampilkan {{ logs.from || 0 }} - {{ logs.to || 0 }} dari {{ formatNumber(logs.total) }} log</div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';

const props = defineProps({
  logs: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) }
});

const form = reactive({
  search: props.filters.search || '',
  action: props.filters.action || '',
});

const filter = () => {
  router.get('/assessments/history', form, { preserveState: true });
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getActionBadge = (action) => {
  switch (action) {
    case 'approve': return 'badge-success';
    case 'reject': return 'badge-danger';
    case 'request_revision': return 'badge-warning';
    case 'submit': return 'badge-info';
    default: return 'badge-secondary';
  }
};
</script>
