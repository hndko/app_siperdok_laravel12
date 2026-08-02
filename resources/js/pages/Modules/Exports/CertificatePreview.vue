<template>
  <app-layout :page-title="pageTitle">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-9">
        <div class="card card-outline card-success shadow">
          <div class="certificate-toolbar no-print">
            <div>
              <div class="certificate-toolbar__eyebrow">Pratinjau Dokumen Resmi</div>
              <h3 class="certificate-toolbar__title">
                <i class="fas fa-award text-success mr-2"></i> Surat Pengesahan Dokumen
              </h3>
            </div>
            <div class="certificate-toolbar__actions">
              <button type="button" @click="printCertificate" class="btn btn-outline-secondary font-weight-bold">
                <i class="fas fa-print mr-1"></i> Cetak Surat
              </button>
              <button
                v-if="canIssueCertificate"
                type="button"
                class="btn btn-primary font-weight-bold"
                :disabled="issuingCertificate"
                @click="issueCertificate"
              >
                <i :class="issuingCertificate ? 'fas fa-circle-notch fa-spin mr-1' : 'fas fa-certificate mr-1'"></i>
                {{ issuingCertificate ? 'Menerbitkan...' : 'Terbitkan Surat' }}
              </button>
              <button
                type="button"
                class="btn btn-success font-weight-bold"
                :disabled="project.status !== 'certificate_issued' || downloadingCertificate"
                @click="downloadCertificate"
              >
                <i :class="downloadingCertificate ? 'fas fa-circle-notch fa-spin mr-1' : 'fas fa-file-pdf mr-1'"></i>
                {{ downloadingCertificate ? 'Mengunduh...' : 'Unduh PDF' }}
              </button>
            </div>
          </div>

          <div v-if="loading" class="text-center py-5">
            <i class="fas fa-circle-notch fa-spin fa-2x text-success mb-3"></i>
            <p class="text-muted mb-0">Memuat pratinjau surat...</p>
          </div>

          <div v-else class="card-body p-5 certificate-print-area bg-white">
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
import { apiErrorMessage, confirmAction, toast } from '../../../lib/feedback';

const props = defineProps({
  project: { type: Object, default: () => ({}) }
});

const route = useRoute();
const project = ref(props.project);
const loading = ref(!props.project?.id);
const issuingCertificate = ref(false);
const downloadingCertificate = ref(false);
const currentRole = ref('');
const year = computed(() => new Date().getFullYear());
const pageTitle = computed(() => project.value?.project_number ? `Surat Pengesahan: ${project.value.project_number}` : 'Surat Pengesahan');
const canIssueCertificate = computed(() => project.value.status === 'approved' && ['admin', 'penilai'].includes(currentRole.value));

const printCertificate = () => {
  window.print();
};

const issueCertificate = async () => {
  const confirmed = await confirmAction({
    title: 'Terbitkan surat resmi?',
    text: 'Setelah diterbitkan, surat pengesahan resmi dapat diunduh sebagai PDF.',
    icon: 'question',
    confirmButtonText: 'Ya, terbitkan',
    confirmButtonColor: '#007bff',
  });

  if (!confirmed) return;

  issuingCertificate.value = true;
  try {
    const response = await window.axios.post(`/api/v1/projects/${project.value.id}/issue-certificate`);
    project.value = response.data.data;
    toast('success', 'Surat resmi berhasil diterbitkan.');
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Surat resmi gagal diterbitkan.'));
  } finally {
    issuingCertificate.value = false;
  }
};

const downloadCertificate = async () => {
  if (project.value.status !== 'certificate_issued') {
    toast('warning', 'Terbitkan surat resmi terlebih dahulu sebelum mengunduh PDF.');
    return;
  }

  downloadingCertificate.value = true;
  try {
    const response = await window.axios.get(`/api/v1/exports/projects/${project.value.id}/certificate`, {
      responseType: 'blob',
      headers: {
        Accept: 'application/pdf',
      },
    });
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `Surat_Pengesahan_${project.value.project_number}.pdf`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
    toast('success', 'Surat pengesahan berhasil diunduh.');
  } catch (error) {
    toast('error', await blobErrorMessage(error, 'Surat pengesahan gagal diunduh.'));
  } finally {
    downloadingCertificate.value = false;
  }
};

const blobErrorMessage = async (error, fallback) => {
  const data = error.response?.data;

  if (data instanceof Blob) {
    try {
      const text = await data.text();
      const parsed = JSON.parse(text);
      return parsed.message || fallback;
    } catch {
      return fallback;
    }
  }

  return apiErrorMessage(error, fallback);
};

const loadProject = async () => {
  loading.value = true;
  try {
    const response = await window.axios.get(`/api/v1/projects/${route.params.id}`);
    project.value = response.data.data;
  } catch (error) {
    toast('error', apiErrorMessage(error, 'Pratinjau surat gagal dimuat.'));
  } finally {
    loading.value = false;
  }
};

const loadMe = async () => {
  try {
    const response = await window.axios.get('/api/v1/me');
    currentRole.value = response.data.data.role || '';
  } catch {
    currentRole.value = '';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

onMounted(() => {
  loadProject();
  loadMe();
});
</script>

<style scoped>
@media print {
  .no-print { display: none !important; }
  .certificate-print-area { padding: 0 !important; }
}

.certificate-toolbar {
  align-items: center;
  background: #fff;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  gap: 16px;
  justify-content: space-between;
  padding: 16px 20px;
}

.certificate-toolbar__eyebrow {
  color: #6c757d;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.certificate-toolbar__title {
  color: #1f2937;
  font-size: 1.15rem;
  font-weight: 800;
  line-height: 1.3;
  margin: 2px 0 0;
}

.certificate-toolbar__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
}

.certificate-toolbar__actions .btn {
  border-radius: 6px;
  padding: 8px 12px;
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

@media (max-width: 767.98px) {
  .certificate-toolbar {
    align-items: stretch;
    flex-direction: column;
  }

  .certificate-toolbar__actions {
    justify-content: stretch;
  }

  .certificate-toolbar__actions .btn {
    flex: 1 1 auto;
  }
}
</style>
