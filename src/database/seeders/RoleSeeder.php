<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate([
            'name' => 'Admin Akademik',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Dosen',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Mahasiswa',
            'guard_name' => 'web',
        ]);
    }
}
