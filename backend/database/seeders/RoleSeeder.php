<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
        'application.create', 'application.update', 'application.submit',
        'application.view-own', 'application.view-all',
        'application.review', 'application.approve', 'application.reject',
        'application.request-revision', 'dashboard.view', 'export.data',
    ];

    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    $pemohon = Role::firstOrCreate(['name' => 'pemohon', 'guard_name' => 'web']);
    $pemohon->syncPermissions([
        'application.create', 'application.update', 'application.submit',
        'application.view-own', 'dashboard.view',
    ]);

    $penilai = Role::firstOrCreate(['name' => 'penilai', 'guard_name' => 'web']);
    $penilai->syncPermissions([
        'application.view-all', 'application.review', 'application.approve',
        'application.reject', 'application.request-revision',
        'dashboard.view', 'export.data',
    ]);
    }
}
