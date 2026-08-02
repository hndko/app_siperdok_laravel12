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
              <div class="form-group mb-3">
                <label class="font-weight-bold">Judul Permohonan / Proyek <span class="text-danger">*</span></label>
                <form-input-group v-model="form.title" icon="fas fa-heading" placeholder="Contoh: Dokumen Kelayakan Lingkungan Pembangunan Pabrik XYZ" required />
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Jenis Dokumen Kelayakan <span class="text-danger">*</span></label>
                <form-input-group v-model="form.document_type_id" icon="fas fa-file-alt" type="select" placeholder="Pilih jenis dokumen" required>
                  <option value="">-- Pilih Jenis Dokumen --</option>
                  <option v-for="dt in documentTypes" :key="dt.id" :value="dt.id">
                    {{ dt.code }} - {{ dt.name }}
                  </option>
                </form-input-group>
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Unggah Berkas Dokumen (PDF/Docx/Max 10MB) <span class="text-danger">*</span></label>
                <form-input-group type="file" icon="fas fa-upload" placeholder="Pilih berkas dokumen" :file-name="selectedFileName" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required @change="handleFile" />
              </div>

              <div class="form-group mb-3">
                <label class="font-weight-bold">Deskripsi / Rincian Kegiatan Proyek</label>
                <form-input-group v-model="form.description" icon="fas fa-align-left" type="textarea" rows="4" placeholder="Tuliskan gambaran umum kegiatan atau informasi tambahan permohonan..." />
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
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '../../../layouts/AppLayout.vue';
import FormInputGroup from '../../../components/FormInputGroup.vue';
import { apiErrorMessages, confirmAction, toast } from '../../../lib/feedback';

const props = defineProps({
  documentTypes: { type: Array, default: () => [] }
});
const documentTypes = ref(props.documentTypes);
const selectedFileName = ref('');
const router = useRouter();

const form = reactive({
  title: '',
  document_type_id: '',
  description: '',
  document: null,
  submit_action: 'submit',
  processing: false,
});

const handleFile = (e) => {
  form.document = e.target.files[0];
  selectedFileName.value = form.document?.name || '';
};

const submitProject = async (action) => {
  const confirmed = await confirmAction({
    title: action === 'draft' ? 'Simpan sebagai draft?' : 'Kirim permohonan?',
    text: action === 'draft'
      ? 'Data akan disimpan dan masih dapat diperbarui.'
      : 'Permohonan akan masuk ke antrean penilaian.',
    icon: action === 'draft' ? 'question' : 'warning',
    confirmButtonText: action === 'draft' ? 'Ya, simpan' : 'Ya, kirim',
    confirmButtonColor: action === 'draft' ? '#007bff' : '#28a745',
  });

  if (!confirmed) {
    return;
  }

  form.processing = true;
  const payload = new FormData();
  form.submit_action = action;
  payload.append('title', form.title);
  payload.append('document_type_id', form.document_type_id);
  payload.append('description', form.description || '');
  payload.append('submit_action', form.submit_action);
  payload.append('document', form.document);

  try {
    const response = await window.axios.post('/api/v1/projects', payload);
    toast('success', action === 'draft' ? 'Draft berhasil disimpan.' : 'Permohonan berhasil dikirim.');
    setTimeout(() => {
      router.push(`/projects/${response.data.data.id}`);
    }, 600);
  } catch (error) {
    apiErrorMessages(error, 'Permohonan gagal disimpan.')
      .slice(0, 3)
      .forEach((message) => toast('error', message));
  } finally {
    form.processing = false;
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
  const response = await window.axios.get('/api/v1/document-types');
  documentTypes.value = response.data.data || [];
});
</script>
