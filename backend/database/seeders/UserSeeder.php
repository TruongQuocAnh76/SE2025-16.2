<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            // Admin User
            [
                'id' => Str::uuid(),
                'email' => 'admin@certchain.com',
                'password' => Hash::make('password123'),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'avatar' => null,
                'bio' => 'System administrator for Certchain platform',
                'role' => 'ADMIN',
                'auth_provider' => 'EMAIL',
                'is_email_verified' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Teacher Users
            [
                'id' => Str::uuid(),
                'email' => 'john.teacher@certchain.com',
                'password' => Hash::make('password123'),
                'first_name' => 'John',
                'last_name' => 'Smith',
                'avatar' => 'https://example.com/avatars/john-smith.jpg',
                'bio' => 'Full-stack developer with 10+ years of experience in web development.',
                'role' => 'TEACHER',
                'auth_provider' => 'EMAIL',
                'is_email_verified' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'email' => 'sarah.johnson@certchain.com',
                'password' => Hash::make('password123'),
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'avatar' => 'https://example.com/avatars/sarah-johnson.jpg',
                'bio' => 'UI/UX designer and frontend specialist with expertise in Vue.js and React.',
                'role' => 'TEACHER',
                'auth_provider' => 'EMAIL',
                'is_email_verified' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Student Users
            [
                'id' => Str::uuid(),
                'email' => 'alice.student@example.com',
                'password' => Hash::make('password123'),
                'first_name' => 'Alice',
                'last_name' => 'Cooper',
                'avatar' => 'https://example.com/avatars/alice-cooper.jpg',
                'bio' => 'Computer science student passionate about web development.',
                'role' => 'STUDENT',
                'auth_provider' => 'EMAIL',
                'is_email_verified' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'email' => 'bob.learner@example.com',
                'password' => Hash::make('password123'),
                'first_name' => 'Bob',
                'last_name' => 'Wilson',
                'avatar' => null,
                'bio' => 'Career changer looking to learn programming.',
                'role' => 'STUDENT',
                'auth_provider' => 'GOOGLE',
                'is_email_verified' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'email' => 'charlie.dev@example.com',
                'password' => Hash::make('password123'),
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'avatar' => 'https://example.com/avatars/charlie-brown.jpg',
                'bio' => 'Junior developer looking to expand skills.',
                'role' => 'STUDENT',
                'auth_provider' => 'EMAIL',
                'is_email_verified' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
