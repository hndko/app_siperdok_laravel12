<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | SIPERDOK - Sistem Informasi Persetujuan Dokumen</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style AdminLTE 3.2.0 -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    <!-- Chart.js Plugin -->
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
    
    <style>
        .main-header { border-bottom: 2px solid #007bff; }
        .brand-link { background-color: #1e293b; color: #fff !important; font-weight: bold; }
        .sidebar-dark-primary { background-color: #0f172a !important; }
        .badge-draft { background-color: #6c757d; color: #fff; }
        .badge-submitted { background-color: #17a2b8; color: #fff; }
        .badge-in_review { background-color: #ffc107; color: #1f2937; }
        .badge-revision { background-color: #fd7e14; color: #fff; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-rejected { background-color: #dc3545; color: #fff; }
    </style>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div id="vue-app" class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link"><i class="fas fa-home mr-1"></i> Beranda</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge">{{ Auth::user()->notifications()->where('is_read', false)->count() }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">Notifikasi Sistem</span>
                    <div class="dropdown-divider"></div>
                    @forelse(Auth::user()->notifications()->with('project')->latest()->limit(5)->get() as $notif)
                        <a href="{{ $notif->project_id ? route('projects.show', $notif->project_id) : '#' }}" class="dropdown-item">
                            <i class="fas fa-info-circle mr-2 text-{{ $notif->type }}"></i> 
                            <span class="text-wrap small">{{ Str::limit($notif->title, 35) }}</span>
                        </a>
                        <div class="dropdown-divider"></div>
                    @empty
                        <span class="dropdown-item text-muted text-center">Tidak ada notifikasi baru</span>
                    @endforelse
                </div>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff" class="user-image img-circle elevation-2" alt="User Image">
                    <span class="d-none d-md-inline font-weight-bold">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-primary">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=fff&color=0D8ABC" class="img-circle elevation-2" alt="User Image">
                        <p>
                            {{ Auth::user()->name }}
                            <small>{{ Auth::user()->company_name ?? 'Pengguna Sistem' }}</small>
                            <span class="badge badge-light mt-1">{{ strtoupper(Auth::user()->getRoleNames()->first() ?? 'USER') }}</span>
                        </p>
                    </li>
                    <li class="user-footer">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-flat btn-block"><i class="fas fa-sign-out-alt mr-1"></i> Keluar (Logout)</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link text-center py-3">
            <i class="fas fa-file-contract text-warning mr-2"></i>
            <span class="brand-text font-weight-bold">SI PERDOK</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block text-white font-weight-bold">{{ Str::limit(Auth::user()->name, 18) }}</a>
                    <span class="badge badge-success small"><i class="fas fa-circle text-xs mr-1"></i> Online</span>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @role('pemohon')
                    <li class="nav-header">PERMOHONAN DOKUMEN</li>
                    <li class="nav-item">
                        <a href="{{ route('projects.create') }}" class="nav-link {{ request()->routeIs('projects.create') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-plus-circle text-success"></i>
                            <p>Buat Pengajuan Baru</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.index') || request()->routeIs('projects.show') || request()->routeIs('projects.edit') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-folder-open text-info"></i>
                            <p>Daftar Permohonan Saya</p>
                        </a>
                    </li>
                    @endrole

                    @role('penilai|admin')
                    <li class="nav-header">PENILAIAN & EVALUASI</li>
                    <li class="nav-item">
                        <a href="{{ route('assessments.index') }}" class="nav-link {{ request()->routeIs('assessments.index') || request()->routeIs('assessments.review') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tasks text-warning"></i>
                            <p>Penilaian Permohonan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('assessments.history') }}" class="nav-link {{ request()->routeIs('assessments.history') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history text-cyan"></i>
                            <p>Histori Penilaian / Log</p>
                        </a>
                    </li>
                    @endrole

                    @role('admin')
                    <li class="nav-header">MASTER DATA & PENGATURAN</li>
                    <li class="nav-item">
                        <a href="{{ route('master.users.index') }}" class="nav-link {{ request()->routeIs('master.users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Manajemen User</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('master.document-types.index') }}" class="nav-link {{ request()->routeIs('master.document-types.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Jenis Dokumen</p>
                        </a>
                    </li>
                    @endrole

                    <li class="nav-header">LAPORAN & EXPORT</li>
                    <li class="nav-item">
                        <a href="{{ route('export.projects.csv') }}" class="nav-link">
                            <i class="nav-icon fas fa-file-excel text-success"></i>
                            <p>Export Excel (CSV)</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                            <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="icon fas fa-check mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="icon fas fa-ban mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            <b>Versi</b> 1.0.0 (Laravel 12 + AdminLTE 3.2)
        </div>
        <strong>&copy; 2026 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>
@stack('scripts')
</body>
</html>
