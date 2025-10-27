<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@rumahsakit.com',
            'password' => Hash::make('admin123'),
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create test petugas user
        DB::table('users')->insert([
            'name' => 'Petugas Test',
            'email' => 'petugas@rumahsakit.com',
            'password' => Hash::make('petugas123'),
            'roles' => 'petugas',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create test pasien user
        DB::table('users')->insert([
            'name' => 'Pasien Test',
            'email' => 'pasien@rumahsakit.com',
            'password' => Hash::make('pasien123'),
            'roles' => 'pasien',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
