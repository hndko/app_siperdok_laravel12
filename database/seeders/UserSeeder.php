<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = Hash::make('password');

        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator Sistem',
                'phone' => '081234567890',
                'nip_nik' => '198501012010011001',
                'company_name' => 'Kementerian / Instansi Pemerintah',
                'password' => $passwordHash,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Create default test accounts for Pemohon & Penilai for easy demonstration
        $defaultPemohon = User::firstOrCreate(
            ['email' => 'pemohon@example.com'],
            [
                'name' => 'Budi Pemohon (PT Maju Bersama)',
                'phone' => '08111222333',
                'nip_nik' => '3171010101800001',
                'company_name' => 'PT Maju Bersama Sejahtera',
                'password' => $passwordHash,
                'email_verified_at' => now(),
            ]
        );
        $defaultPemohon->assignRole('pemohon');

        $defaultPenilai = User::firstOrCreate(
            ['email' => 'penilai@example.com'],
            [
                'name' => 'Dr. Hendra Penilai (Tim Penilai Environmental)',
                'phone' => '08199887766',
                'nip_nik' => '197505052002121002',
                'company_name' => 'Dinas Lingkungan Hidup',
                'password' => $passwordHash,
                'email_verified_at' => now(),
            ]
        );
        $defaultPenilai->assignRole('penilai');

        // 2. Batch Seed 1,000 Pemohon users and 1,000 Penilai users
        $rolePemohonId = Role::findByName('pemohon')->id;
        $rolePenilaiId = Role::findByName('penilai')->id;

        $now = now()->toDateTimeString();

        // Seed remaining Pemohon (999 users)
        $pemohonData = [];
        $modelHasRolesData = [];

        for ($i = 2; $i <= 1000; $i++) {
            $pemohonData[] = [
                'name' => "Pemohon User $i",
                'email' => "pemohon$i@example.com",
                'phone' => '08'.str_pad($i, 10, '0', STR_PAD_LEFT),
                'nip_nik' => '3171'.str_pad($i, 12, '0', STR_PAD_LEFT),
                'company_name' => "PT Perusahaan Mandiri $i",
                'password' => $passwordHash,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($pemohonData, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Fetch inserted IDs for role mapping
        $pemohonUserIds = DB::table('users')
            ->where('email', 'like', 'pemohon%@example.com')
            ->where('id', '!=', $defaultPemohon->id)
            ->pluck('id');

        foreach ($pemohonUserIds as $userId) {
            $modelHasRolesData[] = [
                'role_id' => $rolePemohonId,
                'model_type' => User::class,
                'model_id' => $userId,
            ];
        }

        // Seed remaining Penilai (999 users)
        $penilaiData = [];
        for ($i = 2; $i <= 1000; $i++) {
            $penilaiData[] = [
                'name' => "Penilai Dokumen $i",
                'email' => "penilai$i@example.com",
                'phone' => '08'.str_pad($i + 1000, 10, '0', STR_PAD_LEFT),
                'nip_nik' => '1980'.str_pad($i, 12, '0', STR_PAD_LEFT),
                'company_name' => "Tim Evaluator Wilayah $i",
                'password' => $passwordHash,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($penilaiData, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $penilaiUserIds = DB::table('users')
            ->where('email', 'like', 'penilai%@example.com')
            ->where('id', '!=', $defaultPenilai->id)
            ->pluck('id');

        foreach ($penilaiUserIds as $userId) {
            $modelHasRolesData[] = [
                'role_id' => $rolePenilaiId,
                'model_type' => User::class,
                'model_id' => $userId,
            ];
        }

        foreach (array_chunk($modelHasRolesData, 1000) as $roleChunk) {
            DB::table('model_has_roles')->insert($roleChunk);
        }
    }
}
