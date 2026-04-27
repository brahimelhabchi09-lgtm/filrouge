<?php

namespace Database\Seeders;

use App\Model\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin users
        User::factory()->count(2)->admin()->create();

        // Create teacher users
        User::factory()->count(3)->teacher()->create();

        // Create student users
        User::factory()->count(5)->student()->create();

        // Create BDE users
        User::factory()->count(2)->bde()->create();

        // Create specific test users with known credentials
        User::factory()
            ->admin()
            ->withEmail('admin@youports.com')
            ->withName('Admin', 'User')
            ->create();

        User::factory()
            ->teacher()
            ->withEmail('teacher@youports.com')
            ->withName('Teacher', 'User')
            ->create();

        User::factory()
            ->student()
            ->withEmail('student@youports.com')
            ->withName('Student', 'User')
            ->create();

        User::factory()
            ->bde()
            ->withEmail('bde@youports.com')
            ->withName('BDE', 'User')
            ->create();
    }
}
