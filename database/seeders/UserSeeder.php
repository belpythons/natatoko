<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed users: 1 Admin, 4 Employees
     */
    public function run(): void
    {
    // Initial Admin users are no longer generated via seeder.
    // The first user should be created directly through the /setup browser flow.
    }
}