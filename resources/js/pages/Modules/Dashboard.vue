<template>
  <app-layout page-title="Dashboard Monitoring">
    <!-- KPI Info Boxes -->
    <div class="row">
      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
          <span class="info-box-icon bg-info elevation-1"><i class="fas fa-folder"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Permohonan</span>
            <span class="info-box-number h4 font-weight-bold mb-0">{{ formatNumber(totalProjects) }}</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
          <span class="info-box-icon bg-warning text-dark elevation-1"><i class="fas fa-clock"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Dalam Penilaian</span>
            <span class="info-box-number h4 font-weight-bold mb-0">{{ formatNumber(pendingCount) }}</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
          <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Disetujui (Approved)</span>
            <span class="info-box-number h4 font-weight-bold mb-0">{{ formatNumber(approvedCount) }}</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3 elevation-2">
          <span class="info-box-icon bg-orange text-white elevation-1"><i class="fas fa-edit"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Perlu Revisi</span>
            <span class="info-box-number h4 font-weight-bold mb-0">{{ formatNumber(revisionCount) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
      <div class="col-md-8">
        <div class="card card-outline card-primary shadow-sm">
          <div class="card-header border-0">
            <h3 class="card-title font-weight-bold text-dark">
              <i class="fas fa-chart-line text-primary mr-2"></i> Tren Pengajuan Dokumen Bulanan
            </h3>
          </div>
          <div class="card-body">
            <canvas id="vueMonthlyChart" style="min-height: 260px; height: 260px; max-height: 260px; max-width: 100%;"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-outline card-info shadow-sm">
          <div class="card-header border-0">
            <h3 class="card-title font-weight-bold text-dark">
              <i class="fas fa-chart-pie text-info mr-2"></i> Distribusi Status Dokumen
            </h3>
          </div>
          <div class="card-body">
            <canvas id="vueStatusChart" style="min-height: 260px; height: 260px; max-height: 260px; max-width: 100%;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Projects Table -->
    <div class="card card-outline card-secondary shadow-sm">
      <div class="card-header border-0">
        <h3 class="card-title font-weight-bold text-dark">
          <i class="fas fa-list text-secondary mr-2"></i> Permohonan Dokumen Terbaru
        </h3>
        <div class="card-tools">
          <Link v-if="userRole === 'pemohon'" href="/projects/create" class="btn btn-success btn-sm">
            <i class="fas fa-plus mr-1"></i> Buat Permohonan Baru
          </Link>
          <Link v-if="userRole === 'penilai' || userRole === 'admin'" href="/assessments" class="btn btn-primary btn-sm">
            <i class="fas fa-tasks mr-1"></i> Lihat Semua Penilaian
          </Link>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th>No. Permohonan</th>
              <th>Judul Permohonan</th>
              <th>Jenis Dokumen</th>
              <th v-if="userRole !== 'pemohon'">Pemohon / Perusahaan</th>
              <th>Status</th>
              <th>Tanggal Update</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="project in recentProjects" :key="project.id">
              <td class="font-weight-bold text-primary">{{ project.project_number }}</td>
              <td>{{ truncate(project.title, 45) }}</td>
              <td><span class="badge badge-light border">{{ project.document_type ? project.document_type.code : '-' }}</span></td>
              <td v-if="userRole !== 'pemohon'">
                <div>{{ project.applicant ? project.applicant.name : 'Pemohon' }}</div>
                <small class="text-muted">{{ project.applicant ? project.applicant.company_name : '-' }}</small>
              </td>
              <td>
                <status-badge :status="project.status"></status-badge>
              </td>
              <td class="small">{{ formatDate(project.updated_at) }}</td>
              <td class="text-center">
                <Link v-if="userRole !== 'pemohon'" :href="`/assessments/${project.id}/review`" class="btn btn-primary btn-xs mr-1">
                  <i class="fas fa-search mr-1"></i> Review
                </Link>
                <Link :href="`/projects/${project.id}`" class="btn btn-info btn-xs">
                  <i class="fas fa-eye mr-1"></i> Detail
                </Link>
              </td>
            </tr>
            <tr v-if="!recentProjects.length">
              <td colspan="7" class="text-center text-muted py-4">Belum ada permohonan dokumen terbaru.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import StatusBadge from '../../components/StatusBadge.vue';

const props = defineProps({
  totalProjects: { type: Number, default: 0 },
  approvedCount: { type: Number, default: 0 },
  revisionCount: { type: Number, default: 0 },
  rejectedCount: { type: Number, default: 0 },
  pendingCount: { type: Number, default: 0 },
  draftCount: { type: Number, default: 0 },
  recentProjects: { type: Array, default: () => [] },
  chartLabels: { type: Array, default: () => [] },
  chartValues: { type: Array, default: () => [] },
  statusCounts: { type: Object, default: () => ({}) }
});

const userRole = ref('pemohon');
const totalProjects = ref(props.totalProjects);
const approvedCount = ref(props.approvedCount);
const revisionCount = ref(props.revisionCount);
const rejectedCount = ref(props.rejectedCount);
const pendingCount = ref(props.pendingCount);
const draftCount = ref(props.draftCount);
const recentProjects = ref(props.recentProjects);
const chartLabels = ref(props.chartLabels);
const chartValues = ref(props.chartValues);
const statusCounts = ref(props.statusCounts);

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);
const truncate = (str, len) => str && str.length > len ? str.substring(0, len) + '...' : str;

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const renderCharts = () => {
  if (typeof Chart !== 'undefined') {
    const monthlyCtx = document.getElementById('vueMonthlyChart');
    if (monthlyCtx) {
      new Chart(monthlyCtx.getContext('2d'), {
        type: 'line',
        data: {
          labels: chartLabels.value,
          datasets: [{
            label: 'Jumlah Pengajuan',
            data: chartValues.value,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { yAxes: [{ ticks: { beginAtZero: true } }] }
        }
      });
    }

    const statusCtx = document.getElementById('vueStatusChart');
    if (statusCtx) {
      new Chart(statusCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
          labels: Object.keys(statusCounts.value),
          datasets: [{
            data: Object.values(statusCounts.value),
            backgroundColor: ['#6c757d', '#ffc107', '#fd7e14', '#28a745', '#dc3545']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: { position: 'bottom' }
        }
      });
    }
  }
};

const loadDashboard = async () => {
  const response = await window.axios.get('/api/v1/dashboard');
  const data = response.data.data;

  totalProjects.value = data.total_projects;
  approvedCount.value = data.approved_count;
  revisionCount.value = data.revision_count;
  rejectedCount.value = data.rejected_count;
  pendingCount.value = data.pending_count;
  draftCount.value = data.draft_count;
  recentProjects.value = data.recent_projects.data || [];
  chartLabels.value = data.chart_labels || [];
  chartValues.value = data.chart_values || [];
  statusCounts.value = data.status_counts || {};
};

const loadMe = async () => {
  const response = await window.axios.get('/api/v1/me');
  userRole.value = response.data.data.role || 'pemohon';
};

onMounted(async () => {
  await Promise.all([loadDashboard(), loadMe()]);
  renderCharts();
});
</script>
