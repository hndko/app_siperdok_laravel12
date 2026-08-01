<template>
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a :href="dashboardUrl" class="nav-link"><i class="fas fa-home mr-1"></i> Beranda</a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge">{{ unreadNotificationsCount }}</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">Notifikasi Sistem</span>
            <div class="dropdown-divider"></div>
            <a 
              v-for="notif in notifications" 
              :key="notif.id" 
              :href="notif.project_id ? `/projects/${notif.project_id}` : '#'" 
              class="dropdown-item"
            >
              <i :class="['fas fa-info-circle mr-2', `text-${notif.type}`]"></i> 
              <span class="text-wrap small">{{ notif.title }}</span>
            </a>
            <div v-if="!notifications.length" class="dropdown-item text-muted text-center">Tidak ada notifikasi baru</div>
          </div>
        </li>

        <!-- User Menu -->
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=0D8ABC&color=fff`" class="user-image img-circle elevation-2" alt="User Image">
            <span class="d-none d-md-inline font-weight-bold">{{ user.name }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <li class="user-header bg-primary">
              <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=fff&color=0D8ABC`" class="img-circle elevation-2" alt="User Image">
              <p>
                {{ user.name }}
                <small>{{ user.company_name || 'Pengguna Sistem' }}</small>
                <span class="badge badge-light mt-1">{{ (userRole || 'USER').toUpperCase() }}</span>
              </p>
            </li>
            <li class="user-footer">
              <form :action="logoutUrl" method="POST">
                <input type="hidden" name="_token" :value="csrfToken">
                <button type="submit" class="btn btn-danger btn-flat btn-block"><i class="fas fa-sign-out-alt mr-1"></i> Keluar (Logout)</button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a :href="dashboardUrl" class="brand-link text-center py-3">
        <i class="fas fa-file-contract text-warning mr-2"></i>
        <span class="brand-text font-weight-bold">SI PERDOK</span>
      </a>

      <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
          <div class="image">
            <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=0D8ABC&color=fff`" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="#" class="d-block text-white font-weight-bold">{{ user.name }}</a>
            <span class="badge badge-success small"><i class="fas fa-circle text-xs mr-1"></i> Online (Vue 3)</span>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="/dashboard" class="nav-link" :class="{ active: currentPath.includes('/dashboard') }">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>

            <template v-if="isPemohon">
              <li class="nav-header">PERMOHONAN DOKUMEN</li>
              <li class="nav-item">
                <a href="/projects/create" class="nav-link" :class="{ active: currentPath.includes('/projects/create') }">
                  <i class="nav-icon fas fa-plus-circle text-success"></i>
                  <p>Buat Pengajuan Baru</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/projects" class="nav-link" :class="{ active: currentPath === '/projects' }">
                  <i class="nav-icon fas fa-folder-open text-info"></i>
                  <p>Daftar Permohonan Saya</p>
                </a>
              </li>
            </template>

            <template v-if="isPenilai || isAdmin">
              <li class="nav-header">PENILAIAN & EVALUASI</li>
              <li class="nav-item">
                <a href="/assessments" class="nav-link" :class="{ active: currentPath === '/assessments' }">
                  <i class="nav-icon fas fa-tasks text-warning"></i>
                  <p>Penilaian Permohonan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/assessments/history" class="nav-link" :class="{ active: currentPath.includes('/assessments/history') }">
                  <i class="nav-icon fas fa-history text-cyan"></i>
                  <p>Histori Penilaian / Log</p>
                </a>
              </li>
            </template>

            <template v-if="isAdmin">
              <li class="nav-header">MASTER DATA & PENGATURAN</li>
              <li class="nav-item">
                <a href="/master/users" class="nav-link" :class="{ active: currentPath.includes('/master/users') }">
                  <i class="nav-icon fas fa-users"></i>
                  <p>Manajemen User</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/master/document-types" class="nav-link" :class="{ active: currentPath.includes('/master/document-types') }">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>Jenis Dokumen</p>
                </a>
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
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0 font-weight-bold text-dark">{{ pageTitle }}</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="/dashboard">Beranda</a></li>
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
        <b>Frontend</b> Vue 3 SFC + AdminLTE 3.2
      </div>
      <strong>&copy; 2026 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan.</strong> All rights reserved.
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  user: { type: Object, required: true },
  userRole: { type: String, default: 'pemohon' },
  pageTitle: { type: String, default: 'Dashboard' },
  csrfToken: { type: String, required: true },
  unreadNotificationsCount: { type: Number, default: 0 },
  notifications: { type: Array, default: () => [] }
});

const dashboardUrl = '/dashboard';
const logoutUrl = '/logout';

const isPemohon = computed(() => props.userRole === 'pemohon');
const isPenilai = computed(() => props.userRole === 'penilai');
const isAdmin = computed(() => props.userRole === 'admin');

const currentPath = computed(() => window.location.pathname);
</script>
