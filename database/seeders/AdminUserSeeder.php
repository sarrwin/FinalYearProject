<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@siswa.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@siswa.com',
                'password' => Hash::make('123456789'),
                'role' => 'admin',
            ]);
        }
    }
}
