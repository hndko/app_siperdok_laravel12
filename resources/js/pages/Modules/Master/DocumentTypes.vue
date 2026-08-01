<template>
  <app-layout page-title="Master Jenis Dokumen">
    <div class="card card-outline card-info shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-file-alt text-info mr-2"></i> Master Kategori & Jenis Dokumen Kelayakan
        </h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped border align-middle">
            <thead class="bg-light">
              <tr>
                <th>Kode</th>
                <th>Nama Jenis Dokumen</th>
                <th>Deskripsi Kategori</th>
                <th class="text-center">Jumlah Permohonan</th>
                <th class="text-center">Status Aktif</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="dt in documentTypes" :key="dt.id">
                <td class="font-weight-bold text-primary">{{ dt.code }}</td>
                <td class="font-weight-bold">{{ dt.name }}</td>
                <td class="small text-secondary">{{ dt.description || '-' }}</td>
                <td class="text-center font-weight-bold">{{ formatNumber(dt.projects_count || 0) }}</td>
                <td class="text-center">
                  <span :class="['badge', dt.is_active ? 'badge-success' : 'badge-secondary']">
                    {{ dt.is_active ? 'Aktif' : 'Non-Aktif' }}
                  </span>
                </td>
              </tr>
              <tr v-if="!documentTypes || !documentTypes.length">
                <td colspan="5" class="text-center text-muted py-4">Belum ada jenis dokumen.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../../layouts/AppLayout.vue';

const props = defineProps({
  documentTypes: { type: Array, default: () => [] }
});
const documentTypes = ref(props.documentTypes);

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

onMounted(async () => {
  const response = await window.axios.get('/api/v1/document-types', {
    params: { include_inactive: 1 },
  });
  documentTypes.value = response.data.data || [];
});
</script>
