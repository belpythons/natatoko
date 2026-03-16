<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * WARNING: Do NOT create admin users here.
     * The first Super Admin must be created via the /setup browser flow.
     */
    public function run(): void
    {
        // No-op: Users are created through the /setup first-time flow.
    }
}