<template>
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
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
          <a href="#" class="nav-link dropdown-toggle siperdok-user-toggle" data-toggle="dropdown" aria-label="Menu akun pengguna">
            <img :src="avatarUrl" class="siperdok-navbar-avatar" alt="Avatar pengguna">
            <span class="siperdok-navbar-identity d-none d-md-flex">
              <span class="siperdok-navbar-name">{{ user ? user.name : 'User' }}</span>
              <span class="siperdok-navbar-role">{{ roleLabel }}</span>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right siperdok-user-dropdown">
            <li class="siperdok-user-card">
              <div class="siperdok-user-card-header">
                <img :src="avatarUrl" class="siperdok-user-card-avatar" alt="Avatar pengguna">
                <div class="siperdok-user-card-meta">
                  <div class="siperdok-user-card-name">{{ user ? user.name : 'User' }}</div>
                  <div class="siperdok-user-card-company">{{ user ? (user.company_name || 'Pengguna Sistem') : 'Pengguna Sistem' }}</div>
                  <span class="siperdok-role-chip">
                    <i class="fas fa-user-shield mr-1"></i>{{ roleLabel }}
                  </span>
                </div>
              </div>
            </li>
            <li class="siperdok-user-actions">
              <Link href="/profile" class="siperdok-user-action">
                <span class="siperdok-action-icon"><i class="fas fa-user-cog"></i></span>
                <span>Edit Profil</span>
              </Link>
              <button type="button" class="siperdok-user-action siperdok-user-action-danger" @click="logout">
                <span class="siperdok-action-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Keluar</span>
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

            <template v-if="canExportReports">
              <li class="nav-header">LAPORAN & EXPORT</li>
              <li class="nav-item">
                <button type="button" class="nav-link btn btn-link text-left w-100" @click="downloadExport('csv')">
                  <i class="nav-icon fas fa-file-csv text-success"></i>
                  <p>Export CSV</p>
                </button>
              </li>
              <li class="nav-item">
                <button type="button" class="nav-link btn btn-link text-left w-100" @click="downloadExport('xlsx')">
                  <i class="nav-icon fas fa-file-excel text-success"></i>
                  <p>Export Excel (.xlsx)</p>
                </button>
              </li>
            </template>
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
const notificationPollTimer = ref(null);
const user = computed(() => apiUser.value);
const userRole = computed(() => apiRole.value || 'pemohon');
const notifications = computed(() => apiNotifications.value);
const unreadNotificationsCount = computed(() => apiUnreadNotificationsCount.value);
const roleLabel = computed(() => {
  const labels = {
    admin: 'Admin',
    pemohon: 'Pemohon',
    penilai: 'Penilai',
  };

  return labels[userRole.value] || 'User';
});
const avatarUrl = computed(() => {
  const name = encodeURIComponent(user.value?.name || 'User');

  return `https://ui-avatars.com/api/?name=${name}&background=0D8ABC&color=fff`;
});

const isPemohon = computed(() => userRole.value === 'pemohon');
const isPenilai = computed(() => userRole.value === 'penilai');
const isAdmin = computed(() => userRole.value === 'admin');
const canExportReports = computed(() => isPenilai.value || isAdmin.value);

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
  if (!localStorage.getItem('siperdok_token')) {
    return;
  }

  try {
    const response = await window.axios.get('/api/v1/notifications');
    const data = response.data.data;
    apiNotifications.value = data.notifications.data || data.notifications || [];
    apiUnreadNotificationsCount.value = data.unread_count || 0;
  } catch (error) {
    if (error.response?.status === 401) {
      localStorage.removeItem('siperdok_token');
      delete window.axios.defaults.headers.common.Authorization;
      router.push('/login');
    }
  }
};

const stopNotificationPolling = () => {
  if (!notificationPollTimer.value) {
    return;
  }

  window.clearInterval(notificationPollTimer.value);
  notificationPollTimer.value = null;
};

const startNotificationPolling = () => {
  stopNotificationPolling();

  notificationPollTimer.value = window.setInterval(() => {
    if (document.visibilityState === 'visible') {
      loadNotifications();
    }
  }, 15000);
};

const refreshNotificationsWhenVisible = () => {
  if (document.visibilityState === 'visible') {
    loadNotifications();
  }
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
  if (!canExportReports.value) {
    return;
  }

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
  document.addEventListener('visibilitychange', refreshNotificationsWhenVisible);
  startNotificationPolling();
  initAdminLTEWidgets();
  router.afterEach(() => {
    setTimeout(initAdminLTEWidgets, 100);
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('siperdok:profile-updated', loadCurrentUser);
  document.removeEventListener('visibilitychange', refreshNotificationsWhenVisible);
  stopNotificationPolling();
});
</script>

<style scoped>
.siperdok-user-toggle {
  gap: 10px;
  min-height: 46px;
  padding: 6px 12px;
  border-radius: 8px;
  color: #111827;
  transition: background-color 160ms ease, color 160ms ease;
}

.siperdok-user-toggle:hover,
.siperdok-user-toggle:focus {
  background: #f3f6f9;
  color: #0f172a;
}

.siperdok-navbar-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.16);
}

.siperdok-navbar-identity {
  min-width: 0;
  max-width: 260px;
  flex-direction: column;
  line-height: 1.12;
}

.siperdok-navbar-name {
  overflow: hidden;
  color: #111827;
  font-size: 0.94rem;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.siperdok-navbar-role {
  margin-top: 2px;
  color: #64748b;
  font-size: 0.76rem;
  font-weight: 600;
}

.siperdok-user-dropdown {
  width: min(340px, calc(100vw - 24px));
  padding: 8px;
  border: 1px solid #dbe3ec;
  border-radius: 8px;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.12);
}

.siperdok-user-card {
  padding: 6px;
}

.siperdok-user-card-header {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 10px;
  border-radius: 8px;
  background: #f8fafc;
}

.siperdok-user-card-avatar {
  width: 56px;
  height: 56px;
  flex: 0 0 auto;
  border: 3px solid #ffffff;
  border-radius: 50%;
  box-shadow: 0 3px 8px rgba(15, 23, 42, 0.18);
}

.siperdok-user-card-meta {
  min-width: 0;
}

.siperdok-user-card-name {
  overflow: hidden;
  color: #0f172a;
  font-weight: 800;
  line-height: 1.25;
  text-overflow: ellipsis;
}

.siperdok-user-card-company {
  overflow: hidden;
  margin-top: 2px;
  color: #475569;
  font-size: 0.83rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.siperdok-role-chip {
  display: inline-flex;
  align-items: center;
  margin-top: 8px;
  padding: 4px 8px;
  border: 1px solid #bfdbfe;
  border-radius: 999px;
  background: #eff6ff;
  color: #075985;
  font-size: 0.76rem;
  font-weight: 800;
}

.siperdok-user-actions {
  display: grid;
  gap: 6px;
  padding: 6px;
}

.siperdok-user-action {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 10px;
  padding: 10px 11px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: #1f2937;
  font-weight: 700;
  text-align: left;
  transition: background-color 160ms ease, color 160ms ease;
}

.siperdok-user-action:hover,
.siperdok-user-action:focus {
  background: #eef2f7;
  color: #0f172a;
  text-decoration: none;
}

.siperdok-user-action-danger {
  color: #b91c1c;
}

.siperdok-user-action-danger:hover,
.siperdok-user-action-danger:focus {
  background: #fef2f2;
  color: #991b1b;
}

.siperdok-action-icon {
  display: inline-flex;
  width: 30px;
  height: 30px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: #e2e8f0;
  color: currentColor;
}

@media (max-width: 575.98px) {
  .siperdok-user-toggle {
    padding-inline: 8px;
  }
}
</style>
