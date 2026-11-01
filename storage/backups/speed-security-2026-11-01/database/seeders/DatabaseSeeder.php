<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Remove the environment restriction to allow seeding in production
        try {
            $this->call(RolePermissionSeeder::class);
            $this->call(UserSeeder::class);

            \Log::info('Database seeded successfully.');
        } catch (\Exception $e) {
            \Log::error('Database seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}