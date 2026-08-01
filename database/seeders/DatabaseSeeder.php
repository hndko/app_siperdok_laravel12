<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            DocumentTypeSeeder::class,
            VerificationChecklistItemSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}
