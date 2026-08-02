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
              <form-input-group v-model="form.search" icon="fas fa-search" placeholder="Cari nama, email, perusahaan, atau NIK" />
            </div>
            <div class="col-md-4 mb-2">
              <form-input-group v-model="form.role" icon="fas fa-user-shield" type="select" placeholder="Pilih role">
                <option value="">-- Semua Role --</option>
                <option value="pemohon">Pemohon</option>
                <option value="penilai">Penilai</option>
                <option value="admin">Admin</option>
              </form-input-group>
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
                <th style="width: 70px;">No</th>
                <th>Nama Pengguna</th>
                <th>Email Login</th>
                <th>Perusahaan / Instansi</th>
                <th>No. Telp / NIK</th>
                <th>Role</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(u, index) in users.data" :key="u.id">
                <td>{{ rowNumber(index) }}</td>
                <td class="font-weight-bold">{{ u.name }}</td>
                <td><code>{{ u.email }}</code></td>
                <td>{{ u.company_name || '-' }}</td>
                <td class="small">{{ u.phone || '-' }} / {{ u.nip_nik || '-' }}</td>
                <td>
                  <span v-for="r in u.roles" :key="r.id" :class="['badge mr-1', getRoleBadge(r.name)]">
                    {{ r.name.toUpperCase() }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="action-buttons">
                    <button type="button" class="btn btn-info btn-xs" @click="openShowModal(u)">
                      <i class="fas fa-eye mr-1"></i> Show
                    </button>
                    <button type="button" class="btn btn-warning btn-xs" @click="openEditModal(u)">
                      <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!users.data || !users.data.length">
                <td colspan="7" class="text-center text-muted py-4">Tidak ada pengguna ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3" v-if="users.total">
          <div class="small text-muted">Menampilkan {{ users.from || 0 }} - {{ users.to || 0 }} dari {{ formatNumber(users.total) }} pengguna</div>
        </div>
      </div>
    </div>

    <div v-if="showModalOpen" class="modal-backdrop-custom" @click.self="closeModals">
      <section class="modal-panel" aria-modal="true" role="dialog">
        <div class="modal-panel-header">
          <h5 class="mb-0 font-weight-bold"><i class="fas fa-user mr-2 text-info"></i> Detail Pengguna</h5>
          <button type="button" class="btn btn-link text-muted p-0" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-panel-body">
          <div class="user-detail-grid">
            <div>
              <small class="text-muted d-block">Nama</small>
              <strong>{{ selectedUser?.name || '-' }}</strong>
            </div>
            <div>
              <small class="text-muted d-block">Email</small>
              <code>{{ selectedUser?.email || '-' }}</code>
            </div>
            <div>
              <small class="text-muted d-block">Perusahaan / Instansi</small>
              <strong>{{ selectedUser?.company_name || '-' }}</strong>
            </div>
            <div>
              <small class="text-muted d-block">No. Telepon</small>
              <strong>{{ selectedUser?.phone || '-' }}</strong>
            </div>
            <div>
              <small class="text-muted d-block">NIP / NIK</small>
              <strong>{{ selectedUser?.nip_nik || '-' }}</strong>
            </div>
            <div>
              <small class="text-muted d-block">Role</small>
              <span :class="['badge', getRoleBadge(primaryRole(selectedUser))]">{{ primaryRole(selectedUser).toUpperCase() }}</span>
            </div>
          </div>
        </div>
        <div class="modal-panel-footer">
          <button type="button" class="btn btn-secondary" @click="closeModals">Tutup</button>
          <button type="button" class="btn btn-warning font-weight-bold" @click="openEditModal(selectedUser)">
            <i class="fas fa-edit mr-1"></i> Edit
          </button>
        </div>
      </section>
    </div>

    <div v-if="editModalOpen" class="modal-backdrop-custom" @click.self="closeModals">
      <section class="modal-panel" aria-modal="true" role="dialog">
        <div class="modal-panel-header">
          <h5 class="mb-0 font-weight-bold"><i class="fas fa-user-edit mr-2 text-warning"></i> Edit Pengguna</h5>
          <button type="button" class="btn btn-link text-muted p-0" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="submitEdit">
          <div class="modal-panel-body">
            <div class="form-group">
              <label class="font-weight-bold">Nama Pengguna <span class="text-danger">*</span></label>
              <form-input-group v-model="editForm.name" icon="fas fa-user" placeholder="Masukkan nama pengguna" required />
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Email Login <span class="text-danger">*</span></label>
              <form-input-group v-model="editForm.email" icon="fas fa-envelope" type="email" placeholder="Masukkan email login" required />
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Role <span class="text-danger">*</span></label>
              <form-input-group v-model="editForm.role" icon="fas fa-user-shield" type="select" placeholder="Pilih role" required>
                <option value="pemohon">Pemohon</option>
                <option value="penilai">Penilai</option>
                <option value="admin">Admin</option>
              </form-input-group>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Perusahaan / Instansi</label>
              <form-input-group v-model="editForm.company_name" icon="fas fa-building" placeholder="Masukkan perusahaan atau instansi" />
            </div>
            <div class="form-group">
              <label class="font-weight-bold">No. Telepon</label>
              <form-input-group v-model="editForm.phone" icon="fas fa-phone" placeholder="Masukkan nomor telepon" />
            </div>
            <div class="form-group mb-0">
              <label class="font-weight-bold">NIP / NIK</label>
              <form-input-group v-model="editForm.nip_nik" icon="fas fa-id-card" placeholder="Masukkan NIP atau NIK" />
            </div>
          </div>
          <div class="modal-panel-footer">
            <button type="button" class="btn btn-secondary" :disabled="saving" @click="closeModals">Batal</button>
            <button type="submit" class="btn btn-primary font-weight-bold" :disabled="saving">
              <i :class="saving ? 'fas fa-circle-notch fa-spin mr-1' : 'fas fa-save mr-1'"></i>
              {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </app-layout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import AppLayout from '../../../layouts/AppLayout.vue';
import FormInputGroup from '../../../components/FormInputGroup.vue';
import { apiErrorMessages, toast } from '../../../lib/feedback';

const props = defineProps({
  users: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) }
});
const users = ref(props.users);
const selectedUser = ref(null);
const showModalOpen = ref(false);
const editModalOpen = ref(false);
const saving = ref(false);

const form = reactive({
  search: props.filters.search || '',
  role: props.filters.role || '',
});

const editForm = reactive({
  id: null,
  name: '',
  email: '',
  role: 'pemohon',
  company_name: '',
  phone: '',
  nip_nik: '',
});

const filter = async () => {
  const response = await window.axios.get('/api/v1/users', { params: form });
  users.value = response.data.data;
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);
const rowNumber = (index) => (users.value?.from || 1) + index;

const primaryRole = (user) => {
  const role = user?.roles?.[0];

  return typeof role === 'string' ? role : (role?.name || 'pemohon');
};

const getRoleBadge = (roleName) => {
  switch (roleName) {
    case 'admin': return 'badge-danger';
    case 'penilai': return 'badge-success';
    default: return 'badge-primary';
  }
};

const openShowModal = (user) => {
  selectedUser.value = user;
  editModalOpen.value = false;
  showModalOpen.value = true;
};

const openEditModal = (user) => {
  if (!user) return;

  selectedUser.value = user;
  editForm.id = user.id;
  editForm.name = user.name || '';
  editForm.email = user.email || '';
  editForm.role = primaryRole(user);
  editForm.company_name = user.company_name || '';
  editForm.phone = user.phone || '';
  editForm.nip_nik = user.nip_nik || '';
  showModalOpen.value = false;
  editModalOpen.value = true;
};

const closeModals = () => {
  showModalOpen.value = false;
  editModalOpen.value = false;
};

const submitEdit = async () => {
  saving.value = true;

  try {
    await window.axios.put(`/api/v1/users/${editForm.id}`, {
      name: editForm.name,
      email: editForm.email,
      role: editForm.role,
      company_name: editForm.company_name,
      phone: editForm.phone,
      nip_nik: editForm.nip_nik,
    });
    await filter();
    closeModals();
    toast('success', 'Data pengguna berhasil diperbarui.');
  } catch (error) {
    apiErrorMessages(error, 'Data pengguna gagal diperbarui.')
      .slice(0, 3)
      .forEach((message) => toast('error', message));
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  filter();
});
</script>

<style scoped>
.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  justify-content: center;
}

.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 18px;
  background: rgba(15, 23, 42, 0.48);
}

.modal-panel {
  width: min(680px, 100%);
  max-height: calc(100vh - 36px);
  overflow: auto;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.24);
}

.modal-panel-header,
.modal-panel-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 18px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-panel-footer {
  border-top: 1px solid #e5e7eb;
  border-bottom: 0;
  justify-content: flex-end;
}

.modal-panel-body {
  padding: 18px;
}

.user-detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

@media (max-width: 575.98px) {
  .user-detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
