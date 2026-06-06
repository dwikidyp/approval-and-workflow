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

        $user = User::firstOrCreate(
            ['email' => 'user@admin.com'],
            ['name' => 'User Account', 'password' => Hash::make('password')]
        );
        $user->assignRole('user');

        $user = User::create([
            'name' => 'Admin Akademik',
            'email' => 'adminakademik@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('Admin Akademik');

        $user = User::create([
            'name' => 'Dosen',
            'email' => 'dosen@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('Dosen');

        $user = User::create([
            'name' => 'Mahasiswa',
            'email' => 'mahasiswa@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('Mahasiswa');


    }
}
