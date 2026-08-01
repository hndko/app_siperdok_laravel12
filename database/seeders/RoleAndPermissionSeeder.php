<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage-users',
            'manage-document-types',
            'view-dashboard',
            'create-project',
            'edit-project',
            'submit-project',
            'review-project',
            'approve-project',
            'reject-project',
            'request-revision-project',
            'view-history',
            'export-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $rolePemohon = Role::firstOrCreate(['name' => 'pemohon']);
        $rolePemohon->givePermissionTo([
            'view-dashboard',
            'create-project',
            'edit-project',
            'submit-project',
            'view-history',
        ]);

        $rolePenilai = Role::firstOrCreate(['name' => 'penilai']);
        $rolePenilai->givePermissionTo([
            'view-dashboard',
            'review-project',
            'approve-project',
            'reject-project',
            'request-revision-project',
            'view-history',
            'export-reports',
        ]);
    }
}
