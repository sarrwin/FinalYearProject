<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Rajini', // You can set the name here
            'email' => 'rajini@siswa.um.edu.my', // Your desired email
            'password' => Hash::make('123456789'), // Your desired password
            'role' => 'admin', // If you have roles, set one here
        ]);
    }
}
