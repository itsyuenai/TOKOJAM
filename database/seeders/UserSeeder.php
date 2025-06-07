<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Hash; // Import Hash facade

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Toko',
            'email' => 'admintoko123@gmail.com', // This should match the email you try to log in with
            'password' => Hash::make('12345678'), // You can set any password here, e.g., 'password'
            'email_verified_at' => now(), // Optional: mark as verified
        ]);

        // You can add more users here if needed
        // User::create([
        //     'name' => 'Another User',
        //     'email' => 'user@example.com',
        //     'password' => Hash::make('secret'),
        // ]);
    }
}