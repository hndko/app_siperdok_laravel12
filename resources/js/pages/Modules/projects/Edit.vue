<template>
  <app-layout :page-title="`Edit / Perbaiki Permohonan: ${project.project_number}`">
    <div class="row justify-content-center">
      <div class="col-md-9">
        <div class="card card-outline card-warning shadow-sm">
          <div class="card-header">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-edit text-warning mr-2"></i> Perbaiki Permohonan {{ project.project_number }}
            </h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div v-if="project.status === 'revision'" class="callout callout-warning py-3 mb-4">
                <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Permohonan Ini Memerlukan Revisi dari Penilai</h6>
                <p class="mb-0 small">Silakan perbaiki data atau unggah versi dokumen terbaru sesuai catatan perbaikan Penilai.</p>
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Judul Permohonan / Proyek <span class="text-danger">*</span></label>
                <input type="text" v-model="form.title" class="form-control" required>
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Jenis Dokumen Kelayakan <span class="text-danger">*</span></label>
                <select v-model="form.document_type_id" class="form-control" required>
                  <option value="">-- Pilih Jenis Dokumen --</option>
                  <option v-for="dt in documentTypes" :key="dt.id" :value="dt.id">
                    {{ dt.code }} - {{ dt.name }}
                  </option>
                </select>
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Unggah Berkas Baru (Opsional - Kosongkan jika tidak ada revisi berkas)</label>
                <input type="file" @change="handleFile" class="form-control-file border p-2 rounded bg-light">
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Deskripsi / Rincian Kegiatan Proyek</label>
                <textarea v-model="form.description" class="form-control" rows="4"></textarea>
              </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
              <Link :href="`/projects/${project.id}`" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Batal</Link>
              <div>
                <button type="button" @click="saveDraft" :disabled="form.processing" class="btn btn-outline-primary mr-2 font-weight-bold">
                  <i class="fas fa-save mr-1"></i> Simpan Permbaruan Draft
                </button>
                <button type="submit" :disabled="form.processing" class="btn btn-success font-weight-bold">
                  <i class="fas fa-paper-plane mr-1"></i> Kirim Ulang Permohonan
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import AppLayout from '../../../layouts/AppLayout.vue';
import { apiErrorMessages, confirmAction, toast } from '../../../lib/feedback';

const props = defineProps({
  project: { type: Object, required: true },
  documentTypes: { type: Array, default: () => [] }
});
const project = ref(props.project);
const documentTypes = ref(props.documentTypes);

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = useForm({
  _method: 'PUT',
  title: props.project.title || '',
  document_type_id: props.project.document_type_id || '',
  description: props.project.description || '',
  document: null,
  submit_action: 'submit',
});

const loadProject = async () => {
  const segments = window.location.pathname.split('/').filter(Boolean);
  const id = segments[1];
  const response = await window.axios.get(`/api/v1/projects/${id}`);
  project.value = response.data.data;
  form.title = project.value.title || '';
  form.document_type_id = project.value.document_type_id || project.value.document_type?.id || '';
  form.description = project.value.description || '';
};

const loadDocumentTypes = async () => {
  const response = await window.axios.get('/api/v1/document-types');
  documentTypes.value = response.data.data || [];
};

const handleFile = (e) => {
  form.document = e.target.files[0];
};

const submitProject = async (action) => {
  const confirmed = await confirmAction({
    title: action === 'draft' ? 'Simpan pembaruan draft?' : 'Kirim ulang permohonan?',
    text: action === 'draft'
      ? 'Perubahan akan disimpan tanpa masuk ulang ke penilaian.'
      : 'Permohonan revisi akan dikirim kembali ke penilai.',
    icon: action === 'draft' ? 'question' : 'warning',
    confirmButtonText: action === 'draft' ? 'Ya, simpan' : 'Ya, kirim ulang',
    confirmButtonColor: action === 'draft' ? '#007bff' : '#28a745',
  });

  if (!confirmed) {
    return;
  }

  const payload = new FormData();
  form.submit_action = action;
  payload.append('title', form.title);
  payload.append('document_type_id', form.document_type_id);
  payload.append('description', form.description || '');
  payload.append('submit_action', form.submit_action);

  if (form.document) {
    payload.append('document', form.document);
  }

  try {
    await window.axios.post(`/api/v1/projects/${project.value.id}`, payload);
    toast('success', action === 'draft' ? 'Draft berhasil diperbarui.' : 'Permohonan berhasil dikirim ulang.');
    setTimeout(() => {
      window.location.href = `/projects/${project.value.id}`;
    }, 600);
  } catch (error) {
    apiErrorMessages(error, 'Permohonan gagal diperbarui.')
      .slice(0, 3)
      .forEach((message) => toast('error', message));
  }
};

const saveDraft = () => {
  form.submit_action = 'draft';
  submitProject('draft');
};

const submit = () => {
  form.submit_action = 'submit';
  submitProject('submit');
};

onMounted(async () => {
  await Promise.all([loadProject(), loadDocumentTypes()]);
});

watch(
  errors,
  (value) => {
    Object.values(value).flat().filter(Boolean).slice(0, 3).forEach((message) => {
      toast('error', message);
    });
  },
  { immediate: true }
);
</script>
