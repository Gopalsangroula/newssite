<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Who gets each permission: the same access the hard-coded route groups gave,
     * plus advertisers on Advertisements, which they were meant to have.
     */
    private array $access = [
        'access posts' => ['admin', 'editor'],
        'access pages' => ['admin'],
        'access media' => ['admin', 'editor'],
        'access categories' => ['admin', 'editor'],
        'access tags' => ['admin', 'editor'],
        'access comments' => ['admin', 'editor'],
        'access advertisements' => ['admin', 'advertiser'],
        'access seo' => ['admin', 'editor'],
        'access seo settings' => ['admin'],
        'access users' => ['admin'],
        'access roles' => ['admin'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->access as $name => $roles) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            foreach ([...$roles, 'super-admin'] as $roleName) {
                Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('name', array_keys($this->access))->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
