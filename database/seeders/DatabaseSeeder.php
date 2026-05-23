<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Professeur de test
        User::create([
            'name' => 'Prof. Martin',
            'email' => 'prof@test.com',
            'password' => Hash::make('password'),
            'role' => 'professor',
        ]);

        // Étudiants de test
        User::create([
            'name' => 'Alice Dupont',
            'email' => 'alice@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => 'ETU-2024-001',
        ]);

        User::create([
            'name' => 'Bob Martin',
            'email' => 'bob@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => 'ETU-2024-002',
        ]);

        User::create([
            'name' => 'Charlie Durand',
            'email' => 'charlie@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => 'ETU-2024-003',
        ]);

        User::create([
            'name' => 'Diana Lefebvre',
            'email' => 'diana@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => 'ETU-2024-004',
        ]);
    }
}