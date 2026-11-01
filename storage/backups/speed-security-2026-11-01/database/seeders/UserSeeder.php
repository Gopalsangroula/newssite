<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment(['local', 'testing'])) {
            try {
                $defaultPassword = bcrypt(env('SEEDER_DEFAULT_PASSWORD', Str::random(12)));


                $superAdmin = User::firstOrCreate(
                    ['email' => 'superadmin@appharu.com'],
                    [
                        'name' => 'Super Admin',
                        'slug' => Str::slug('superadmin'),
                        'password' => $defaultPassword,
                        'email_verified_at' => now(),
                    ]
                );
                $superAdmin->syncRoles(['super-admin']);

                $admin = User::firstOrCreate(
                    ['email' => 'admin@appharu.com'],
                    [
                        'name' => 'Admin User',
                        'slug' => Str::slug('admin'),
                        'password' => $defaultPassword,
                        'email_verified_at' => now(),
                    ]
                );
                $admin->syncRoles(['admin']);

                $editor = User::firstOrCreate(
                    ['email' => 'editor@appharu.com'],
                    [
                        'name' => 'Editor User',
                        'slug' => Str::slug('editor'),
                        'password' => $defaultPassword,
                        'email_verified_at' => now(),
                    ]
                );
                $editor->syncRoles(['editor']);

                $advertiser = User::firstOrCreate(
                    ['email' => 'adv@appharu.com'],
                    [
                        'name' => 'Advertiser',
                        'slug' => Str::slug('advertiser'),
                        'password' => $defaultPassword,
                        'email_verified_at' => now(),
                    ]
                );
                $advertiser->syncRoles(['advertiser']);

                $user = User::firstOrCreate(
                    ['email' => 'user@appharu.com'],
                    [
                        'name' => 'Regular User',
                        'password' => $defaultPassword,
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles(['user']);

                \Log::info('Users seeded successfully.');
            } catch (\Exception $e) {
                \Log::error('User seeding failed: ' . $e->getMessage());
                throw $e;
            }
        }
    }
}