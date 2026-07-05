<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // Documents
            'view_any_document',
            'view_document',
            'create_document',
            'update_document',
            'delete_document',

            // Document Types
            'view_any_document_type',
            'view_document_type',
            'create_document_type',
            'update_document_type',
            'delete_document_type',

            // Approvals
            'view_any_approval',
            'view_approval',
            'create_approval',
            'update_approval',
            'delete_approval',

            // Activity Log
            'view_activity_log',

            // User Management
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',

            // Role Management
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }

        // Roles
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
        ]);

        $adminAkademik = Role::firstOrCreate([
            'name' => 'Admin Akademik',
        ]);

        $dosen = Role::firstOrCreate([
            'name' => 'Dosen',
        ]);

        $mahasiswa = Role::firstOrCreate([
            'name' => 'Mahasiswa',
        ]);

        // super admin
        $superAdmin->syncPermissions(
            Permission::all()
        );

        // admin akademik
        $adminAkademik->syncPermissions([
            'view_any_document',
            'view_document',

            'view_any_document_type',
            'view_document_type',
            'create_document_type',
            'update_document_type',
            'delete_document_type',

            'view_any_approval',
            'view_approval',
            'create_approval',
            'update_approval',

            'view_activity_log',
        ]);

        // dosen
        $dosen->syncPermissions([
            'view_any_document',
            'view_document',

            'view_any_approval',
            'view_approval',
            'create_approval',
            'update_approval',
        ]);

        // mahasiswa
        $mahasiswa->syncPermissions([
            'view_any_document',
            'view_document',
            'create_document',
            'update_document',
        ]);
    }
}