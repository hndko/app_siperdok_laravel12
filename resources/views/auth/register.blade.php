<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Akun Pemohon | SIPERDOK</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    <style>
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px 0; }
        .register-box { width: 520px; }
        .card { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
    </style>
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="card card-outline card-success">
        <div class="card-header text-center py-3">
            <a href="#" class="h2 text-dark font-weight-bold">
                <i class="fas fa-file-contract text-success mr-2"></i><b>SI PERDOK</b>
            </a>
            <p class="text-muted mb-0 small">Pendaftaran Akun Pemohon Dokumen Kelayakan</p>
        </div>
        <div class="card-body register-card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="post">
                @csrf
                <div class="form-group mb-3">
                    <label class="small font-weight-bold">Nama Lengkap Pemohon / Penanggung Jawab</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" value="{{ old('name') }}" required>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold">Email Perusahaan / Resmi</label>
                    <input type="email" name="email" class="form-control" placeholder="alamat@email.com" value="{{ old('email') }}" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="small font-weight-bold">No. Telepon / Whatsapp</label>
                        <input type="text" name="phone" class="form-control" placeholder="08123456789" value="{{ old('phone') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="small font-weight-bold">NIK / NIP Pemohon</label>
                        <input type="text" name="nip_nik" class="form-control" placeholder="3171xxxxxxxx" value="{{ old('nip_nik') }}" required>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="small font-weight-bold">Nama Perusahaan / Instansi Pemohon</label>
                    <input type="text" name="company_name" class="form-control" placeholder="PT Contoh Sejahtera" value="{{ old('company_name') }}" required>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="small font-weight-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 8 Karakter" required>
                    </div>
                    <div class="col-md-6">
                        <label class="small font-weight-bold">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi Password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-block font-weight-bold py-2"><i class="fas fa-user-plus mr-1"></i> Daftar Sekarang</button>
            </form>

            <p class="mb-0 mt-3 text-center small">
                Sudah memiliki akun? <a href="{{ route('login') }}" class="font-weight-bold">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
