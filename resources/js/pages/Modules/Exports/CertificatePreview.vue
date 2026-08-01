<template>
  <app-layout :page-title="`Surat Pengesahan: ${project.project_number}`">
    <div class="row justify-content-center">
      <div class="col-md-9">
        <div class="card card-outline card-success shadow">
          <div class="card-header d-flex justify-content-between align-items-center no-print">
            <h3 class="card-title font-weight-bold text-success">
              <i class="fas fa-award mr-2"></i> Pratinjau Surat Pengesahan Dokumen (Vue 3 Component)
            </h3>
            <div>
              <button @click="printCertificate" class="btn btn-primary btn-sm mr-2 font-weight-bold">
                <i class="fas fa-print mr-1"></i> Cetak Surat (Print)
              </button>
              <button type="button" class="btn btn-success btn-sm font-weight-bold" :disabled="project.status !== 'certificate_issued'" @click="downloadCertificate">
                <i class="fas fa-file-pdf mr-1"></i> Unduh File PDF
              </button>
            </div>
          </div>

          <div class="card-body p-5 certificate-print-area bg-white">
            <div v-if="project.status !== 'certificate_issued'" class="draft-watermark">DRAFT</div>
            <div class="header text-center border-bottom pb-3 mb-4">
              <h4 class="font-weight-bold text-uppercase mb-1" style="letter-spacing: 1px;">REPUBLIK INDONESIA</h4>
              <h6 class="text-secondary mb-0">SISTEM INFORMASI PERSETUJUAN DOKUMEN KELAYAKAN (SIPERDOK)</h6>
              <small class="text-muted">Kementerian / Instansi Lingkungan Hidup dan Kehutanan</small>
            </div>

            <div class="text-center mb-4">
              <h4 class="font-weight-bold text-uppercase text-underline text-dark" style="text-decoration: underline;">SURAT PENGESAHAN KELAYAKAN DOKUMEN</h4>
              <div class="text-muted small">Nomor Certificate: {{ project.certificate_number || `DRAFT/${year}/${project.project_number}` }}</div>
            </div>

            <div class="content text-secondary mb-4" style="font-size: 1.05rem;">
              <p>Berdasarkan hasil evaluasi dan penilaian teknis yang dilakukan oleh Tim Evaluator Dokumen Kelayakan, bersama ini menerangkan bahwa permohonan dokumen kelayakan berikut:</p>

              <table class="table table-borderless my-4">
                <tbody>
                  <tr>
                    <td class="font-weight-bold" style="width: 35%;">Nomor Permohonan</td>
                    <td>: <strong class="text-primary">{{ project.project_number }}</strong></td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold">Judul Kegiatan / Proyek</td>
                    <td>: {{ project.title }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold">Jenis Dokumen</td>
                    <td>: {{ project.document_type ? project.document_type.name : '-' }} ({{ project.document_type ? project.document_type.code : '-' }})</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold">Nama Pemohon</td>
                    <td>: {{ project.applicant ? project.applicant.name : '-' }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold">Perusahaan / Instansi</td>
                    <td>: {{ project.applicant ? project.applicant.company_name : '-' }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold">Tanggal Pengajuan</td>
                    <td>: {{ project.submitted_at ? formatDate(project.submitted_at) : '-' }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold">Tanggal Disetujui</td>
                    <td>: {{ project.approved_at ? formatDate(project.approved_at) : formatDate(new Date()) }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold">Tanggal Diterbitkan</td>
                    <td>: {{ project.certificate_issued_at ? formatDate(project.certificate_issued_at) : 'Belum diterbitkan' }}</td>
                  </tr>
                </tbody>
              </table>

              <div class="stamp-box p-4 border border-success rounded text-center my-4" style="background-color: #f8fff9;">
                <h4 class="font-weight-bold text-success mb-1">STATUS: DISETUJU (APPROVED)</h4>
                <div class="small text-muted">Dokumen dinyatakan LENGKAP, SAH, dan MEMENUHI SYARAT KELAYAKAN LINGKUNGAN</div>
              </div>

              <p>Demikian Surat Pengesahan Kelayakan Dokumen ini diterbitkan secara elektronik melalui sistem SIPERDOK untuk dipergunakan sebagaimana mestinya.</p>
            </div>

            <div class="row mt-5">
              <div class="col-md-6 offset-md-6 text-center">
                <p class="mb-1">Ditetapkan di Jakarta<br>Pada Tanggal: {{ project.certificate_issued_at ? formatDate(project.certificate_issued_at) : formatDate(new Date()) }}</p>
                <p class="font-weight-bold mb-5">Pejabat Penerbit Dokumen Elektronik</p>
                <p class="font-weight-bold text-dark mb-0 text-underline" style="text-decoration: underline;">
                  {{ project.certificate_issuer ? project.certificate_issuer.name : (project.evaluator ? project.evaluator.name : 'Pejabat Penerbit') }}
                </p>
                <small class="text-muted d-block">NIP. {{ project.certificate_issuer ? (project.certificate_issuer.nip_nik || '-') : (project.evaluator ? (project.evaluator.nip_nik || '-') : '-') }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '../../../layouts/AppLayout.vue';
import { apiErrorMessage, toast } from '../../../lib/feedback';

const props = defineProps({
  project: { type: Object, default: () => ({}) }
});

const route = useRoute();
const project = ref(props.project);
const year = computed(() => new Date().getFullYear());

const printCertificate = () => {
  window.print();
};

const downloadCertificate = async () => {
  try {
    const response = await window.axios.get(`/api/v1/exports/projects/${project.value.id}/certificate`, {
      responseType: 'blob',
    });
    const url = URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.download = `Surat_Pengesahan_${project.value.project_number}.pdf`;
    link.click();
    URL.revokeObjectURL(url);
    toast('success', 'Surat pengesahan berhasil diunduh.');
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Surat pengesahan gagal diunduh.'));
  }
};

const loadProject = async () => {
  const response = await window.axios.get(`/api/v1/projects/${route.params.id}`);
  project.value = response.data.data;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

onMounted(() => {
  loadProject();
});
</script>

<style scoped>
@media print {
  .no-print { display: none !important; }
  .certificate-print-area { padding: 0 !important; }
}

.certificate-print-area {
  position: relative;
}

.draft-watermark {
  position: absolute;
  top: 42%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-22deg);
  font-size: 5rem;
  font-weight: 800;
  color: rgba(220, 53, 69, 0.14);
  border: 6px solid rgba(220, 53, 69, 0.12);
  padding: 8px 28px;
  pointer-events: none;
}
</style>
