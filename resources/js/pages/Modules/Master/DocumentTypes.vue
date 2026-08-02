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
                <th style="width: 70px;">No</th>
                <th>Kode</th>
                <th>Nama Jenis Dokumen</th>
                <th>Deskripsi Kategori</th>
                <th class="text-center">Jumlah Permohonan</th>
                <th class="text-center">Status Aktif</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(dt, index) in documentTypes" :key="dt.id">
                <td>{{ index + 1 }}</td>
                <td class="font-weight-bold text-primary">{{ dt.code }}</td>
                <td class="font-weight-bold">{{ dt.name }}</td>
                <td class="small text-secondary">{{ dt.description || '-' }}</td>
                <td class="text-center font-weight-bold">{{ formatNumber(dt.projects_count || 0) }}</td>
                <td class="text-center">
                  <span :class="['badge', dt.is_active ? 'badge-success' : 'badge-secondary']">
                    {{ dt.is_active ? 'Aktif' : 'Non-Aktif' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="action-buttons">
                    <button type="button" class="btn btn-info btn-xs" @click="openShowModal(dt)">
                      <i class="fas fa-eye mr-1"></i> Show
                    </button>
                    <button type="button" class="btn btn-warning btn-xs" @click="openEditModal(dt)">
                      <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!documentTypes || !documentTypes.length">
                <td colspan="7" class="text-center text-muted py-4">Belum ada jenis dokumen.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showModalOpen" class="modal-backdrop-custom" @click.self="closeModals">
      <section class="modal-panel" aria-modal="true" role="dialog">
        <div class="modal-panel-header">
          <h5 class="mb-0 font-weight-bold"><i class="fas fa-file-alt mr-2 text-info"></i> Detail Jenis Dokumen</h5>
          <button type="button" class="btn btn-link text-muted p-0" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-panel-body">
          <div class="document-detail-grid">
            <div>
              <small class="text-muted d-block">Kode</small>
              <strong class="text-primary">{{ selectedDocumentType?.code || '-' }}</strong>
            </div>
            <div>
              <small class="text-muted d-block">Status</small>
              <span :class="['badge', selectedDocumentType?.is_active ? 'badge-success' : 'badge-secondary']">
                {{ selectedDocumentType?.is_active ? 'Aktif' : 'Non-Aktif' }}
              </span>
            </div>
            <div class="document-detail-grid__full">
              <small class="text-muted d-block">Nama Jenis Dokumen</small>
              <strong>{{ selectedDocumentType?.name || '-' }}</strong>
            </div>
            <div class="document-detail-grid__full">
              <small class="text-muted d-block">Deskripsi</small>
              <p class="mb-0">{{ selectedDocumentType?.description || '-' }}</p>
            </div>
            <div>
              <small class="text-muted d-block">Jumlah Permohonan</small>
              <strong>{{ formatNumber(selectedDocumentType?.projects_count || 0) }}</strong>
            </div>
            <div class="document-detail-grid__full">
              <small class="text-muted d-block mb-1">Berkas Wajib</small>
              <ul v-if="selectedRequiredFiles.length" class="mb-0 pl-3">
                <li v-for="file in selectedRequiredFiles" :key="file">{{ file }}</li>
              </ul>
              <span v-else>-</span>
            </div>
          </div>
        </div>
        <div class="modal-panel-footer">
          <button type="button" class="btn btn-secondary" @click="closeModals">Tutup</button>
          <button type="button" class="btn btn-warning font-weight-bold" @click="openEditModal(selectedDocumentType)">
            <i class="fas fa-edit mr-1"></i> Edit
          </button>
        </div>
      </section>
    </div>

    <div v-if="editModalOpen" class="modal-backdrop-custom" @click.self="closeModals">
      <section class="modal-panel" aria-modal="true" role="dialog">
        <div class="modal-panel-header">
          <h5 class="mb-0 font-weight-bold"><i class="fas fa-edit mr-2 text-warning"></i> Edit Jenis Dokumen</h5>
          <button type="button" class="btn btn-link text-muted p-0" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="submitEdit">
          <div class="modal-panel-body">
            <div class="form-group">
              <label class="font-weight-bold">Kode Dokumen <span class="text-danger">*</span></label>
              <form-input-group v-model="editForm.code" icon="fas fa-hashtag" placeholder="Contoh: AMDAL" required />
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Nama Jenis Dokumen <span class="text-danger">*</span></label>
              <form-input-group v-model="editForm.name" icon="fas fa-file-alt" placeholder="Masukkan nama jenis dokumen" required />
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Status Aktif <span class="text-danger">*</span></label>
              <form-input-group v-model="editForm.is_active" icon="fas fa-toggle-on" type="select" placeholder="Pilih status" required>
                <option value="1">Aktif</option>
                <option value="0">Non-Aktif</option>
              </form-input-group>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Deskripsi</label>
              <form-input-group v-model="editForm.description" icon="fas fa-align-left" type="textarea" rows="3" placeholder="Masukkan deskripsi jenis dokumen" />
            </div>
            <div class="form-group mb-0">
              <label class="font-weight-bold">Berkas Wajib</label>
              <form-input-group v-model="editForm.required_files_text" icon="fas fa-list-ul" type="textarea" rows="5" placeholder="Tulis satu nama berkas per baris" />
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
import { computed, onMounted, reactive, ref } from 'vue';
import AppLayout from '../../../layouts/AppLayout.vue';
import FormInputGroup from '../../../components/FormInputGroup.vue';
import { apiErrorMessages, toast } from '../../../lib/feedback';

const props = defineProps({
  documentTypes: { type: Array, default: () => [] }
});
const documentTypes = ref(props.documentTypes);
const selectedDocumentType = ref(null);
const showModalOpen = ref(false);
const editModalOpen = ref(false);
const saving = ref(false);

const editForm = reactive({
  id: null,
  code: '',
  name: '',
  description: '',
  required_files_text: '',
  is_active: '1',
});

const selectedRequiredFiles = computed(() => selectedDocumentType.value?.required_files || []);

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

const loadDocumentTypes = async () => {
  const response = await window.axios.get('/api/v1/document-types', {
    params: { include_inactive: 1 },
  });
  documentTypes.value = response.data.data || [];
};

const openShowModal = (documentType) => {
  selectedDocumentType.value = documentType;
  editModalOpen.value = false;
  showModalOpen.value = true;
};

const openEditModal = (documentType) => {
  if (!documentType) return;

  selectedDocumentType.value = documentType;
  editForm.id = documentType.id;
  editForm.code = documentType.code || '';
  editForm.name = documentType.name || '';
  editForm.description = documentType.description || '';
  editForm.required_files_text = (documentType.required_files || []).join('\n');
  editForm.is_active = documentType.is_active ? '1' : '0';
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
    await window.axios.put(`/api/v1/document-types/${editForm.id}`, {
      code: editForm.code,
      name: editForm.name,
      description: editForm.description,
      required_files_text: editForm.required_files_text,
      is_active: editForm.is_active === '1',
    });
    await loadDocumentTypes();
    closeModals();
    toast('success', 'Jenis dokumen berhasil diperbarui.');
  } catch (error) {
    apiErrorMessages(error, 'Jenis dokumen gagal diperbarui.')
      .slice(0, 3)
      .forEach((message) => toast('error', message));
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  await loadDocumentTypes();
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
  width: min(720px, 100%);
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

.document-detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.document-detail-grid__full {
  grid-column: 1 / -1;
}

@media (max-width: 575.98px) {
  .document-detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
