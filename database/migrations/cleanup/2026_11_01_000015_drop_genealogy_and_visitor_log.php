<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * This permanently deletes the family tree records and the visitor log, so it sits in its
 * own folder and an ordinary "php artisan migrate" does not run it. Run it by hand with:
 *
 *     php artisan migrate --path=database/migrations/cleanup --force
 *
 * The site no longer uses either table, so it works the same before and after.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A last copy of the records, outside the folders the website serves.
        if (Schema::hasTable('genealogies')) {
            File::ensureDirectoryExists(storage_path('backups'));
            File::put(
                storage_path('backups/genealogies-'.now()->format('Y-m-d-His').'.json'),
                DB::table('genealogies')->orderBy('id')->get()->toJson(JSON_UNESCAPED_UNICODE)
            );
        }

        Schema::dropIfExists('genealogies');
        Schema::dropIfExists('visits');

        Permission::where('name', 'like', '%genealogy%')
            ->orWhereIn('name', ['access analytics', 'view visitor details'])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * The records cannot be brought back by a rollback. Restore them from the backup file.
     */
    public function down(): void
    {
    }
};
