<template>
  <app-layout page-title="Buat Permohonan Dokumen Baru">
    <div class="row justify-content-center">
      <div class="col-md-9">
        <div class="card card-outline card-success shadow-sm">
          <div class="card-header">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-file-signature text-success mr-2"></i> Formulir Pengajuan Dokumen Kelayakan
            </h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div v-if="Object.keys(errors).length" class="alert alert-danger py-2 small mb-3">
                <ul class="mb-0 pl-3">
                  <li v-for="(err, key) in errors" :key="key">{{ err }}</li>
                </ul>
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Judul Permohonan / Proyek <span class="text-danger">*</span></label>
                <input type="text" v-model="form.title" class="form-control" placeholder="Contoh: Dokumen Kelayakan Lingkungan Pembangunan Pabrik XYZ" required>
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
                <label class="font-weight-bold">Unggah Berkas Dokumen (PDF/Docx/Max 10MB) <span class="text-danger">*</span></label>
                <input type="file" @change="handleFile" class="form-control-file border p-2 rounded bg-light" required>
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Deskripsi / Rincian Kegiatan Proyek</label>
                <textarea v-model="form.description" class="form-control" rows="4" placeholder="Tuliskan gambaran umum kegiatan atau informasi tambahan permohonan..."></textarea>
              </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
              <Link href="/projects" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Batal</Link>
              <div>
                <button type="button" @click="saveDraft" :disabled="form.processing" class="btn btn-outline-primary mr-2 font-weight-bold">
                  <i class="fas fa-save mr-1"></i> Simpan Sebagai Draft
                </button>
                <button type="submit" :disabled="form.processing" class="btn btn-success font-weight-bold">
                  <i class="fas fa-paper-plane mr-1"></i> Kirim Permohonan Sekarang
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
  documentTypes: { type: Array, default: () => [] }
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = useForm({
  title: '',
  document_type_id: '',
  description: '',
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
  payload.append('document', form.document);

  const response = await window.axios.post('/api/v1/projects', payload);
  window.location.href = `/projects/${response.data.data.id}`;
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
