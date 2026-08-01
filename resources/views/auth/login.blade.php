<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | SIPERDOK</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <style>
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); height: 100vh; }
        .login-box { width: 420px; }
        .card { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .btn-role { border-radius: 8px; font-weight: 600; text-align: left; }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
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
                    <button class="btn btn-outline-info btn-sm btn-role mb-1" onclick="fillLogin('pemohon@siperdok.go.id', 'password123')">
                        <i class="fas fa-user-tie mr-2"></i> Pemohon: <code>pemohon@siperdok.go.id</code>
                    </button>
                    <button class="btn btn-outline-success btn-sm btn-role mb-1" onclick="fillLogin('penilai@siperdok.go.id', 'password123')">
                        <i class="fas fa-user-check mr-2"></i> Penilai: <code>penilai@siperdok.go.id</code>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm btn-role" onclick="fillLogin('admin@siperdok.go.id', 'password123')">
                        <i class="fas fa-user-shield mr-2"></i> Admin: <code>admin@siperdok.go.id</code>
                    </button>
                </div>
            </div>

            <p class="mb-0 mt-3 text-center">
                Belum punya akun? <a href="{{ route('register') }}" class="font-weight-bold">Daftar Akun Pemohon</a>
            </p>
        </div>
    </div>
</div>

<script>
    function fillLogin(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
</body>
</html>
