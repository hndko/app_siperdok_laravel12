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
              <div v-if="project.status === 'revision'" class="alert alert-warning py-3 mb-4">
                <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Permohonan Ini Memerlukan Revisi dari Penilai</h6>
                <p class="mb-0 small">Silakan perbaiki data atau unggah versi dokumen terbaru sesuai catatan perbaikan Penilai.</p>
              </div>

              <div v-if="Object.keys(errors).length" class="alert alert-danger py-2 small mb-3">
                <ul class="mb-0 pl-3">
                  <li v-for="(err, key) in errors" :key="key">{{ err }}</li>
                </ul>
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
import { computed } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

const props = defineProps({
  project: { type: Object, required: true },
  documentTypes: { type: Array, default: () => [] }
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = useForm({
  _method: 'PUT',
  title: props.project.title,
  document_type_id: props.project.document_type_id,
  description: props.project.description || '',
  document: null,
  submit_action: 'submit',
});

const handleFile = (e) => {
  form.document = e.target.files[0];
};

const submitProject = async (action) => {
  const payload = new FormData();
  form.submit_action = action;
  payload.append('title', form.title);
  payload.append('document_type_id', form.document_type_id);
  payload.append('description', form.description || '');
  payload.append('submit_action', form.submit_action);

  if (form.document) {
    payload.append('document', form.document);
  }

  await window.axios.post(`/api/v1/projects/${props.project.id}`, payload);
  window.location.href = `/projects/${props.project.id}`;
};

const saveDraft = () => {
  form.submit_action = 'draft';
  submitProject('draft');
};

const submit = () => {
  form.submit_action = 'submit';
  submitProject('submit');
};
</script>
