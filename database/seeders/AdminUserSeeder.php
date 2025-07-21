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
        // Check if admin user already exists
        $existingUser = User::where('email', 'ngareseanmark7@gmail.com')->first();
        
        if (!$existingUser) {
            User::create([
                'name' => 'Admin User',
                'email' => 'ngareseanmark7@gmail.com',
                'password' => Hash::make('12345678'), // Change this to a secure password
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Admin user created successfully.');
        } else {
            $this->command->info('Admin user already exists. Skipping creation.');
        }
    }
}