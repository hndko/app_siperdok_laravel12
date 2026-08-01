<template>
  <app-layout page-title="Manajemen User & Role">
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-users text-primary mr-2"></i> Master Pengguna & Hak Akses
        </h3>
      </div>
      <div class="card-body">
        <form @submit.prevent="filter" class="mb-4">
          <div class="row">
            <div class="col-md-5 mb-2">
              <input type="text" v-model="form.search" class="form-control" placeholder="Cari Nama / Email / Perusahaan / NIK...">
            </div>
            <div class="col-md-4 mb-2">
              <select v-model="form.role" class="form-control">
                <option value="">-- Semua Role --</option>
                <option value="pemohon">Pemohon</option>
                <option value="penilai">Penilai</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <button type="submit" class="btn btn-primary btn-block font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter</button>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover table-striped border align-middle">
            <thead class="bg-light">
              <tr>
                <th>ID</th>
                <th>Nama Pengguna</th>
                <th>Email Login</th>
                <th>Perusahaan / Instansi</th>
                <th>No. Telp / NIK</th>
                <th>Role</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in users.data" :key="u.id">
                <td>{{ u.id }}</td>
                <td class="font-weight-bold">{{ u.name }}</td>
                <td><code>{{ u.email }}</code></td>
                <td>{{ u.company_name || '-' }}</td>
                <td class="small">{{ u.phone || '-' }} / {{ u.nip_nik || '-' }}</td>
                <td>
                  <span v-for="r in u.roles" :key="r.id" :class="['badge mr-1', getRoleBadge(r.name)]">
                    {{ r.name.toUpperCase() }}
                  </span>
                </td>
              </tr>
              <tr v-if="!users.data || !users.data.length">
                <td colspan="6" class="text-center text-muted py-4">Tidak ada pengguna ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3" v-if="users.total">
          <div class="small text-muted">Menampilkan {{ users.from || 0 }} - {{ users.to || 0 }} dari {{ formatNumber(users.total) }} pengguna</div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import AppLayout from '../../../layouts/AppLayout.vue';

const props = defineProps({
  users: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) }
});
const users = ref(props.users);

const form = reactive({
  search: props.filters.search || '',
  role: props.filters.role || '',
});

const filter = async () => {
  const response = await window.axios.get('/api/v1/users', { params: form });
  users.value = response.data.data;
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

const getRoleBadge = (roleName) => {
  switch (roleName) {
    case 'admin': return 'badge-danger';
    case 'penilai': return 'badge-success';
    default: return 'badge-primary';
  }
};

onMounted(() => {
  filter();
});
</script>
