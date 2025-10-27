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
        // Check if users already exist
        $existingUsers = DB::table('user')->count();
        if ($existingUsers > 0) {
            $this->command->info('Users already exist. Skipping seeder.');
            return;
        }

        // Create admin user
        $adminId = DB::table('user')->insertGetId([
            'nama_user' => 'Administrator',
            'username' => 'admin@polanka.com',
            'password' => Hash::make('password'),
            'roles' => 'admin',
            'no_telepon' => '08123456789',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create test petugas user
        $petugasId = DB::table('user')->insertGetId([
            'nama_user' => 'Petugas Test',
            'username' => 'petugas@polanka.com',
            'password' => Hash::make('password'),
            'roles' => 'petugas',
            'no_telepon' => '08123456790',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create test pasien user
        $pasienId = DB::table('user')->insertGetId([
            'nama_user' => 'Pasien Test',
            'username' => 'pasien@polanka.com',
            'password' => Hash::make('password'),
            'roles' => 'pasien',
            'no_telepon' => '08123456791',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create corresponding datapasien for pasien user
        DB::table('datapasien')->insert([
            'nama_pasien' => 'Pasien Test',
            'email' => 'pasien@polanka.com',
            'no_telp' => '08123456791',
            'user_id' => $pasienId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@polanka.com / password');
        $this->command->info('Petugas: petugas@polanka.com / password');
        $this->command->info('Pasien: pasien@polanka.com / password');
    }
}
