<?php

namespace Database\Seeders;

use App\Support\PageAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // if (!app()->environment(['local', 'testing'])) {
        //     $this->command->warn('Skipping RolePermissionSeeder in production.');
        //     return;
        // }

        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $permissions = [
                'view dashboard',
                'create content',
                'edit content',
                'delete content',
                'create posts',
                'edit posts',
                'delete posts',
                'delete own posts',
                'restore posts',
                'manage users',
                'manage roles',
                'manage permissions',
                ...array_keys(PageAccess::SECTIONS),
            ];

            // Create all permissions
            collect($permissions)->each(fn ($perm) =>
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web'])
            );

            // Role → Permissions mapping
            $roles = [
                'super-admin' => $permissions,
                'admin' => $permissions,
                'editor' => [
                    'view dashboard',
                    'create content',
                    'edit content',
                    'delete content',
                    'create posts',
                    'edit posts',
                    'delete own posts',
                    'access posts',
                    'access media',
                    'access categories',
                    'access tags',
                    'access comments',
                    'access gold silver',
                    'access patro',
                    'access rashifal',
                    'access seo',
                ],
                'advertiser' => [
                    'view dashboard',
                    'create content',
                    'create posts',
                    'access advertisements',
                ],
                'user' => [
                    'view dashboard',
                    'create content',
                    'edit content',
                    'create posts',
                    'edit posts',
                ],
            ];

            // Create roles and assign permissions
            foreach ($roles as $roleName => $rolePermissions) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $role->syncPermissions($rolePermissions);
            }

            \Log::info('Roles and permissions seeded successfully.');
        } catch (\Exception $e) {
            \Log::error('Role and permission seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
