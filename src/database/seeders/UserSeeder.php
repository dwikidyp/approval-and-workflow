<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $user->assignRole('super_admin');

        $admin = User::firstOrCreate(
            [
                'email' => 'adminakademik@kampus.com'
            ],
            [
                'name' => 'Admin Akademik',
                'password' => bcrypt('password'),
            ]
        );

        $admin->assignRole('Admin Akademik');

        $dosen = User::firstOrCreate(
            [
                'email' => 'dosen@kampus.com'
            ],
            [
                'name' => 'Dosen',
                'password' => bcrypt('password'),
            ]
        );

        $dosen->assignRole('Dosen');

        $mahasiswa = User::firstOrCreate(
            [
                'email' => 'mahasiswa@kampus.com'
            ],
            [
                'name' => 'Mahasiswa',
                'password' => bcrypt('password'),
            ]
        );

        $mahasiswa->assignRole('Mahasiswa');


    }
}
