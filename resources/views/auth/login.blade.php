@extends('layouts.app-auth')

@section('title', 'Login')
@section('body-class', 'login-page')

@push('styles')
<style>
    .login-box { width: 420px; }
    .btn-role { border-radius: 8px; font-weight: 600; text-align: left; }
</style>
@endpush

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary auth-card">
        <div class="card-header text-center py-4">
            <a href="#" class="h2 text-dark font-weight-bold">
                <i class="fas fa-file-contract text-primary mr-2"></i><b>SI PERDOK</b>
            </a>
            <p class="text-muted mb-0 small">Sistem Informasi Persetujuan Dokumen Kelayakan</p>
        </div>
        <div class="card-body login-card-body p-4">
            <p class="login-box-msg font-weight-bold text-secondary">Masuk ke dalam Akun Anda</p>

            @if(session('info'))
                <div class="alert alert-info py-2 small">{{ session('info') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="post" id="loginForm">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember" class="small text-muted">Ingat Saya</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">Masuk</button>
                    </div>
                </div>
            </form>

            <div class="border-top pt-3 mt-3">
                <p class="small text-muted font-weight-bold mb-2"><i class="fas fa-key mr-1"></i> Quick Demo Login Credentials:</p>
                <div class="btn-group-vertical w-100">
                    <button class="btn btn-outline-info btn-sm btn-role mb-1" onclick="fillLogin('pemohon@example.com', 'password')">
                        <i class="fas fa-user-tie mr-2"></i> Pemohon: <code>pemohon@example.com</code>
                    </button>
                    <button class="btn btn-outline-success btn-sm btn-role mb-1" onclick="fillLogin('penilai@example.com', 'password')">
                        <i class="fas fa-user-check mr-2"></i> Penilai: <code>penilai@example.com</code>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm btn-role" onclick="fillLogin('admin@example.com', 'password')">
                        <i class="fas fa-user-shield mr-2"></i> Admin: <code>admin@example.com</code>
                    </button>
                </div>
            </div>

            <p class="mb-0 mt-3 text-center">
                Belum punya akun? <a href="{{ route('register') }}" class="font-weight-bold">Daftar Akun Pemohon</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillLogin(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endpush
