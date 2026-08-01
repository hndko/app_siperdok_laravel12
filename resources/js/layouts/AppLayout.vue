<template>
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <Link href="/dashboard" class="nav-link"><i class="fas fa-home mr-1"></i> Beranda</Link>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto align-items-center">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown mr-2">
          <a class="nav-link position-relative" data-toggle="dropdown" href="#">
            <i class="far fa-bell fa-lg"></i>
            <span v-if="unreadNotificationsCount > 0" class="badge badge-warning navbar-badge position-absolute" style="top: 2px; right: 2px;">{{ unreadNotificationsCount }}</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow">
            <div class="dropdown-item dropdown-header d-flex justify-content-between align-items-center">
              <span class="font-weight-bold">Notifikasi Sistem</span>
              <button type="button" class="btn btn-link btn-xs p-0" :disabled="markingAllRead || unreadNotificationsCount === 0" @click.stop="markAllNotificationsRead">
                Tandai semua dibaca
              </button>
            </div>
            <div class="dropdown-divider"></div>
            <div
              v-for="notif in notifications" 
              :key="notif.id" 
              :class="['dropdown-item py-2', !notif.is_read ? 'bg-light font-weight-bold' : '']"
            >
              <i :class="['fas fa-info-circle mr-2', `text-${notif.type}`]"></i> 
              <Link :href="notif.project_id ? `/projects/${notif.project_id}` : '#'" class="text-reset">
                <span class="text-wrap small">{{ notif.title }}</span>
              </Link>
              <button
                v-if="!notif.is_read"
                type="button"
                class="btn btn-link btn-xs float-right p-0"
                :disabled="markingNotificationId === notif.id"
                @click.stop="markNotificationRead(notif)"
              >
                Dibaca
              </button>
            </div>
            <div v-if="!notifications || !notifications.length" class="dropdown-item text-muted text-center py-2">Tidak ada notifikasi baru</div>
          </div>
        </li>

        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user ? user.name : 'User')}&background=0D8ABC&color=fff`" class="user-image img-circle elevation-1 mr-2" alt="User Image">
            <span class="d-none d-md-inline font-weight-bold text-dark">{{ user ? user.name : 'User' }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow">
            <li class="user-header bg-primary text-center p-3">
              <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user ? user.name : 'User')}&background=fff&color=0D8ABC`" class="img-circle elevation-2 mb-2" style="width: 70px; height: 70px;" alt="User Image">
              <p class="mb-0 text-white font-weight-bold">
                {{ user ? user.name : 'User' }}
              </p>
              <small class="text-white-50 d-block">{{ user ? (user.company_name || 'Pengguna Sistem') : '' }}</small>
              <span class="badge badge-light mt-2">{{ (userRole || 'USER').toUpperCase() }}</span>
            </li>
            <li class="user-footer p-2 bg-light border-bottom">
              <Link href="/profile" class="btn btn-default btn-flat btn-block font-weight-bold">
                <i class="fas fa-user-cog mr-1"></i> Edit Profil
              </Link>
            </li>
            <li class="user-footer p-2 bg-light">
              <button type="button" class="btn btn-danger btn-flat btn-block font-weight-bold" @click="logout">
                <i class="fas fa-sign-out-alt mr-1"></i> Keluar (Logout)
              </button>
            </li>
          </ul>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <Link href="/dashboard" class="brand-link text-center py-3">
        <i class="fas fa-file-contract text-warning mr-2"></i>
        <span class="brand-text font-weight-bold">SI PERDOK</span>
      </Link>

      <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
          <div class="image">
            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user ? user.name : 'User')}&background=0D8ABC&color=fff`" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="#" class="d-block text-white font-weight-bold text-truncate" style="max-width: 170px;">{{ user ? user.name : 'User' }}</a>
            <span class="badge badge-success small"><i class="fas fa-circle text-xs mr-1"></i> Vue 3 SPA</span>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <Link href="/dashboard" class="nav-link" :class="{ active: currentPath.includes('/dashboard') }">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </Link>
            </li>

            <template v-if="isPemohon">
              <li class="nav-header">PERMOHONAN DOKUMEN</li>
              <li class="nav-item">
                <Link href="/projects/create" class="nav-link" :class="{ active: currentPath.includes('/projects/create') }">
                  <i class="nav-icon fas fa-plus-circle text-success"></i>
                  <p>Buat Pengajuan Baru</p>
                </Link>
              </li>
              <li class="nav-item">
                <Link href="/projects" class="nav-link" :class="{ active: currentPath === '/projects' }">
                  <i class="nav-icon fas fa-folder-open text-info"></i>
                  <p>Daftar Permohonan Saya</p>
                </Link>
              </li>
            </template>

            <template v-if="isPenilai || isAdmin">
              <li class="nav-header">PENILAIAN & EVALUASI</li>
              <li class="nav-item">
                <Link href="/assessments" class="nav-link" :class="{ active: currentPath === '/assessments' }">
                  <i class="nav-icon fas fa-tasks text-warning"></i>
                  <p>Penilaian Permohonan</p>
                </Link>
              </li>
              <li class="nav-item">
                <Link href="/assessments/history" class="nav-link" :class="{ active: currentPath.includes('/assessments/history') }">
                  <i class="nav-icon fas fa-history text-cyan"></i>
                  <p>Histori Penilaian / Log</p>
                </Link>
              </li>
            </template>

            <template v-if="isAdmin">
              <li class="nav-header">MASTER DATA & PENGATURAN</li>
              <li class="nav-item">
                <Link href="/master/users" class="nav-link" :class="{ active: currentPath.includes('/master/users') }">
                  <i class="nav-icon fas fa-users"></i>
                  <p>Manajemen User</p>
                </Link>
              </li>
              <li class="nav-item">
                <Link href="/master/document-types" class="nav-link" :class="{ active: currentPath.includes('/master/document-types') }">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>Jenis Dokumen</p>
                </Link>
              </li>
            </template>

            <li class="nav-header">LAPORAN & EXPORT</li>
            <li class="nav-item">
              <button type="button" class="nav-link btn btn-link text-left w-100" @click="downloadExport('csv')">
                <i class="nav-icon fas fa-file-csv text-success"></i>
                <p>Export CSV</p>
              </button>
            </li>
            <li class="nav-item" v-if="isPenilai || isAdmin">
              <button type="button" class="nav-link btn btn-link text-left w-100" @click="downloadExport('xlsx')">
                <i class="nav-icon fas fa-file-excel text-success"></i>
                <p>Export Excel (.xlsx)</p>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
              <h1 class="m-0 font-weight-bold text-dark">{{ pageTitle }}</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right mb-0">
                <li class="breadcrumb-item"><Link href="/dashboard">Beranda</Link></li>
                <li class="breadcrumb-item active">{{ pageTitle }}</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <slot></slot>
        </div>
      </section>
    </div>

    <footer class="main-footer">
      <div class="float-right d-none d-sm-inline">
        <b>Frontend</b> Vue 3 SPA + Vue Router + AdminLTE 3.2
      </div>
      <strong>&copy; 2026 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan.</strong> All rights reserved.
    </footer>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { apiErrorMessage, confirmAction, toast } from '../lib/feedback';

const props = defineProps({
  pageTitle: { type: String, default: 'Dashboard' }
});

const route = useRoute();
const router = useRouter();
const apiUser = ref(null);
const apiRole = ref(null);
const apiNotifications = ref([]);
const apiUnreadNotificationsCount = ref(0);
const markingNotificationId = ref(null);
const markingAllRead = ref(false);
const user = computed(() => apiUser.value);
const userRole = computed(() => apiRole.value || 'pemohon');
const notifications = computed(() => apiNotifications.value);
const unreadNotificationsCount = computed(() => apiUnreadNotificationsCount.value);

const isPemohon = computed(() => userRole.value === 'pemohon');
const isPenilai = computed(() => userRole.value === 'penilai');
const isAdmin = computed(() => userRole.value === 'admin');

const currentPath = computed(() => route.path);

const initAdminLTEWidgets = () => {
  if (typeof $ !== 'undefined') {
    // Re-initialize PushMenu & Treeview for AdminLTE
    if ($.fn.PushMenu) {
      $('[data-widget="pushmenu"]').PushMenu();
    }
    if ($.fn.Treeview) {
      $('[data-widget="treeview"]').Treeview('init');
    }
  }
};

const loadCurrentUser = async () => {
  if (!localStorage.getItem('siperdok_token')) {
    return;
  }

  try {
    const response = await window.axios.get('/api/v1/me');
    apiUser.value = response.data.data.user;
    apiRole.value = response.data.data.role;
    apiNotifications.value = response.data.data.notifications || [];
    apiUnreadNotificationsCount.value = response.data.data.unread_notifications_count || 0;
  } catch {
    localStorage.removeItem('siperdok_token');
    router.push('/login');
  }
};

const loadNotifications = async () => {
  const response = await window.axios.get('/api/v1/notifications');
  const data = response.data.data;
  apiNotifications.value = data.notifications.data || data.notifications || [];
  apiUnreadNotificationsCount.value = data.unread_count || 0;
};

const markNotificationRead = async (notification) => {
  markingNotificationId.value = notification.id;
  const previous = { notifications: [...apiNotifications.value], count: apiUnreadNotificationsCount.value };

  notification.is_read = true;
  apiUnreadNotificationsCount.value = Math.max(0, apiUnreadNotificationsCount.value - 1);

  try {
    const response = await window.axios.patch(`/api/v1/notifications/${notification.id}/read`);
    apiUnreadNotificationsCount.value = response.data.data.unread_count;
  } catch (error) {
    apiNotifications.value = previous.notifications;
    apiUnreadNotificationsCount.value = previous.count;
    toast('error', apiErrorMessage(error, 'Notifikasi gagal ditandai dibaca.'));
  } finally {
    markingNotificationId.value = null;
  }
};

const markAllNotificationsRead = async () => {
  markingAllRead.value = true;
  const previous = { notifications: [...apiNotifications.value], count: apiUnreadNotificationsCount.value };

  apiNotifications.value = apiNotifications.value.map((notification) => ({ ...notification, is_read: true }));
  apiUnreadNotificationsCount.value = 0;

  try {
    await window.axios.patch('/api/v1/notifications/read-all');
  } catch (error) {
    apiNotifications.value = previous.notifications;
    apiUnreadNotificationsCount.value = previous.count;
    toast('error', apiErrorMessage(error, 'Notifikasi gagal ditandai semua dibaca.'));
  } finally {
    markingAllRead.value = false;
  }
};

const logout = async () => {
  const confirmed = await confirmAction({
    title: 'Keluar dari akun?',
    text: 'Sesi Anda akan diakhiri dari perangkat ini.',
    confirmButtonText: 'Ya, keluar',
    confirmButtonColor: '#dc3545',
  });

  if (!confirmed) {
    return;
  }

  try {
    await window.axios.post('/api/v1/logout');
  } finally {
    localStorage.removeItem('siperdok_token');
    delete window.axios.defaults.headers.common.Authorization;
    router.push('/login');
  }
};

const downloadExport = async (type) => {
  try {
    const response = await window.axios.get(`/api/v1/exports/projects/${type}`, {
      responseType: 'blob',
    });
    const url = URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.download = `siperdok-projects.${type}`;
    link.click();
    URL.revokeObjectURL(url);
    toast('success', `Export ${type.toUpperCase()} berhasil diunduh.`);
  } catch (error) {
    toast('error', apiErrorMessage(error, `Export ${type.toUpperCase()} gagal diunduh.`));
  }
};

onMounted(() => {
  loadCurrentUser();
  loadNotifications();
  window.addEventListener('siperdok:profile-updated', loadCurrentUser);
  initAdminLTEWidgets();
  router.afterEach(() => {
    setTimeout(initAdminLTEWidgets, 100);
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('siperdok:profile-updated', loadCurrentUser);
});
</script>
