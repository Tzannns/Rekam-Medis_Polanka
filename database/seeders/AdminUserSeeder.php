<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user')->insert([
            'nama_user' => 'Administrator',
            'username' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'no_telepon' => '081234567890',
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
