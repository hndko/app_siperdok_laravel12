@extends('layouts.app-modules')

@section('title', 'Manajemen User & Role')

@section('content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-users text-primary mr-2"></i> Master Data Pengguna System</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('master.users.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama / Email / Perusahaan / NIK..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-2">
                    <select name="role" class="form-control">
                        <option value="">-- Semua Role --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ strtoupper($r->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold"><i class="fas fa-search mr-1"></i> Cari User</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped border align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Perusahaan / Instansi</th>
                        <th>NIK / NIP</th>
                        <th>Role Access</th>
                        <th>Tgl Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>{{ $u->id }}</td>
                            <td class="font-weight-bold text-dark">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->company_name ?? '-' }}</td>
                            <td>{{ $u->nip_nik ?? '-' }}</td>
                            <td>
                                @foreach($u->roles as $role)
                                    @php
                                        $rClass = match($role->name) {
                                            'admin' => 'badge-danger',
                                            'penilai' => 'badge-success',
                                            'pemohon' => 'badge-info',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $rClass }} px-2 py-1">{{ strtoupper($role->name) }}</span>
                                @endforeach
                            </td>
                            <td class="small">{{ $u->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Pengguna tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="small text-muted">Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ number_format($users->total()) }} pengguna</div>
            <div>{{ $users->links() }}</div>
        </div>
    </div>
</div>
@endsection
