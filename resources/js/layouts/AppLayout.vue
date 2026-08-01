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
            <span class="dropdown-item dropdown-header font-weight-bold">Notifikasi Sistem</span>
            <div class="dropdown-divider"></div>
            <Link 
              v-for="notif in notifications" 
              :key="notif.id" 
              :href="notif.project_id ? `/projects/${notif.project_id}` : '#'" 
              class="dropdown-item py-2"
            >
              <i :class="['fas fa-info-circle mr-2', `text-${notif.type}`]"></i> 
              <span class="text-wrap small">{{ notif.title }}</span>
            </Link>
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
            <li class="user-footer p-2 bg-light">
              <Link href="/logout" method="post" as="button" class="btn btn-danger btn-flat btn-block font-weight-bold">
                <i class="fas fa-sign-out-alt mr-1"></i> Keluar (Logout)
              </Link>
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
              <a href="/export/projects/csv" class="nav-link">
                <i class="nav-icon fas fa-file-excel text-success"></i>
                <p>Export Excel (CSV)</p>
              </a>
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
          <div v-if="flash.success" class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ flash.success }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div v-if="flash.error" class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ flash.error }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <slot></slot>
        </div>
      </section>
    </div>

    <footer class="main-footer">
      <div class="float-right d-none d-sm-inline">
        <b>Frontend</b> Vue 3 SPA (Inertia.js) + AdminLTE 3.2
      </div>
      <strong>&copy; 2026 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan.</strong> All rights reserved.
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';

const props = defineProps({
  pageTitle: { type: String, default: 'Dashboard' }
});

const page = usePage();
const user = computed(() => page.props.auth ? page.props.auth.user : null);
const userRole = computed(() => page.props.auth ? page.props.auth.role : 'pemohon');
const notifications = computed(() => page.props.notifications || []);
const unreadNotificationsCount = computed(() => page.props.unreadNotificationsCount || 0);
const flash = computed(() => page.props.flash || {});

const isPemohon = computed(() => userRole.value === 'pemohon');
const isPenilai = computed(() => userRole.value === 'penilai');
const isAdmin = computed(() => userRole.value === 'admin');

const currentPath = computed(() => window.location.pathname);

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

onMounted(() => {
  initAdminLTEWidgets();
  router.on('navigate', () => {
    setTimeout(initAdminLTEWidgets, 100);
  });
});
</script>
