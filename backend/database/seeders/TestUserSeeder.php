<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Model\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test admin user
        User::updateOrCreate(
            ['email' => 'admin@youports.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'ADMIN',
            ]
        );

        // Create test teacher user
        User::updateOrCreate(
            ['email' => 'teacher@youports.com'],
            [
                'first_name' => 'Teacher',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'TEACHER',
            ]
        );

        // Create test student user
        User::updateOrCreate(
            ['email' => 'student@youports.com'],
            [
                'first_name' => 'Student',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'STUDENT',
            ]
        );

        // Create test BDE user
        User::updateOrCreate(
            ['email' => 'bde@youports.com'],
            [
                'first_name' => 'BDE',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'BDE',
            ]
        );
    }
}
